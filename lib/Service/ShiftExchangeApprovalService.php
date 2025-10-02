<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Service;

use OCA\ShiftsNext\Db\ShiftExchangeApproval;
use OCA\ShiftsNext\Db\ShiftExchangeApprovalMapper;
use OCA\ShiftsNext\Exception\ShiftExchangeApprovalNotFoundException;
use OCA\ShiftsNext\Exception\UserNotFoundException;
use OCA\ShiftsNext\Extended\ShiftExchangeApprovalExtended;
use OCP\IUser;

final class ShiftExchangeApprovalService {
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
