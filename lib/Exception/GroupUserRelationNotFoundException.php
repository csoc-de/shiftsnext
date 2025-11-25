<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Exception;

use OCP\AppFramework\Db\DoesNotExistException;

final class GroupUserRelationNotFoundException extends DoesNotExistException {
	public function __construct(string $message = 'GroupUserRelation not found') {
		parent::__construct($message);
	}
}
