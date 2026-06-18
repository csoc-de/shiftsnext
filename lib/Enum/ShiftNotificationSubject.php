<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Enum;

enum ShiftNotificationSubject: string {
	case Assigned = 'assigned';
	case Moved = 'moved';
	case Deleted = 'deleted';
}
