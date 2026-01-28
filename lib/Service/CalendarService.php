<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Service;

use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use OCA\DAV\CalDAV\CalDavBackend;
use OCA\ShiftsNext\Db\CalendarChange;
use OCA\ShiftsNext\Db\CalendarChangeMapper;
use OCA\ShiftsNext\Exception\CalendarNotFoundException;
use OCA\ShiftsNext\Exception\ShiftNotFoundException;
use OCA\ShiftsNext\Extended\ShiftExtended;
use OCA\ShiftsNext\Psalm\CalendarAlias;
use OCA\ShiftsNext\Util\Util;
use Ramsey\Uuid\Uuid;
use Sabre\VObject\Component\VCalendar;
use Throwable;

use function array_column;
use function array_map;
use function array_merge;
use function array_push;
use function in_array;
use function mb_strtolower;
use function trim;

/**
 * @psalm-import-type Calendar from CalendarAlias
 * @psalm-import-type CalendarObject from CalendarAlias
 * @psalm-import-type SanitizedCalendar from CalendarAlias
 * @psalm-import-type SearchResultObject from CalendarAlias
 * @psalm-import-type SearchResult from CalendarAlias
 */
final class CalendarService {
	/** DON'T EVER CHANGE THIS VALUE */
	private const string NAMESPACE_UUID = 'd3a8945c-b6ce-4d49-915f-9f7be87c866b';

	public function __construct(
		private CalDavBackend $calDavBackend,
		private ConfigService $configService,
		private ShiftService $shiftService,
		private CalendarChangeMapper $calendarChangeMapper,
		private UserService $userService,
	) {
	}

	/**
	 * @return list<SanitizedCalendar>
	 */
	public function getCalendars(): array {
		$users = $this->userService->getAll();
		/** @var list<Calendar> */
		$calendars = [];
		foreach ($users as $user) {
			$principalUri = 'principals/users/' . $user->getUID();
			/** @var list<Calendar> */
			$userCalendars = $this->calDavBackend->getUsersOwnCalendars(
				$principalUri,
			);
			array_push($calendars, ...$userCalendars);
		}
		return array_map(self::sanitizeCalendar(...), $calendars);
	}

	/**
	 * Sequentially applies `$calendarChanges` and deletes them afterwards if
	 * applying was successful
	 *
	 * None-critical exceptions are catched and hints about the culprit are
	 * included in a string array returned from this method
	 *
	 * @param CalendarChange[] $calendarChanges
	 *
	 * @return list<string> Error messages
	 *
	 * @throws CalendarNotFoundException
	 *                                   {@see OCA\ShiftsNext\Service\ConfigService::getCommonCalendarId()}
	 */
	public function applyChanges(array $calendarChanges): array {
		$errors = [];

		foreach ($calendarChanges as $change) {
			$calendars = $this->applyChange($change);

			foreach ($calendars as $calendar) {
				$errors[]
					= 'Failed to apply change to calendar '
					. "'{$calendar['displayName']}' of user"
					. "'{$calendar['ownerDisplayName']}'";
			}

			if ($calendars) {
				continue;
			}

			$changeId = $change->getId();
			try {
				$this->calendarChangeMapper->deleteById($changeId);
			} catch (Throwable) {
				$errors[] = "Failed to delete calendar change `$changeId`";
			}
		}

		return $errors;
	}

