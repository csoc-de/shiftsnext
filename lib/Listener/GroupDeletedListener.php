<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Listener;

use OCA\ShiftsNext\Db\GroupShiftAdminRelationMapper;
use OCA\ShiftsNext\Db\ShiftTypeMapper;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\Group\Events\GroupDeletedEvent;

use function array_walk;

/**
 * @implements IEventListener<GroupDeletedEvent>
 */
class GroupDeletedListener implements IEventListener {
	public function __construct(
		private ShiftTypeMapper $typeMapper,
		private GroupShiftAdminRelationMapper $relationMapper,
	) {
	}

	public function handle(Event $event): void {
		if (!($event instanceof GroupDeletedEvent)) {
			return;
		}
		$groupId = $event->getGroup()->getGID();

		$types = $this->typeMapper->findAll([$groupId]);
		array_walk($types, $this->typeMapper->delete(...));

		$relations = $this->relationMapper->findAll([$groupId]);
		array_walk($relations, $this->relationMapper->delete(...));
	}
}
