<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Enum;

enum ExchangeNotificationSubject: string {
	case CreatedByParticipant = 'created_by_participant';
	case CreatedByAdmin = 'created_by_admin';
	case DeletedByParticipant = 'deleted_by_participant';
	case DeletedByAdmin = 'deleted_by_admin';
	case Approved = 'approved';
	case Rejected = 'rejected';
}
