<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method int getShiftAId()
 * @method void setShiftAId(int $shiftAId)
 * @method ?int getShiftBId()
 * @method void setShiftBId(?int $shiftBId)
 * @method ?string getTransferToUserId()
 * @method void setTransferToUserId(?string $transferToUserId)
 * @method string getComment()
 * @method void setComment(string $comment)
 * @method bool getDone()
 * @method void setDone(bool $done)
 * @method ?bool getApproved()
 * @method void setApproved(?bool $approved)
 * @method int getUserAApprovalId()
 * @method void setUserAApprovalId(int $userAApprovalId)
 * @method int getUserBApprovalId()
 * @method void setUserBApprovalId(int $userBApprovalId)
 * @method int getAdminApprovalId()
 * @method void setAdminApprovalId(int $adminApprovalId)
 */
class ShiftExchange extends Entity {
	/** @var int */
	protected $shiftAId;
	/** @var ?int */
	protected $shiftBId;
	/** @var ?string */
	protected $transferToUserId;
	/** @var string */
	protected $comment;
	/** @var bool */
	protected $done;
	/** @var null|bool */
	protected $approved;
	/** @var int */
	protected $userAApprovalId;
	/** @var int */
	protected $userBApprovalId;
	/** @var int */
	protected $adminApprovalId;

	public function __construct() {
		$this->addType('shiftAId', 'integer');
		$this->addType('shiftBId', 'integer');
		$this->addType('transferToUserId', 'string');
		$this->addType('comment', 'string');
		$this->addType('done', 'boolean');
		$this->addType('approved', 'boolean');
		$this->addType('userAApprovalId', 'integer');
		$this->addType('userBApprovalId', 'integer');
		$this->addType('adminApprovalId', 'integer');
	}
}
