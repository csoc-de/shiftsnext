<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Tests;

use OC;
use OCA\ShiftsNext\AppInfo\Application;
use OCA\ShiftsNext\Db\ShiftExchangeMapper;
use Test\TestCase;

/**
 * @group DB
 */
class ShiftExchangeMapperTest extends TestCase {
	private ShiftExchangeMapper $mapper;

	/** @var array<int,array<string,mixed>> */
	private array $testExchanges = [
		[
			'shiftAId' => 1,
			'shiftBId' => 2,
			'transferToUserId' => null,
			'comment' => 'Lorem ipsum',
			'done' => false,
			'approved' => null,
			'userAApprovalId' => 1,
			'userBApprovalId' => 2,
			'adminApprovalId' => 3,
		], [
			'shiftAId' => 3,
			'shiftBId' => null,
			'transferToUserId' => 'user4',
			'comment' => 'Lorem ipsum',
			'done' => false,
			'approved' => null,
			'userAApprovalId' => 4,
			'userBApprovalId' => 5,
			'adminApprovalId' => 6,
		],
	];

	public function setUp(): void {
		parent::setUp();

		OC::$server->getAppManager()->enableApp(Application::APP_ID);

		$this->mapper = OC::$server->get(ShiftExchangeMapper::class);
	}

	public function testCreate() {
		foreach ($this->testExchanges as $testExchange) {
			$this->mapper->create(...$testExchange);
		}
	}
}
