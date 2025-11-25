<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Service;

use Exception;
use OCA\ShiftsNext\Db\GroupUserRelation;
use OCA\ShiftsNext\Db\GroupUserRelationMapper;
use OCA\ShiftsNext\Exception\GroupNotFoundException;
use OCA\ShiftsNext\Exception\GroupUserRelationNotFoundException;
use OCA\ShiftsNext\Exception\UserNotFoundException;
use OCA\ShiftsNext\Extended\GroupUserRelationExtended;
use OCA\ShiftsNext\Psalm\GroupUserRelationAlias;
use OCA\ShiftsNext\Serializable\SerializableGroup;
use OCA\ShiftsNext\Serializable\SerializableUser;
use OCP\IGroup;
use OCP\IGroupManager;
use OCP\IUser;

use function array_filter;
use function array_map;


use function array_values;

/**
 * @psalm-import-type GroupUserRow from GroupUserRelationAlias
 * @psalm-import-type GroupUserRelationsByGroup from GroupUserRelationAlias
 */
final class GroupUserRelationService {
	public function __construct(
		private string $userId,
		private IGroupManager $groupManager,
		private GroupUserRelationMapper $groupUserRelationMapper,
		private GroupService $groupService,
		private UserService $userService,
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

	/**
	 * Gets all relations grouped by groups. Each element in the returned
	 * array itself is an associative array with a 'group' field and a 'users'
	 * field which contains the corresponding users (members) for that group
	 *
	 * @param null|string[] $groupIds If not `null`, only grouped relations with
	 *                                the matching group IDs are returned. If `null`, the returned array will
	 *                                contain an element for every group with its 'users' field set to an empty
	 *                                array if there are no corresponding users (members) for that group.
	 *
	 * @return GroupUserRelationsByGroup[]
	 */
	public function getAllGroupedByGroup(?array $groupIds = null): array {
		$relations = $this->getAllExtended($groupIds);
		$groups = $this->groupService->getAll($groupIds);
		$groupedRelations = [];
		foreach ($groups as $group) {
			$matchingRelations = array_filter(
				$relations,
				fn (GroupUserRelationExtended $relation)
					=> $relation->group->getGID() === $group->getGID(),
			);
			$matchingRelations = array_values($matchingRelations);
			$groupedRelations[] = [
				'group' => new SerializableGroup($group),
				'users' => array_map(
					fn ($relation) => new SerializableUser($relation->user),
					$matchingRelations,
				),
			];
		}
		return $groupedRelations;
	}

	/**
	 * @param GroupUserRow|GroupUserRelation|GroupUserRelationExtended $groupUserRelation
	 *
	 * @throws GroupUserRelationNotFoundException {@see OCA\ShiftsNext\Db\GroupUserRelationMapper::findById()}
	 * @throws GroupNotFoundException {@see OCA\ShiftsNext\Service\GroupService::get()}
	 * @throws UserNotFoundException {@see OCA\ShiftsNext\Service\UserService::get()}
	 */
	public function getExtended(
		array|GroupUserRelation|GroupUserRelationExtended $groupUserRelation,
		null|string|IGroup $group = null,
		null|string|IUser $user = null,
	): GroupUserRelationExtended {
		if ($groupUserRelation instanceof GroupUserRelationExtended) {
			return $groupUserRelation;
		}
		$groupUserRelation = $this->groupUserRelationMapper->findById($groupUserRelation);

		$group ??= $groupUserRelation->getGid();
		$group = $this->groupService->get($group);

		$user ??= $groupUserRelation->getUid();
		$user = $this->userService->get($user);

		return new GroupUserRelationExtended($group, $user);
	}

	/**
	 * Multiple parameters are combined with a logical `AND`
	 *
	 * @param null|string[] $groupIds Adds `WHERE gid IN($groupIds)`
	 * @param null|string[] $userIds Adds `WHERE uid IN($userIds)`
	 *
	 * @return GroupUserRelationExtended[]
	 *
	 * @throws Exception {@see OCA\ShiftsNext\Service\GroupUserRelationService::getExtended()}
	 */
	public function getAllExtended(
		?array $groupIds = null,
		?array $userIds = null,
	): array {
		$groupUserRelations = $this->groupUserRelationMapper->findAll($groupIds, $userIds);
		return array_map($this->getExtended(...), $groupUserRelations);
	}
}
