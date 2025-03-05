<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method ?string getUserId()
 * @method void setUserId(?string $userId)
 * @method ?bool getApproved()
 * @method void setApproved(?bool $approved)
 */
class ShiftExchangeApproval extends Entity {
	/** @var ?string */
	protected $userId;
	/** @var ?bool */
	protected $approved;

	public function __construct() {
		$this->addType('userId', 'string');
		$this->addType('approved', 'boolean');
	}
}
