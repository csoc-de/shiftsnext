<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method string getGroupdId()
 * @method void setGroupId(string $groupId)
 * @method string getUserId()
 * @method void setUserId(string $userId)
 * @method int getShiftId()
 * @method void setShiftId(int $shiftId)
 */
class CalendarChange extends Entity {
	/** @var string */
	protected $groupId;
	/** @var string */
	protected $userId;
	/** @var int */
	protected $shiftId;

	public function __construct() {
		$this->addType('groupId', 'string');
		$this->addType('userId', 'string');
		$this->addType('shiftId', 'integer');
	}
}
