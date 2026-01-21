<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Exception;

use Exception;

final class VersionMismatchException extends Exception {
	public function __construct() {
		parent::__construct(
			'The client app version does not match the installed app version'
		);
	}
}
