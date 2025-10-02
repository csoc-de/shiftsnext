<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Service;

use Exception;
use JsonSerializable;
use OCA\ShiftsNext\Db\Shift;
use OCA\ShiftsNext\Db\ShiftExchange;
use OCA\ShiftsNext\Db\ShiftExchangeApproval;
use OCA\ShiftsNext\Db\ShiftExchangeMapper;
use OCA\ShiftsNext\Exception\ShiftExchangeApprovalNotFoundException;
use OCA\ShiftsNext\Exception\ShiftExchangeNotFoundException;
use OCA\ShiftsNext\Exception\ShiftNotFoundException;
use OCA\ShiftsNext\Exception\ShiftTypeNotFoundException;
use OCA\ShiftsNext\Exception\UserNotFoundException;
use OCA\ShiftsNext\Serializable\SerializableUser;
use OCP\IUser;

use function array_map;

class ShiftExchangeService {
	public function __construct(
		private ShiftExchangeMapper $shiftExchangeMapper,
		private ShiftExchangeApprovalService $shiftExchangeApprovalService,
		private ShiftService $shiftService,
		private UserService $userService,
	) {
	}

	/**
	 * @throws ShiftExchangeNotFoundException {@see OCA\ShiftsNext\Db\ShiftExchangeMapper::findById()}
	 * @throws ShiftNotFoundException {@see OCA\ShiftsNext\Db\ShiftMapper::findById()}
	 * @throws UserNotFoundException {@see OCA\ShiftsNext\Service\UserService::get()}
	 * @throws ShiftTypeNotFoundException {@see OCA\ShiftsNext\Db\ShiftTypeMapper::findById()}
	 * @throws ShiftExchangeApprovalNotFoundException {@see OCA\ShiftsNext\Db\ShiftExchangeApprovalMapper::findById()}
	 */
	public function getExtended(
		int|ShiftExchange|ShiftExchangeExtended $shiftExchange,
		null|int|Shift|ShiftExtended $shiftA = null,
		null|int|Shift|ShiftExtended $shiftB = null,
		null|string|IUser $transferToUser = null,
		null|int|ShiftExchangeApproval|ShiftExchangeApprovalExtended $userAApproval = null,
		null|int|ShiftExchangeApproval|ShiftExchangeApprovalExtended $userBApproval = null,
		null|int|ShiftExchangeApproval|ShiftExchangeApprovalExtended $adminApproval = null,
	): ShiftExchangeExtended {
		if ($shiftExchange instanceof ShiftExchangeExtended) {
			return $shiftExchange;
		}
		$shiftExchange = $this->shiftExchangeMapper->findById($shiftExchange);

		$shiftA ??= $shiftExchange->getShiftAId();
		$shiftA = $this->shiftService->getExtended($shiftA);

		$shiftB ??= $shiftExchange->getShiftBId();
		if ($shiftB !== null) {
			$shiftB = $this->shiftService->getExtended($shiftB);
		}

		$transferToUser ??= $shiftExchange->getTransferToUserId();
		if ($transferToUser !== null) {
			$transferToUser = $this->userService->get($transferToUser);
		}

		$userAApproval ??= $shiftExchange->getUserAApprovalId();
		$userAApproval = $this->shiftExchangeApprovalService->getExtended($userAApproval);

		$userBApproval ??= $shiftExchange->getUserBApprovalId();
		$userBApproval = $this->shiftExchangeApprovalService->getExtended($userBApproval);

		$adminApproval ??= $shiftExchange->getAdminApprovalId();
		$adminApproval = $this->shiftExchangeApprovalService->getExtended($adminApproval);

		return new ShiftExchangeExtended(
			$shiftExchange,
			$shiftA,
			$shiftB,
			$transferToUser,
			$userAApproval,
			$userBApproval,
			$adminApproval,
		);
	}

	/**
	 * @return ShiftExchangeExtended[]
	 *
	 * @throws Exception {@see OCA\ShiftsNext\Service\ShiftExchangeService::getExtended()}
	 */
	public function getAllExtended(): array {
		$shiftExchanges = $this->shiftExchangeMapper->findAll();
		return array_map($this->getExtended(...), $shiftExchanges);
	}
}

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
