<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Exception;

use OCP\AppFramework\Db\DoesNotExistException;

class CalendarChangeNotFoundException extends DoesNotExistException {
	public function __construct(string $message = 'CalendarChange not found') {
		parent::__construct($message);
	}
}
