<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Service;

use OCP\Server;

abstract class AbstractService {

	/** Returns the late static binding instance from the DI container */
	public static function get(): static {
		return Server::get(static::class);
	}
}
