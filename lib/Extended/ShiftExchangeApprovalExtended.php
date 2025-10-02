<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Extended;

use JsonSerializable;
use OCA\ShiftsNext\Db\ShiftExchangeApproval;
use OCA\ShiftsNext\Serializable\SerializableUser;
use OCP\IUser;

/**
 * This class is used to create objects representing a shift exchange approval
 * with all its foreign keys resolved to full objects
 */
final class ShiftExchangeApprovalExtended implements JsonSerializable {
	public int $id;
	public ?bool $approved;

	public function __construct(
		ShiftExchangeApproval $shiftExchangeApproval,
		public ?IUser $user,
	) {
		$this->id = $shiftExchangeApproval->getId();
		$this->approved = $shiftExchangeApproval->getApproved();
	}

	#[\Override]
	public function jsonSerialize(): array {
		$data = [
			'id' => $this->id,
			'user' => $this->user ? new SerializableUser($this->user) : $this->user,
			'approved' => $this->approved,
		];
		return $data;
	}
}
