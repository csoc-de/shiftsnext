<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Service;

use DateTimeImmutable;
use Exception;
use OCA\ShiftsNext\Db\Shift;
use OCA\ShiftsNext\Db\ShiftMapper;
use OCA\ShiftsNext\Db\ShiftType;
use OCA\ShiftsNext\Exception\ShiftNotFoundException;
use OCA\ShiftsNext\Exception\ShiftTypeNotFoundException;
use OCA\ShiftsNext\Exception\UserNotFoundException;
use OCA\ShiftsNext\Extended\ShiftExtended;
use OCA\ShiftsNext\Extended\ShiftTypeExtended;
use OCP\IUser;
use Throwable;

use function array_map;
use function explode;

final class ShiftService {
	public function __construct(
		private ShiftMapper $shiftMapper,
		private ShiftTypeService $shiftTypeService,
		private ConfigService $configService,
		private UserService $userService,
	) {
	}

	/**
	 * @throws ShiftNotFoundException {@see OCA\ShiftsNext\Db\ShiftMapper::findById()}
	 * @throws UserNotFoundException {@see OCA\ShiftsNext\Service\UserService::get()}
	 * @throws ShiftTypeNotFoundException {@see OCA\ShiftsNext\Db\ShiftTypeMapper::findById()}
	 */
	public function getExtended(
		int|Shift|ShiftExtended $shift,
		null|int|ShiftType|ShiftTypeExtended $shiftType = null,
		null|string|IUser $user = null,
	): ShiftExtended {
		if ($shift instanceof ShiftExtended) {
			return $shift;
		}
		$shift = $this->shiftMapper->findById($shift);

		$user ??= $shift->getUserId();
		$user = $this->userService->get($user);

		$shiftType ??= $shift->getShiftTypeId();
		$shiftType = $this->shiftTypeService->getExtended($shiftType);

		return new ShiftExtended($shift, $user, $shiftType);
	}

	/**
	 * Gets all shifts in extended format
	 *
	 * The passed parameters are used as additional filters.
	 *
	 * Parameters `$calendarDate` and `$weekDate` are mutually exclusive.
	 *
	 * @param null|string[] $groupIds Returns Shifts for those groups
	 * @param null|string $userId Returns Shifts for that user
	 * @param null|string $calendarDate Returns Shifts on that date
	 * @param null|string $weekDate Returns Shifts with their start date in the given week
	 *
	 * @return ShiftExtended[]
	 *
	 * @throws Exception {@see OCA\ShiftsNext\Service\ShiftService::getExtended()}
	 */
	public function getAllExtended(
		?array $groupIds = null,
		?string $userId = null,
		?string $calendarDate = null,
		?string $weekDate = null,
	): array {
		$start = null;
		if ($calendarDate !== null) {
			$start = [
				'timeZone' => $this->configService->getTimeZone(),
				'calendarDates' => [$calendarDate],
			];
		} elseif ($weekDate !== null) {
			$parts = explode('-W', $weekDate);
			$year = (int)$parts[0];
			$week = (int)$parts[1];
			$dateTime = new DateTimeImmutable('now');
			$start = [
				'timeZone' => $this->configService->getTimeZone(),
				'calendarDates' => [
					$dateTime->setISODate($year, $week, 1)->format('Y-m-d'),
					$dateTime->setISODate($year, $week, 7)->format('Y-m-d'),
				],
			];
		}
		$shifts = $this->shiftMapper->findAll($groupIds, $userId, $start);
		return array_map($this->getExtended(...), $shifts);
	}

	/**
	 * @return null|ShiftExtended `null` if no Shift with `$id` exists
	 *
	 * @psalm-suppress PossiblyUnusedMethod Currently unused
	 */
	public function safeGetExtended(int $id): ?ShiftExtended {
		try {
			return $this->getExtended($id);
		} catch (Throwable) {
			return null;
		}
	}
}
