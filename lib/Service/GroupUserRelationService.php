<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Service;

use OCP\IGroupManager;

class GroupUserRelationService {
	public function __construct(
		private string $userId,
		private IGroupManager $groupManager,
	) {
	}

	/**
	 * Checks if the `$userId` or the logged-in user is in `$groupId`
	 *
	 * @param string $groupId
	 * @param null|string $userId If `null`, the logged-in user is used
	 *
	 * @return bool
	 */
	public function isMember(string $groupId, ?string $userId = null): bool {
		return $this->groupManager->isInGroup($userId ?? $this->userId, $groupId);
	}
}
