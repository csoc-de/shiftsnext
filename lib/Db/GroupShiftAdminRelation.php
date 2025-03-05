<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method string getGroupId()
 * @method void setGroupId(string $groupId)
 * @method string getUserId()
 * @method void setUserId(string $userId)
 */
class GroupShiftAdminRelation extends Entity {
	/** @var string */
	protected $groupId;
	/** @var string */
	protected $userId;

	public function __construct() {
		$this->addType('groupId', 'string');
		$this->addType('userId', 'string');
	}
}
