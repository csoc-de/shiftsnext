<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Exception;

use OCP\AppFramework\Db\DoesNotExistException;

final class ShiftExchangeNotFoundException extends DoesNotExistException {
	public function __construct(string $message = 'ShiftExchange not found') {
		parent::__construct($message);
	}
}
