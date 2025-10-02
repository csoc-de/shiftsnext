<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method string getUserId()
 * @method void setUserId(string $userId)
 * @method int getShiftTypeId()
 * @method void setShiftTypeId(int $shiftTypeId)
 * @method string getStart()
 * @method void setStart(string $start)
 * @method string getEnd()
 * @method void setEnd(string $end)
 */
final class Shift extends Entity {
	/** @var string */
	protected $userId;
	/** @var int */
	protected $shiftTypeId;
	/** @var string */
	protected $start;
	/** @var string */
	protected $end;

	public function __construct() {
		$this->addType('userId', 'string');
		$this->addType('shiftTypeId', 'integer');
		$this->addType('start', 'string');
		$this->addType('end', 'string');
	}
}