	/**
	 * Either creates, updates or deletes an event based on `$calendarChange`
	 *
	 * @param CalendarChange $calendarChange
	 *
	 * @return list<SanitizedCalendar> Calendars where applying the change failed
	 *
	 * @throws CalendarNotFoundException
	 *                                   {@see OCA\ShiftsNext\Service\ConfigService::getCommonCalendarId()}
	 */
	public function applyChange(CalendarChange $calendarChange): array {
		$userId = $calendarChange->getUserId();
		$shiftId = $calendarChange->getShiftId();

		try {
			$shift = $this->shiftService->getExtended($shiftId);
		} catch (ShiftNotFoundException) {
			// Failing to get the shift, because it does not exist, is expected
			$shift = null;
		}

		['normal' => $objectUri, 'deleted' => $objectUriDeleted]
			= self::getCalendarObjectUri($shiftId);

		$calendars = [];

		try {
			$calendars[] = $this->getCommonCalendar();
		} catch (CalendarNotFoundException) {
			// Failing to get the common calendar is fine
		}

		if ($this->configService->getSyncToPersonalCalendar()) {
			try {
				$calendars[] = $this->getPersonalCalendar($userId);
			} catch (CalendarNotFoundException) {
				// Failing to get the personal calendar is fine
			}
		}

		$failedCalendars = [];
		foreach ($calendars as $calendar) {
			/** @var null|CalendarObject */
			$deletedObject = $this->calDavBackend->getCalendarObject(
				$calendar['id'],
				$objectUriDeleted,
			);
			if ($deletedObject !== null) {
				$this->calDavBackend->restoreCalendarObject($deletedObject);
			}

			/** @var null|CalendarObject */
			$calendarObject = $this->calDavBackend->getCalendarObject(
				$calendar['id'],
				$objectUri,
			);

			$calendarObjectExists = $calendarObject !== null;

			try {
				if ($shift && $shift->user->getUID() === $userId) {
					$isPersonal
						= $calendar['uri']
							=== CalDavBackend::PERSONAL_CALENDAR_URI;
					$stream = $this->createICalendarStream($shift, $isPersonal);

					if ($calendarObjectExists) {
						$this->calDavBackend->updateCalendarObject(
							$calendar['id'],
							$objectUri,
							$stream,
						);
					} else {
						$this->calDavBackend->createCalendarObject(
							$calendar['id'],
							$objectUri,
							$stream,
						);
					}
				} elseif ($calendarObjectExists) {
					$this->calDavBackend->deleteCalendarObject(
						$calendar['id'],
						$objectUri,
						forceDeletePermanently: true,
					);
				}
			} catch (Throwable) {
				$failedCalendars[] = $calendar;
			}
		}

		return $failedCalendars;
	}

	private function createICalendarStream(
		ShiftExtended $shift,
		bool $isPersonal,
	): string {
		$shiftTypeGroupId = $shift->shiftType->group->getGID();
		$shiftTypeName = $shift->shiftType->name;
		$description = $shift->shiftType->caldav['description'] ?? '';
		$location = $shift->shiftType->caldav['location'] ?? '';
		$categories = $shift->shiftType->caldav['categories'] ?? '';

		$summary = "$shiftTypeGroupId $shiftTypeName";
		if (!$isPersonal) {
			$userDisplayName = $shift->user->getDisplayName();
			$summary .= " ($userDisplayName)";
		}

		$dtStart = Util::parseEcma($shift->start)[0];
		$dtEnd = Util::parseEcma($shift->end)[0];

		if ($shift->shiftType->repetition['weekly_type'] === 'by_week') {
			$dtStart = $dtStart->format(Util::I_CAL_PLAIN_DATE);
			$dtEnd = $dtEnd
				->add(new DateInterval('P1D')) // Necessary because full day iCal events are DTEND exclusive
				->format(Util::I_CAL_PLAIN_DATE);
		} else {
			$timeZone = $this->configService->getTimeZone($shift->user->getUID());
			$dateTimeZone = new DateTimeZone($timeZone);

			$dtStart = $dtStart->setTimezone($dateTimeZone);
			$dtEnd = $dtEnd->setTimezone($dateTimeZone);
		}

		$vCalendar = new VCalendar([
			'VEVENT' => array_merge(
				[
					'SUMMARY' => $summary,
					'TRANSP' => 'TRANSPARENT',
					'DTSTART' => $dtStart,
					'DTEND' => $dtEnd,
				],
				$description !== '' ? ['DESCRIPTION' => $description] : [],
				$location !== '' ? ['LOCATION' => $location] : [],
				$categories !== '' ? ['CATEGORIES' => $categories] : [],
			),
		]);

		/** @var string */
		return $vCalendar->serialize();
	}

	/**
	 * @return SanitizedCalendar
	 *
	 * @throws CalendarNotFoundException
	 */
	public function getCommonCalendar(): array {
		$id = $this->configService->getCommonCalendarId();
		return $this->getCalendarById($id);
	}

	/**
	 * @return null|SanitizedCalendar `null` if not found
	 */
	public function safeGetCommonCalendar(): ?array {
		$id = $this->configService->getCommonCalendarId();
		return $this->safeGetCalendarById($id);
	}

