<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Exception;

use Exception;

final class GroupNotFoundException extends Exception {
	public function __construct(string $message = 'Group not found') {
		parent::__construct($message);
	}
}
