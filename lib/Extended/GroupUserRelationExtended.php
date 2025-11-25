<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Extended;

use JsonSerializable;
use OCA\ShiftsNext\Serializable\SerializableGroup;
use OCA\ShiftsNext\Serializable\SerializableUser;
use OCP\IGroup;
use OCP\IUser;

/**
 * This class is used to create objects representing a group user relation
 * with all its foreign keys resolved to full objects
 */
final class GroupUserRelationExtended implements JsonSerializable {
	public function __construct(
		public IGroup $group,
		public IUser $user,
	) {
	}

	#[\Override]
	public function jsonSerialize(): array {
		return [
			'group' => new SerializableGroup($this->group),
			'user' => new SerializableUser($this->user),
		];
	}
}
