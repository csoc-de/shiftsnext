<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Service;

use Exception;
use JsonSerializable;
use OCA\ShiftsNext\Db\GroupShiftAdminRelation;
use OCA\ShiftsNext\Db\GroupShiftAdminRelationMapper;
use OCA\ShiftsNext\Exception\GroupNotFoundException;
use OCA\ShiftsNext\Exception\GroupShiftAdminRelationNotFoundException;
use OCA\ShiftsNext\Exception\UserNotFoundException;
use OCA\ShiftsNext\Psalm\GroupShiftAdminRelationAlias;
use OCP\IGroup;
use OCP\IUser;

use function array_all;
use function array_any;
use function array_diff;
use function array_filter;
use function array_map;
use function array_values;
use function array_walk;
use function in_array;

/**
 * @psalm-import-type GroupShiftAdminRelationsByGroup from GroupShiftAdminRelationAlias
 */
class GroupShiftAdminRelationService {
	public function __construct(
		private string $userId,
		private GroupShiftAdminRelationMapper $groupShiftAdminRelationMapper,
		private GroupService $groupService,
		private UserService $userService,
	) {
	}

	/**
	 * Gets the group IDs for which the logged-in user has shift admin privileges
	 *
	 * @return string[]
	 */
	public function getShiftAdminGroupIds(): array {
		$groupShiftAdminRelations = $this->groupShiftAdminRelationMapper->findAll(
			userIds: [$this->userId],
		);
		return array_map(
			fn ($groupShiftAdminRelation) => $groupShiftAdminRelation->getGroupId(),
			$groupShiftAdminRelations,
		);
	}

	/**
	 * Checks if the logged-in user is a group shift admin of all `$groupIds`
	 *
	 * @param string[] $groupIds
	 *
	 * @return bool
	 */
	public function isShiftAdminAll(array $groupIds): bool {
		$shiftAdminGroupIds = $this->getShiftAdminGroupIds();
		return array_all(
			$groupIds,
			fn (string $groupId) => in_array($groupId, $shiftAdminGroupIds, true),
		);
	}

	/**
	 * Checks if the logged-in user is a group shift admin of any of the `$groupIds`
	 *
	 * @param string[] $groupIds
	 *
	 * @return bool
	 *
	 * @psalm-suppress PossiblyUnusedMethod Currently unused
	 */
	public function isShiftAdminAny(array $groupIds): bool {
		$shiftAdminGroupIds = $this->getShiftAdminGroupIds();
		return array_any(
			$groupIds,
			fn (string $groupId) => in_array($groupId, $shiftAdminGroupIds, true),
		);
	}

	/**
	 * Checks if the logged-in user is a group shift admin of `$groupId`
	 *
	 * @param string $groupId
	 *
	 * @return bool
	 */
	public function isShiftAdmin(string $groupId): bool {
		return in_array($groupId, $this->getShiftAdminGroupIds(), true);
	}

	/**
	 * Gets all relations grouped by groups. Each element in the returned
	 * array itself is an associative array with a 'group' field and a 'users'
	 * field which contains the corresponding users (admins) for that group
	 *
	 * @param null|string[] $groupIds If not `null`, only grouped relations with
	 *                                the matching group IDs are returned. If `null`, the returned array will
	 *                                contain an element for every group with its 'users' field set to an empty
	 *                                array if there are no corresponding users (admins) for that group.
	 *
	 * @return GroupShiftAdminRelationsByGroup[]
	 */
	public function getAllGroupedByGroup(?array $groupIds = null): array {
		$relations = $this->getAllExtended($groupIds);
		$groups = $this->groupService->getAll($groupIds);
		$groupedRelations = [];
		foreach ($groups as $group) {
			$matchingRelations = array_filter(
				$relations,
				fn (GroupShiftAdminRelationExtended $relation)
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
	 * Creates/deletes multiple relations for `$groupId`
	 *
	 * For the given `$groupId`, all current relations, whose user ID is not one
	 * of `$userIds`, are deleted.
	 *
	 * Conversely, new relations are created for every element in `$userIds`,
	 * that is not in the list of the current relations's user IDs
	 *
	 * @param string $groupId
	 * @param string[] $userIds
	 *
	 * @return GroupShiftAdminRelationsByGroup
	 */
	public function updateAllOfGroup(string $groupId, array $userIds): array {
		$currentRelations = $this->groupShiftAdminRelationMapper->findAll([$groupId]);
		$currentUserIds = array_map(fn ($relation) => $relation->getUserId(), $currentRelations);
		$deletionUserIds = array_diff($currentUserIds, $userIds);
		$creationUserIds = array_diff($userIds, $currentUserIds);

		$deletionRelations = array_filter(
			$currentRelations,
			fn ($relation) => in_array($relation->getUserId(), $deletionUserIds, true),
		);

		array_walk($deletionRelations, $this->groupShiftAdminRelationMapper->deleteById(...));

		foreach ($creationUserIds as $userId) {
			$this->groupShiftAdminRelationMapper->create($groupId, $userId);
		}
		return $this->getAllGroupedByGroup([$groupId])[0];
	}

	/**
	 * @throws GroupShiftAdminRelationNotFoundException {@see OCA\ShiftsNext\Db\GroupShiftAdminRelationMapper::findById()}
	 * @throws GroupNotFoundException {@see OCA\ShiftsNext\Service\GroupService::get()}
	 * @throws UserNotFoundException {@see OCA\ShiftsNext\Service\UserService::get()}
	 */
	public function getExtended(
		int|GroupShiftAdminRelation|GroupShiftAdminRelationExtended $groupShiftAdminRelation,
		null|string|IGroup $group = null,
		null|string|IUser $user = null,
	): GroupShiftAdminRelationExtended {
		if ($groupShiftAdminRelation instanceof GroupShiftAdminRelationExtended) {
			return $groupShiftAdminRelation;
		}
		$groupShiftAdminRelation = $this->groupShiftAdminRelationMapper->findById($groupShiftAdminRelation);

		$group ??= $groupShiftAdminRelation->getGroupId();
		$group = $this->groupService->get($group);

		$user ??= $groupShiftAdminRelation->getUserId();
		$user = $this->userService->get($user);

		return new GroupShiftAdminRelationExtended($groupShiftAdminRelation, $group, $user);
	}

	/**
	 * Multiple parameters are combined with a logical `AND`
	 *
	 * @param null|string[] $groupIds Adds `WHERE group_id IN($groupIds)`
	 * @param null|string[] $userIds Adds `WHERE user_id IN($userIds)`
	 *
	 * @return GroupShiftAdminRelationExtended[]
	 *
	 * @throws Exception {@see OCA\ShiftsNext\Service\GroupShiftAdminRelationService::getExtended()}
	 */
	public function getAllExtended(
		?array $groupIds = null,
		?array $userIds = null,
	): array {
		$groupShiftAdminRelations = $this->groupShiftAdminRelationMapper->findAll($groupIds, $userIds);
		return array_map($this->getExtended(...), $groupShiftAdminRelations);
	}
}

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

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'group' => new SerializableGroup($this->group),
			'user' => new SerializableUser($this->user),
		];
	}
}
