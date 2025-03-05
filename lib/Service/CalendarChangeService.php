<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Service;

use OCA\ShiftsNext\Db\CalendarChange;
use OCA\ShiftsNext\Db\CalendarChangeMapper;
use OCA\ShiftsNext\Db\Shift;
use Throwable;

class CalendarChangeService {
	public function __construct(
		private CalendarChangeMapper $calendarChangeMapper,
		private ShiftService $shiftService,
	) {
	}

	/**
	 * @return CalendarChange|false `false` if creation fails
	 *
	 * @psalm-suppress PossiblyUnusedReturnValue
	 */
	public function safeCreate(Shift|ShiftExtended $shift): CalendarChange|false {
		try {
			$shift = $this->shiftService->getExtended($shift);
			return $this->calendarChangeMapper->create(
				$shift->shiftType->group->getGID(),
				$shift->user->getUID(),
				$shift->id,
			);
		} catch (Throwable) {
			return false;
		}
	}
}
