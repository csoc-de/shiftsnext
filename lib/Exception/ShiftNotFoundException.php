<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Exception;

use OCP\AppFramework\Db\DoesNotExistException;

final class ShiftNotFoundException extends DoesNotExistException {
	public function __construct(string $message = 'Shift not found') {
		parent::__construct($message);
	}
}
