<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Service;

use OC;

abstract class AbstractService {

	/** Returns the late static binding instance from the DI container */
	public static function get(): static {
		return OC::$server->get(static::class);
	}
}
