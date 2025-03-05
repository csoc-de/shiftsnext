<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Exception;

use OCP\AppFramework\Db\DoesNotExistException;

class ShiftTypeNotFoundException extends DoesNotExistException {
	public function __construct(string $message = 'ShiftType not found') {
		parent::__construct($message);
	}
}
