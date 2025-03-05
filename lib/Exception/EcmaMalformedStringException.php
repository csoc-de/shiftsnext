<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Exception;

use Exception;

class EcmaMalformedStringException extends Exception {
	public function __construct(string $message = 'ECMA string malformed') {
		parent::__construct($message);
	}
}
