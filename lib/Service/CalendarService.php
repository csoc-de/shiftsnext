<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Service;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use OCA\DAV\CalDAV\CalDavBackend;
use OCA\ShiftsNext\Db\Shift;
use OCA\ShiftsNext\Enum\SyncShiftOperation;
use OCA\ShiftsNext\Extended\ShiftExtended;
use OCA\ShiftsNext\Psalm\CalendarAlias;
use OCA\ShiftsNext\Util\Util;
use Ramsey\Uuid\Uuid;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Component\VEvent;
use Sabre\VObject\Reader;
use Throwable;
use function array_any;
use function array_column;
use function array_filter;
use function array_map;
use function array_merge;
use function array_push;
use function array_unique;
use function array_values;
use function explode;
use function in_array;
use function mb_ereg_replace;
use function mb_split;
use function mb_strtolower;
use function preg_split;
use function rtrim;
use function str_contains;
use function str_starts_with;
use function trim;

/**
 * @psalm-import-type Calendar from CalendarAlias
 * @psalm-import-type CalendarObject from CalendarAlias
 * @psalm-import-type SanitizedCalendar from CalendarAlias
 * @psalm-import-type SearchResultObject from CalendarAlias
 * @psalm-import-type SearchResult from CalendarAlias
 */
final class CalendarService extends AbstractService {
	/** DON'T EVER CHANGE THIS VALUE */
	private const string NAMESPACE_UUID = 'd3a8945c-b6ce-4d49-915f-9f7be87c866b';

	public function __construct(
		private CalDavBackend $calDavBackend,
		private ConfigService $configService,
		private UserService $userService,
		private ?string $userId,
	) {
	}

	/**
	 * Returns all calendars
	 *
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
	 * Returns all calendars for which `$userId` has write permissions
	 *
	 * @param null|string $userId If `null`, the logged-in user is used
	 *
	 * @return list<SanitizedCalendar> Empty if there is no logged-in user
	 */
	public function getWritableCalendars(?string $userId = null): array {
		$effectiveUserId = $userId ?? $this->userId;
		if ($effectiveUserId === null) {
			return [];
		}
		/** @var list<Calendar> */
		$calendars = $this->calDavBackend->getCalendarsForUser(
			'principals/users/' . $effectiveUserId
		);
		$calendars = array_filter(
			$calendars,
			fn ($calendar) => !($calendar['{http://owncloud.org/ns}read-only'] ?? false),
		);
		return array_map(self::sanitizeCalendar(...), array_values($calendars));
	}

	/**
	 * Checks if `$userId` has write access for `$calendarId`
	 *
	 * @param int $calendarId The calendar to check
	 * @param null|string $userId If `null`, the logged-in user is used
	 *
	 * @return bool
	 */
	public function hasUserWriteAccessForCalendar(
		int $calendarId,
		?string $userId = null,
	): bool {
		return array_any(
			$this->getWritableCalendars($userId ?? $this->userId),
			fn ($calendar) => $calendar['id'] === $calendarId,
		);
	}

