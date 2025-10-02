<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Service;

use OCA\ShiftsNext\Exception\UserNotFoundException;
use OCA\ShiftsNext\Serializable\SerializableUser;
use OCP\IGroupManager;
use OCP\IUser;
use OCP\IUserManager;
use OCP\Util;

use function array_filter;
use function array_map;
use function array_merge;
use function array_unique;
use function in_array;
use function usort;

use const SORT_REGULAR;

class UserService {
	public function __construct(
		private IUserManager $userManager,
		private IGroupManager $groupManager,
	) {
	}

	/**
	 * @throws UserNotFoundException if `$user` is a string ID and no
	 *                               User with that ID exists
	 */
	public function get(string|IUser $user): IUser {
		if ($user instanceof IUser) {
			return $user;
		}
		$instance = $this->userManager->get($user);
		if (!$instance) {
			throw new UserNotFoundException("User with ID $user not found");
		}
		return $instance;
	}

	/**
	 * @param null|string[] $userIds If not `null`, only users whose ID is an
	 *                               element of `$userIds` are returned
	 * @param null|string[] $groupIds If not `null`, only users who are a member
	 *                                of at least one group inside `$groupIds` are returned
	 *
	 * @return IUser[]
	 */
	public function getAll(
		?array $userIds = null,
		?array $groupIds = null,
	): array {
		if ($groupIds !== null) {
			$users = [];
			foreach ($groupIds as $groupId) {
				$users = array_merge(
					$users,
					$this->groupManager->get($groupId)?->getUsers() ?? [],
				);
			}
			$users = array_unique($users, SORT_REGULAR);
		} else {
			$users = $this->userManager->search('');
		}
		if ($userIds !== null) {
			$users = array_filter(
				$users,
				fn (IUser $user) => in_array($user->getUID(), $userIds, true),
			);
		}
		usort(
			$users,
			fn (IUser $a, IUser $b)
				=> Util::naturalSortCompare(
					$a->getDisplayName(),
					$b->getDisplayName(),
				),
		);
		return $users;
	}

	/**
	 * @throws UserNotFoundException {@see OCA\ShiftsNext\Service\UserService::get()}
	 *
	 * @psalm-suppress PossiblyUnusedMethod Currently unused
	 */
	public function getSerializable(string|IUser $user): SerializableUser {
		return new SerializableUser($this->get($user));
	}

	/**
	 * @param null|string[] $userIds If not `null`, only users whose ID is an
	 *                               element of `$userIds` are returned
	 * @param null|string[] $groupIds If not `null`, only users who are a member
	 *                                of at least one group inside `$groupIds` are returned
	 *
	 * @return SerializableUser[]
	 */
	public function getAllSerializable(
		?array $userIds = null,
		?array $groupIds = null,
	): array {
		return array_map(
			fn ($user) => new SerializableUser($user),
			$this->getAll($userIds, $groupIds),
		);
	}
}
