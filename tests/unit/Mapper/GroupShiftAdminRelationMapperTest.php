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
		['groupId' => 'Black Team', 'userId' => 'admin'],
		['groupId' => 'Black Team', 'userId' => 'alice'],
		['groupId' => 'Blue Team', 'userId' => 'admin'],
		['groupId' => 'Blue Team', 'userId' => 'bob'],
		['groupId' => 'Dev Team', 'userId' => 'admin'],
		['groupId' => 'Dev Team', 'userId' => 'jane'],
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
