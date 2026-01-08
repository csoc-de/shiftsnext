<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Exception;

use Throwable;

interface LocalizedThrowable extends Throwable {
	/**
	 * Gets the localized message
	 *
	 * @return string
	 */
	public function getLocalizedMessage(): string;
}