	/**
	 * Syncs `$shift` with the calendar app
	 *
	 * @param ShiftExtended $shift The shift to sync
	 * @param SyncShiftOperation $operation The sync operation
	 *
	 * @return void
	 */
	public function syncShift(ShiftExtended $shift, SyncShiftOperation $operation): void {
		if (!$shift->shiftType->syncToCalendar) {
			return;
		}
		['normal' => $objectUri, 'deleted' => $objectUriDeleted]
			= self::getCalendarObjectUri($shift->id);
		$calendars = [];
		if ($shift->shiftType->calendar !== null) {
			$calendars[] = $shift->shiftType->calendar;
		} elseif ($commonCalendar = $this->getCommonCalendar()) {
			$calendars[] = $commonCalendar;
		}
		if ($this->configService->getSyncToPersonalCalendar()) {
			if ($personalCalendar = $this->getPersonalCalendar($shift->user->getUID())) {
				$calendars[] = $personalCalendar;
			}
		}
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
				if ($operation === SyncShiftOperation::CreateOrUpdate) {
					$stream = $this->createICalendarStream(
						$shift,
						$calendar['uri'] === CalDavBackend::PERSONAL_CALENDAR_URI,
					);
					$args = [$calendar['id'], $objectUri, $stream];
					if ($calendarObjectExists) {
						$this->calDavBackend->updateCalendarObject(...$args);
					} else {
						$this->calDavBackend->createCalendarObject(...$args);
					}
				} elseif ($calendarObjectExists) {
					$this->calDavBackend->deleteCalendarObject(
						$calendar['id'],
						$objectUri,
						forceDeletePermanently: true,
					);
				}
			} catch (Throwable) {
				// Ignore
			}
		}
	}

	/**
	 * Creates an iCal stream (string) for the specified shift
	 *
	 * @param ShiftExtended $shift The shift to create the iCal stream for
	 * @param bool $isPersonal Whether the stream is meant to be used for the
	 *                         user's personal calendar or the common calendar.
	 *                         If `false`, the user's display name is included
	 *                         in the iCal event's SUMMARY field.
	 *
	 * @return string
	 */
	private function createICalendarStream(
		ShiftExtended $shift,
		bool $isPersonal,
	): string {
		$shiftTypeGroupDisplayName = $shift->shiftType->group->getDisplayName();
		$shiftTypeName = $shift->shiftType->name;
		$description = $shift->shiftType->caldav['description'] ?? '';
		$location = $shift->shiftType->caldav['location'] ?? '';
		$categories = $shift->shiftType->caldav['categories'] ?? '';
		$categories = mb_split('(?<!\\\\),', $categories);
		if ($categories === false) {
			$categories = [];
		}
		$categories = array_filter(
			array_map(
				fn ($category) => mb_ereg_replace('\\\\,', ',', trim($category)),
				$categories,
			),
			fn ($category) => $category !== '',
		);

		$summary = "$shiftTypeGroupDisplayName $shiftTypeName";
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
				$categories ? ['CATEGORIES' => $categories] : [],
			),
		]);

		/** @var string */
		return $vCalendar->serialize();
	}

	/**
	 * Returns the "common" calendar set in the admin settings by the Nextcloud
	 * instance admin
	 *
	 * @return null|SanitizedCalendar `null` if not found
	 */
	public function getCommonCalendar(): ?array {
		return $this->getCalendarById($this->configService->getCommonCalendarId());
	}

	/**
	 * Returns the "absence" calendar set in the admin settings by the Nextcloud
	 * instance admin
	 *
	 * @return null|SanitizedCalendar `null` if not found
	 */
	public function getAbsenceCalendar(): ?array {
		return $this->getCalendarById($this->configService->getAbsenceCalendarId());
	}

	/**
	 * Returns the personal calendar of the specified user
	 *
	 * @param string $userId The user to get the personal calendar for
	 *
	 * @return null|SanitizedCalendar `null` if not found
	 */
	public function getPersonalCalendar(string $userId): ?array {
		/** @var string */
		$uri = CalDavBackend::PERSONAL_CALENDAR_URI;
		return $this->getCalendarByUri($userId, $uri);
	}

	/**
	 * Returns the calendar identified by `$id`
	 *
	 * @param int $id The ID of the calendar
	 *
	 * @return null|SanitizedCalendar `null` if no calendar for `$id` exists
	 */
	public function getCalendarById(int $id): ?array {
		/** @var null|Calendar */
		$calendar = $this->calDavBackend->getCalendarById($id);
		return $calendar === null ? null : self::sanitizeCalendar($calendar);
	}

	/**
	 * Returns the calendar identified by `$userId` and `$calendarUri`
	 *
	 * @param string $userId The principals user ID
	 * @param string $calendarUri The calendar URI
	 *
	 * @return null|SanitizedCalendar `null` if no calendar for `$userId` and
	 *                                `$calendarUri` exists
	 */
	public function getCalendarByUri(
		string $userId,
		string $calendarUri,
	): ?array {
		$principalUri = "principals/users/$userId";
		/** @var null|Calendar */
		$calendar = $this->calDavBackend->getCalendarByUri(
			$principalUri,
			$calendarUri,
		);
		return $calendar === null ? null : self::sanitizeCalendar($calendar);
	}

	/**
	 * Checks if there is an event in the absence calendar for `$userId`
	 * between `$start` and `$end`
	 *
	 * @param string $userId The user to execute the absence check for
	 * @param DateTimeImmutable $start The start of the checked period
	 * @param DateTimeImmutable $end The end of the checked period
	 *
	 * @return bool
	 */
	public function isUserAbsent(
		string $userId,
		DateTimeImmutable $start,
		DateTimeImmutable $end,
	): bool {
		$blockers = $this->getAbsenceBlockers($start, $end, [$userId]);
		return in_array($userId, array_column($blockers, 'user_id'), true);
	}

	/**
	 * Returns all absence calendar events blocking the users in `$userIds`
	 * between `$start` and `$end`
	 *
	 * @param DateTimeImmutable $start The start of the checked period
	 * @param DateTimeImmutable $end The end of the checked period
	 * @param null|list<string> $userIds The users to return blockers for. If
	 *                                   `null`, all users are considered.
	 *
	 * @return list<array{
	 *     user_id: string,
	 *     start: string,
	 *     end: string,
	 *     all_day: bool,
	 *     title: string,
	 * }>
	 *
	 * @psalm-suppress MixedAssignment, MixedMethodCall The Sabre VObject
	 *                 classes are not visible to Psalm
	 */
	public function getAbsenceBlockers(
		DateTimeImmutable $start,
		DateTimeImmutable $end,
		?array $userIds = null,
	): array {
		$calendar = $this->getAbsenceCalendar();
		if ($calendar === null) {
			return [];
		}

		/** @var list<SearchResult> */
		$results = $this->calDavBackend->search(
			['id' => $calendar['id']],
			'',
			[],
			['timerange' => ['start' => $start, 'end' => $end]],
			100,
			0,
		);

		$users = $this->userService->getAll($userIds);
		$userIdMap = [];
		$emailMap = [];
		$displayNameMap = [];
		foreach ($users as $user) {
			$userId = $user->getUID();
			$userIdMap[self::normalizeIdentity($userId)] = $userId;
			$displayNameMap[self::normalizeIdentity($user->getDisplayName())] = $userId;
			$email = $user->getEMailAddress();
			if ($email !== null && $email !== '') {
				$emailMap[self::normalizeIdentity($email)] = $userId;
			}
		}

		$blockers = [];
		foreach ($results as $result) {
			$summary = $result['objects'][0]['SUMMARY'][0] ?? '';
			/** @var null|CalendarObject */
			$calendarObject = $this->calDavBackend->getCalendarObject(
				$calendar['id'],
				$result['uri'],
			);
			if ($calendarObject === null) {
				continue;
			}

			try {
				$vCalendar = Reader::read($calendarObject['calendardata']);
			} catch (Throwable) {
				continue;
			}
			foreach ($vCalendar->select('VEVENT') as $vEvent) {
				if (!$vEvent instanceof VEvent) {
					continue;
				}
				/** @var null|DateTimeInterface */
				$eventStart = $vEvent->DTSTART?->getDateTime();
				if ($eventStart === null) {
					continue;
				}
				/** @var DateTimeInterface */
				$eventEnd = $vEvent->DTEND?->getDateTime() ?? $eventStart;
				if ($eventEnd < $start || $eventStart > $end) {
					continue;
				}
				$eventSummary = (string)($vEvent->SUMMARY?->getValue() ?? $summary);
				$resolvedUserIds = $this->resolveEventParticipants(
					$vEvent,
					$eventSummary,
					$userIdMap,
					$emailMap,
					$displayNameMap,
				);
				foreach ($resolvedUserIds as $resolvedUserId) {
					$blockers[] = [
						'user_id' => $resolvedUserId,
						'start' => $eventStart->format(Util::ECMA_DATE_TIME),
						'end' => $eventEnd->format(Util::ECMA_DATE_TIME),
						'all_day' => self::isAllDayEvent($vEvent, $eventStart, $eventEnd),
						'title' => $eventSummary,
					];
				}
			}
		}
		return $blockers;
	}

	/**
	 * Resolves the users participating in `$vEvent`
	 *
	 * Participants are matched against the user ID, the email address and the
	 * display name, both via the event's attendees and its summary
	 *
	 * @param VEvent $vEvent The event to resolve the participants for
	 * @param string $summary The event's summary
	 * @param array<string,string> $userIdMap Normalized user ID to user ID
	 * @param array<string,string> $emailMap Normalized email to user ID
	 * @param array<string,string> $displayNameMap Normalized display name to user ID
	 *
	 * @return list<string>
	 *
	 * @psalm-suppress MixedAssignment, MixedMethodCall The Sabre VObject
	 *                 classes are not visible to Psalm
	 */
	private function resolveEventParticipants(
		VEvent $vEvent,
		string $summary,
		array $userIdMap,
		array $emailMap,
		array $displayNameMap,
	): array {
		$tokens = [];
		foreach ($vEvent->select('ATTENDEE') as $attendee) {
			$tokens[] = (string)$attendee->getValue();
			$tokens[] = (string)($attendee['CN']?->getValue() ?? '');
			$tokens[] = (string)($attendee['EMAIL']?->getValue() ?? '');
			$tokens[] = (string)($attendee['X-NC-USER-ID']?->getValue() ?? '');
		}
		foreach ($vEvent->select('PARTICIPANT') as $participant) {
			$tokens[] = (string)$participant->getValue();
			$tokens[] = (string)($participant['CN']?->getValue() ?? '');
			$tokens[] = (string)($participant['EMAIL']?->getValue() ?? '');
			$tokens[] = (string)($participant['X-NC-USER-ID']?->getValue() ?? '');
		}
		$tokens[] = $summary;
		$tokens = array_merge(
			$tokens,
			preg_split('/[,;]/', $summary) ?: [],
		);

		$resolvedUserIds = [];
		foreach ($tokens as $token) {
			foreach (self::toIdentityCandidates($token) as $candidate) {
				$userId
					= $userIdMap[$candidate]
					?? $emailMap[$candidate]
					?? $displayNameMap[$candidate]
					?? null;
				if ($userId !== null) {
					$resolvedUserIds[] = $userId;
				}
			}
		}

		/** @var list<string> */
		return array_values(array_unique($resolvedUserIds));
	}

	/**
	 * @return list<string>
	 */
	private static function toIdentityCandidates(string $raw): array {
		$candidates = [];
		$normalized = self::normalizeIdentity($raw);
		if ($normalized !== '') {
			$candidates[] = $normalized;
		}
		$withoutMailto = self::normalizeIdentity(
			str_starts_with($raw, 'mailto:') ? substr($raw, 7) : $raw,
		);
		if ($withoutMailto !== '') {
			$candidates[] = $withoutMailto;
		}
		if (str_contains($raw, '/')) {
			$segments = explode('/', rtrim($raw, '/'));
			$lastSegment = $segments[count($segments) - 1] ?? '';
			$lastSegmentNormalized = self::normalizeIdentity($lastSegment);
			if ($lastSegmentNormalized !== '') {
				$candidates[] = $lastSegmentNormalized;
			}
		}
		return array_values(array_unique($candidates));
	}

	private static function normalizeIdentity(string $value): string {
		return mb_strtolower(trim($value));
	}

	/**
	 * Checks if `$vEvent` spans one or more full days
	 *
	 * @param VEvent $vEvent The event to check
	 * @param DateTimeInterface $start The event's start
	 * @param DateTimeInterface $end The event's end
	 *
	 * @return bool
	 *
	 * @psalm-suppress MixedArrayAccess, MixedAssignment, MixedMethodCall The
	 *                 Sabre VObject classes are not visible to Psalm
	 */
	private static function isAllDayEvent(
		VEvent $vEvent,
		DateTimeInterface $start,
		DateTimeInterface $end,
	): bool {
		$dateType = '';
		if ($vEvent->DTSTART !== null) {
			$valueParameter = $vEvent->DTSTART['VALUE'];
			if ($valueParameter !== null) {
				$dateType = (string)$valueParameter->getValue();
			}
		}
		if (mb_strtolower($dateType) === 'date') {
			return true;
		}
		return $start->format('H:i:s') === '00:00:00'
			&& $end->format('H:i:s') === '00:00:00'
			&& $end > $start;
	}

	/**
	 * Returns an array containing two calendar object URIs: one for the
	 * not-deleted variant and the other one for the deleted variant
	 *
	 * The return value is stable for identical `$shiftId` input values
	 *
	 * @param int $shiftId The shift to get the calendar object URIs for
	 *
	 * @return array{normal:string,deleted:string}
	 */
	public static function getCalendarObjectUri(int $shiftId): array {
		$uuid5 = Uuid::uuid5(self::NAMESPACE_UUID, "$shiftId")->toString();
		return ['normal' => "$uuid5.ics", 'deleted' => "$uuid5-deleted.ics"];
	}

	/**
	 * Sanitizes a calendar (associative array) returned from many of the
	 * {@see OCA\DAV\CalDAV\CalDavBackend} methods
	 *
	 * @param Calendar $calendar The calendar to sanitize
	 *
	 * @return SanitizedCalendar
	 */
	public static function sanitizeCalendar(array $calendar): array {
		$sanitizedCalendar = [
			'id' => $calendar['id'],
			'uri' => $calendar['uri'],
			'principalUri' => $calendar['principaluri'],
			'displayName' => $calendar['{DAV:}displayname'],
			'ownerDisplayName'
				=> $calendar['{http://nextcloud.com/ns}owner-displayname'],
		];
		if (array_key_exists('{http://owncloud.org/ns}owner-principal', $calendar)) {
			$sanitizedCalendar['ownerPrincipal'] = $calendar['{http://owncloud.org/ns}owner-principal'];
		}
		if (array_key_exists('{http://owncloud.org/ns}read-only', $calendar)) {
			$sanitizedCalendar['readOnly'] = $calendar['{http://owncloud.org/ns}read-only'];
		}
		return $sanitizedCalendar;
	}
}
