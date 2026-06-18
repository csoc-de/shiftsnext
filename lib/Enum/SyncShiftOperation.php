<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Enum;

enum SyncShiftOperation: string {
	case CreateOrUpdate = 'create_or_update';
	case Delete = 'delete';
}
