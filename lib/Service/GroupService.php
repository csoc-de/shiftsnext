<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Service;

use JsonSerializable;
use OCA\ShiftsNext\Exception\GroupNotFoundException;
use OCP\IGroup;
use OCP\IGroupManager;
use OCP\Util;

use function array_filter;
use function in_array;
use function usort;

class GroupService {
	public function __construct(
		private IGroupManager $groupManager,
	) {
	}

	/**
	 * @throws GroupNotFoundException if `$group` is a string ID and no
	 *                                Group with that ID exists
	 */
	public function get(string|IGroup $group): IGroup {
		if ($group instanceof IGroup) {
			return $group;
		}
		$instance = $this->groupManager->get($group);
		if (!$instance) {
			throw new GroupNotFoundException("Group with ID $group not found");
		}
		return $instance;
	}

	/**
	 * @param null|string[] $groupIds If not `null`, only groups whose ID is an
	 *                                element of `$groupIds` are returned
	 *
	 * @return IGroup[]
	 */
	public function getAll(?array $groupIds = null): array {
		$groups = $this->groupManager->search('');
		if ($groupIds !== null) {
			$groups = array_filter(
				$groups,
				fn (IGroup $group) => in_array($group->getGID(), $groupIds, true),
			);
		}
		usort(
			$groups,
			fn (IGroup $a, IGroup $b)
				=> Util::naturalSortCompare(
					$a->getDisplayName(),
					$b->getDisplayName(),
				),
		);
		return $groups;
	}

	/**
	 * @throws GroupNotFoundException {@see OCA\ShiftsNext\Service\GroupService::get()}
	 *
	 * @psalm-suppress PossiblyUnusedMethod Currently unused
	 */
	public function getSerializable(string|IGroup $group): SerializableGroup {
		return new SerializableGroup($this->get($group));
	}

	/**
	 * @param null|string[] $groupIds If not `null`, only groups whose ID is an
	 *                                element of `$groupIds` are returned
	 *
	 * @return SerializableGroup[]
	 */
	public function getAllSerializable(?array $groupIds = null): array {
		return array_map(
			fn ($group) => new SerializableGroup($group),
			$this->getAll($groupIds),
		);
	}
}

final class SerializableGroup implements JsonSerializable {
	public string $id;
	public string $displayName;

	public function __construct(IGroup $group) {
		$this->id = $group->getGID();
		$this->displayName = $group->getDisplayName();
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'display_name' => $this->displayName,
		];
	}
}
