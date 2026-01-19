<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Service;

use Exception;
use OCA\ShiftsNext\Db\Shift;
use OCA\ShiftsNext\Db\ShiftExchange;
use OCA\ShiftsNext\Db\ShiftExchangeApproval;
use OCA\ShiftsNext\Db\ShiftExchangeMapper;
use OCA\ShiftsNext\Exception\ShiftExchangeApprovalNotFoundException;
use OCA\ShiftsNext\Exception\ShiftExchangeNotFoundException;
use OCA\ShiftsNext\Exception\ShiftNotFoundException;
use OCA\ShiftsNext\Exception\ShiftTypeNotFoundException;
use OCA\ShiftsNext\Exception\UserNotFoundException;
use OCA\ShiftsNext\Extended\ShiftExchangeApprovalExtended;
use OCA\ShiftsNext\Extended\ShiftExchangeExtended;
use OCA\ShiftsNext\Extended\ShiftExtended;
use OCP\IUser;

use function array_map;

final class ShiftExchangeService {
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
	 * @return list<ShiftExchangeExtended>
	 *
	 * @throws Exception {@see OCA\ShiftsNext\Service\ShiftExchangeService::getExtended()}
	 */
	public function getAllExtended(): array {
		$shiftExchanges = $this->shiftExchangeMapper->findAll();
		return array_map($this->getExtended(...), $shiftExchanges);
	}
}
