<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Service;

use JsonSerializable;
use OCA\ShiftsNext\Db\ShiftExchangeApproval;
use OCA\ShiftsNext\Db\ShiftExchangeApprovalMapper;
use OCA\ShiftsNext\Exception\ShiftExchangeApprovalNotFoundException;
use OCA\ShiftsNext\Exception\UserNotFoundException;
use OCP\IUser;

class ShiftExchangeApprovalService {
	public function __construct(
		private ShiftExchangeApprovalMapper $shiftExchangeApprovalMapper,
		private UserService $userService,
	) {
	}

	/**
	 * @throws ShiftExchangeApprovalNotFoundException {@see OCA\ShiftsNext\Db\ShiftExchangeApprovalMapper::findById()}
	 * @throws UserNotFoundException {@see OCA\ShiftsNext\Service\UserService::get()}
	 */
	public function getExtended(
		int|ShiftExchangeApproval|ShiftExchangeApprovalExtended $shiftExchangeApproval,
		null|string|IUser $user = null,
	): ShiftExchangeApprovalExtended {
		if ($shiftExchangeApproval instanceof ShiftExchangeApprovalExtended) {
			return $shiftExchangeApproval;
		}
		$shiftExchangeApproval = $this->shiftExchangeApprovalMapper->findById($shiftExchangeApproval);

		$user ??= $shiftExchangeApproval->getUserId();
		if ($user !== null) {
			$user = $this->userService->get($user);
		}

		return new ShiftExchangeApprovalExtended(
			$shiftExchangeApproval,
			$user,
		);
	}
}

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

	public function jsonSerialize(): array {
		$data = [
			'id' => $this->id,
			'user' => $this->user ? new SerializableUser($this->user) : $this->user,
			'approved' => $this->approved,
		];
		return $data;
	}
}
