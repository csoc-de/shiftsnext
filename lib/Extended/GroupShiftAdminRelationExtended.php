<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Extended;

use JsonSerializable;
use OCA\ShiftsNext\Db\GroupShiftAdminRelation;
use OCA\ShiftsNext\Serializable\SerializableGroup;
use OCA\ShiftsNext\Serializable\SerializableUser;
use OCP\IGroup;
use OCP\IUser;

/**
 * This class is used to create objects representing a group shift admin relation
 * with all its foreign keys resolved to full objects
 */
final class GroupShiftAdminRelationExtended implements JsonSerializable {
	public int $id;

	public function __construct(
		GroupShiftAdminRelation $groupShiftAdminRelation,
		public IGroup $group,
		public IUser $user,
	) {
		$this->id = $groupShiftAdminRelation->getId();
	}

	#[\Override]
	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'group' => new SerializableGroup($this->group),
			'user' => new SerializableUser($this->user),
		];
	}
}
