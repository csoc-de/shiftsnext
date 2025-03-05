<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Exception;

use OCP\AppFramework\Db\DoesNotExistException;

class GroupShiftAdminRelationNotFoundException extends DoesNotExistException {
	public function __construct(string $message = 'GroupShiftAdminRelation not found') {
		parent::__construct($message);
	}
}
