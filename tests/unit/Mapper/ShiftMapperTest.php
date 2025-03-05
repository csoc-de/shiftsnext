<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Tests;

use OC;
use OCA\ShiftsNext\AppInfo\Application;
use OCA\ShiftsNext\Db\ShiftMapper;
use Test\TestCase;

/**
 * @psalm-type ShiftShape = array{
 *     userId: string,
 *     shiftTypeId: int,
 *     start: string,
 *     end: string,
 * }
 *
 * @group DB
 */
class ShiftMapperTest extends TestCase {
	private ShiftMapper $mapper;

	/** @var list<ShiftShape> */
	private array $testShifts = [
		[
			'userId' => 'user1',
			'shiftTypeId' => 1, // Black Team Early shift
			'start' => '2025-01-01T07:00:00.000Z',
			'end' => '2025-01-01T11:30:00.000Z',
		], [
			'userId' => 'user2',
			'shiftTypeId' => 2, // Black Team Late shift
			'start' => '2025-01-01T11:30:00.000Z',
			'end' => '2025-01-01T16:00:00.000Z',
		], [
			'userId' => 'user3',
			'shiftTypeId' => 3, // Blue Team Early shift
			'start' => '2025-01-01T05:00:00.000Z',
			'end' => '2025-01-01T13:00:00.000Z',
		], [
			'userId' => 'user4',
			'shiftTypeId' => 4, // Blue Team Late shift
			'start' => '2025-01-01T13:00:00.000Z',
			'end' => '2025-01-01T21:00:00.000Z',
		], [
			'userId' => 'user2',
			'shiftTypeId' => 6, // Black Team Office duty
			'start' => '2024-12-30',
			'end' => '2025-01-05',
		],
	];

	public function setUp(): void {
		parent::setUp();

		OC::$server->getAppManager()->enableApp(Application::APP_ID);

		$this->mapper = OC::$server->get(ShiftMapper::class);
	}

	public function testCreate() {
		foreach ($this->testShifts as $testShift) {
			$this->mapper->create(...$testShift);
		}
	}
}
