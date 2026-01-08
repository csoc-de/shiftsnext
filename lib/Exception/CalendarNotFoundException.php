<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Exception;

use Exception;

final class CalendarNotFoundException extends Exception {
	public function __construct(string $message) {
		parent::__construct($message);
	}
}
