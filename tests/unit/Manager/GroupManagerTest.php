<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Tests;

use Exception;
use OC;
use OCA\ShiftsNext\AppInfo\Application;
use OCP\IGroupManager;
use OCP\IUserManager;
use Test\TestCase;

/**
 * @group DB
 */
class GroupManagerTest extends TestCase {
	private IGroupManager $groupManager;
	private IUserManager $userManager;

	/** @var array<int,array{groupName:string,userIds:string[]}> */
	private array $testRelationsGroupedByGroup = [
		[
			'groupName' => 'Black Team',
			'userIds' => ['user1','user2']
		],
		[
			'groupName' => 'Blue Team',
			'userIds' => ['user1', 'user2', 'user3', 'user4']
		],
		[
			'groupName' => 'Dev Team',
			'userIds' => ['user5', 'user6']
		],
	];

	public function setUp(): void {
		parent::setUp();

		OC::$server->getAppManager()->enableApp(Application::APP_ID);

		$this->groupManager = OC::$server->get(IGroupManager::class);
		$this->userManager = OC::$server->get(IUserManager::class);
	}

	public function testCreateGroupsAndAddUsers() {
		foreach ($this->testRelationsGroupedByGroup as $relation) {
			$group = $this->groupManager->createGroup($relation['groupName']);
			if ($group === null) {
				throw new Exception(
					"Failed to create group with name {$relation['groupName']}"
				);
			}
			foreach ($relation['userIds'] as $userId) {
				$user = $this->userManager->get($userId);
				if ($user === null) {
					throw new Exception("Failed to get user with ID $userId");
				}
				$group->addUser($user);
			}
		}
	}
}