	/**
	 * @return SanitizedCalendar
	 *
	 * @throws CalendarNotFoundException
	 */
	public function getAbsenceCalendar(): array {
		$id = $this->configService->getAbsenceCalendarId();
		return $this->getCalendarById($id);
	}

	/**
	 * @return null|SanitizedCalendar `null` if not found
	 */
	public function safeGetAbsenceCalendar(): ?array {
		$id = $this->configService->getAbsenceCalendarId();
		return $this->safeGetCalendarById($id);
	}

	/**
	 * @return SanitizedCalendar
	 *
	 * @throws CalendarNotFoundException
	 */
	public function getPersonalCalendar(string $userId): array {
		/** @var string */
		$uri = CalDavBackend::PERSONAL_CALENDAR_URI;
		return $this->getCalendarByUri($userId, $uri);
	}

	/**
	 * @return SanitizedCalendar
	 *
	 * @throws CalendarNotFoundException if no calendar for `$id` exists
	 */
	public function getCalendarById(int $id): array {
		/** @var null|Calendar */
		$calendar = $this->calDavBackend->getCalendarById($id);
		if (!$calendar) {
			throw new CalendarNotFoundException("Calendar with ID $id not found");
		}
		return self::sanitizeCalendar($calendar);
	}

	/**
	 * @return null|SanitizedCalendar `null` if no calendar for `$id` exists
	 */
	public function safeGetCalendarById(int $id): ?array {
		try {
			return $this->getCalendarById($id);
		} catch (CalendarNotFoundException) {
			return null;
		}
	}

	/**
	 * @return SanitizedCalendar
	 *
	 * @throws CalendarNotFoundException if no calendar for `$userId` and
	 *                                   `$calendarUri` exists
	 */
	public function getCalendarByUri(string $userId, string $calendarUri): array {
		$principalUri = "principals/users/$userId";
		/** @var null|Calendar */
		$calendar = $this->calDavBackend->getCalendarByUri(
			$principalUri,
			$calendarUri,
		);
		if (!$calendar) {
			throw new CalendarNotFoundException(
				"Couldn't find calendar by principal URI $principalUri"
				. " and calendar URI $calendarUri"
			);
		}
		return self::sanitizeCalendar($calendar);
	}

	/**
	 * Checks if there is an event in the absence calendar for `$userId`
	 * between `$start` and `$end`
	 */
	public function isUserAbsent(
		string $userId,
		DateTimeImmutable $start,
		DateTimeImmutable $end,
	): bool {
		$user = $this->userService->get($userId);
		$userDisplayName = $user->getDisplayName();

		$calendar = $this->getAbsenceCalendar();

		/** @var list<SearchResult> */
		$results = $this->calDavBackend->search(
			['id' => $calendar['id']],
			'',
			[],
			['timerange' => ['start' => $start, 'end' => $end]],
			25,
			0,
		);

		// Extract only the relevant data from the results
		$eventTitles = array_column(
			array_column(
				array_merge(
					...array_column(
						$results,
						'objects',
					)
				),
				'SUMMARY',
			),
			0,
		);

		$sanitizedTitles = array_map(
			fn ($title) => mb_strtolower(trim($title)),
			$eventTitles,
		);

		return
			in_array(mb_strtolower(trim($userDisplayName)), $sanitizedTitles, true)
			|| in_array(mb_strtolower(trim($userId)), $sanitizedTitles, true);
	}

	/**
	 * This method returns the same values if the `$shiftId` does not change
	 *
	 * @param int $shiftId
	 *
	 * @return array{normal:string,deleted:string}
	 */
	public static function getCalendarObjectUri(int $shiftId): array {
		$uuid5 = Uuid::uuid5(self::NAMESPACE_UUID, "$shiftId")->toString();
		return ['normal' => "$uuid5.ics", 'deleted' => "$uuid5-deleted.ics"];
	}

	/**
	 * @param Calendar $calendar
	 *
	 * @return SanitizedCalendar
	 */
	public static function sanitizeCalendar(array $calendar) {
		return [
			'id' => $calendar['id'],
			'uri' => $calendar['uri'],
			'principalUri' => $calendar['principaluri'],
			'displayName' => $calendar['{DAV:}displayname'],
			'ownerDisplayName'
				=> $calendar['{http://nextcloud.com/ns}owner-displayname'],
		];
	}
}
