<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Enum;

enum ExchangeApprovalType: string {
	case Users = 'users';
	case Admin = 'admin';
	case All = 'all';
}
