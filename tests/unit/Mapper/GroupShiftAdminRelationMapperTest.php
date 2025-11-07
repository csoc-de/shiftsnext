<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Tests;

use OC;
use OCA\ShiftsNext\AppInfo\Application;
use OCA\ShiftsNext\Db\GroupShiftAdminRelationMapper;
use Test\TestCase;

/**
 * @group DB
 */
class GroupShiftAdminRelationMapperTest extends TestCase {
	private GroupShiftAdminRelationMapper $mapper;

	/** @var array<int,array{groupId:string,userId:string}> */
	private array $testRelations = [
		['groupId' => 'Black_Team', 'userId' => 'admin'],
		['groupId' => 'Black_Team', 'userId' => 'alice'],
		['groupId' => 'Blue_Team', 'userId' => 'admin'],
		['groupId' => 'Blue_Team', 'userId' => 'bob'],
		['groupId' => 'Dev_Team', 'userId' => 'admin'],
		['groupId' => 'Dev_Team', 'userId' => 'jane'],
	];

	public function setUp(): void {
		parent::setUp();

		OC::$server->getAppManager()->enableApp(Application::APP_ID);

		$this->mapper = OC::$server->get(GroupShiftAdminRelationMapper::class);
	}

	public function testCreate() {
		foreach ($this->testRelations as $testRelation) {
			$this->mapper->create(...$testRelation);
		}
	}
}
