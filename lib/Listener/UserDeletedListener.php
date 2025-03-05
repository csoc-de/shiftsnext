<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Listener;

use OCA\ShiftsNext\Db\GroupShiftAdminRelationMapper;
use OCA\ShiftsNext\Db\ShiftExchangeApprovalMapper;
use OCA\ShiftsNext\Db\ShiftExchangeMapper;
use OCA\ShiftsNext\Db\ShiftMapper;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\User\Events\UserDeletedEvent;

use function array_walk;

/**
 * @implements IEventListener<UserDeletedEvent>
 */
class UserDeletedListener implements IEventListener {
	public function __construct(
		private ShiftMapper $shiftMapper,
		private ShiftExchangeMapper $exchangeMapper,
		private ShiftExchangeApprovalMapper $approvalMapper,
		private GroupShiftAdminRelationMapper $relationMapper,
	) {
	}

	public function handle(Event $event): void {
		if (!($event instanceof UserDeletedEvent)) {
			return;
		}
		$userId = $event->getUser()->getUID();

		$shifts = $this->shiftMapper->findAll(userId: $userId);
		array_walk($shifts, $this->shiftMapper->delete(...));

		$exchanges = $this->exchangeMapper->findAll(transferToUserId: $userId);
		array_walk($exchanges, $this->exchangeMapper->delete(...));

		$approvals = $this->approvalMapper->findAll($userId);
		array_walk($approvals, $this->approvalMapper->delete(...));

		$relations = $this->relationMapper->findAll(userIds: [$userId]);
		array_walk($relations, $this->relationMapper->delete(...));
	}
}
