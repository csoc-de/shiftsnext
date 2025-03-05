<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Exception;

use Exception;

class CalendarNotFoundException extends Exception {
	public function __construct(string $message = 'Calendar not found') {
		parent::__construct($message);
	}
}
