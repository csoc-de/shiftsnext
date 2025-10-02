<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Extended;

use JsonSerializable;
use OCA\ShiftsNext\Db\ShiftExchange;
use OCA\ShiftsNext\Serializable\SerializableUser;
use OCP\IUser;

/**
 * This class is used to create objects representing a shift exchange
 * with all its foreign keys resolved to full objects
 */
final class ShiftExchangeExtended implements JsonSerializable {
	public int $id;
	public string $comment;
	public bool $done;
	public ?bool $approved;

	public function __construct(
		ShiftExchange $shiftExchange,
		public ShiftExtended $shiftA,
		public ?ShiftExtended $shiftB,
		public ?IUser $transferToUser,
		public ShiftExchangeApprovalExtended $userAApproval,
		public ShiftExchangeApprovalExtended $userBApproval,
		public ShiftExchangeApprovalExtended $adminApproval,
	) {
		$this->id = $shiftExchange->getId();
		$this->comment = $shiftExchange->getComment();
		$this->done = $shiftExchange->getDone();
		$this->approved = $shiftExchange->getApproved();
	}

	#[\Override]
	public function jsonSerialize(): array {
		$data = [
			'id' => $this->id,
			'shift_a' => $this->shiftA,
			'comment' => $this->comment,
			'done' => $this->done,
			'approved' => $this->approved,
			'user_a_approval' => $this->userAApproval,
			'user_b_approval' => $this->userBApproval,
			'admin_approval' => $this->adminApproval,
		];
		if ($this->shiftB !== null) {
			$data['shift_b'] = $this->shiftB;
		}
		if ($this->transferToUser !== null) {
			$data['transfer_to_user'] = new SerializableUser($this->transferToUser);
		}
		return $data;
	}
}
