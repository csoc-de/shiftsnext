<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Exception;

use OCP\AppFramework\Db\DoesNotExistException;

final class GroupShiftAdminRelationNotFoundException extends DoesNotExistException {
	public function __construct(string $message) {
		parent::__construct($message);
	}
}
