<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Tests;

use OC;
use OCA\ShiftsNext\AppInfo\Application;
use OCA\ShiftsNext\Db\ShiftExchangeApprovalMapper;
use Test\TestCase;

/**
 * @group DB
 */
class ShiftExchangeApprovalMapperTest extends TestCase {
	private ShiftExchangeApprovalMapper $mapper;

	/** @var array<int,array{userId:null|string,approved:null|bool}> */
	private array $testApprovals = [
		['userId' => 'user1', 'approved' => null],
		['userId' => 'user2', 'approved' => true],
		['userId' => null, 'approved' => null],
		['userId' => 'user3', 'approved' => null],
		['userId' => 'user4', 'approved' => null],
		['userId' => null, 'approved' => null],
	];

	public function setUp(): void {
		parent::setUp();

		OC::$server->getAppManager()->enableApp(Application::APP_ID);

		$this->mapper = OC::$server->get(ShiftExchangeApprovalMapper::class);
	}

	public function testCreate() {
		foreach ($this->testApprovals as $testApproval) {
			$this->mapper->create(...$testApproval);
		}
	}
}
