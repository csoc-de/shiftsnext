<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Tests;

use OC;
use OCA\ShiftsNext\AppInfo\Application;
use OCP\IGroupManager;
use OCP\IUserManager;
use Test\TestCase;

use function array_walk;

/**
 * @group DB
 */
class GroupManagerTest extends TestCase {
	private IGroupManager $groupManager;
	private IUserManager $userManager;

	/** @var array<int,array{groupId:string,userId:string}> */
	private array $testRelations = [
		['groupId' => 'Black Team', 'userId' => 'user1'],
		['groupId' => 'Black Team', 'userId' => 'user2'],
		['groupId' => 'Blue Team', 'userId' => 'user1'],
		['groupId' => 'Blue Team', 'userId' => 'user2'],
		['groupId' => 'Blue Team', 'userId' => 'user3'],
		['groupId' => 'Blue Team', 'userId' => 'user4'],
		['groupId' => 'Dev Team', 'userId' => 'user5'],
		['groupId' => 'Dev Team', 'userId' => 'user6'],
	];

	public function setUp(): void {
		parent::setUp();

		OC::$server->getAppManager()->enableApp(Application::APP_ID);

		$this->groupManager = OC::$server->get(IGroupManager::class);
		$this->userManager = OC::$server->get(IUserManager::class);
	}

	public function testCreateGroups() {
		/** @var string[] */
		$groupIds = array_unique(array_column($this->testRelations, 'groupId'));
		array_walk($groupIds, $this->groupManager->createGroup(...));
	}

	public function testAddUsers() {
		foreach ($this->testRelations as $testRelation) {
			$user = $this->userManager->get($testRelation['userId']);
			$this->groupManager->get($testRelation['groupId'])->addUser($user);
		}
	}
}
