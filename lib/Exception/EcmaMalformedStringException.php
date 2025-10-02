<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Exception;

use Exception;

final class EcmaMalformedStringException extends Exception {
	public function __construct(string $message = 'ECMA string malformed') {
		parent::__construct($message);
	}
}
