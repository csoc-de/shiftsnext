<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Tests;

use OC;
use OCA\ShiftsNext\AppInfo\Application;
use OCA\ShiftsNext\Db\ShiftTypeMapper;
use OCA\ShiftsNext\Psalm\ShiftTypeAlias;
use Test\TestCase;

/**
 * @psalm-import-type Repetition from ShiftTypeAlias
 * @psalm-import-type Caldav from ShiftTypeAlias
 *
 * @psalm-type ShiftTypeShape = array{
 *     groupId: string,
 *     name: string,
 *     description: string,
 *     color: string,
 *     active: bool,
 *     duration: string,
 *     repetition: Repetition,
 *     caldav: Caldav,
 * }
 *
 * @group DB
 */
class ShiftTypeMapperTest extends TestCase {
	private ShiftTypeMapper $mapper;

	/** @var list<ShiftTypeShape> */
	private array $testTypes = [
		[
			'groupId' => 'Black Team',
			'name' => 'Early shift',
			'description' => 'Lorem ipsum',
			'color' => '#1F1F1F',
			'active' => true,
			'repetition' => [
				'frequency' => 'weekly',
				'interval' => 1,
				'weekly_type' => 'by_day',
				'config' => [
					'reference' => '2025-01-01T08:00:00+01:00[Europe/Berlin]',
					'short_day_to_amount_map' => [
						'MO' => 1,
						'TU' => 1,
						'WE' => 1,
						'TH' => 1,
						'FR' => 1,
						'SA' => 0,
						'SU' => 0,
					],
					'duration' => 'PT4H30M',
				],
			],
			'caldav' => ['categories' => 'Control center'],
		], [
			'groupId' => 'Black Team',
			'name' => 'Late shift',
			'description' => 'Lorem ipsum',
			'color' => '#3F3F3F',
			'active' => true,
			'repetition' => [
				'frequency' => 'weekly',
				'interval' => 1,
				'weekly_type' => 'by_day',
				'config' => [
					'reference' => '2025-01-01T12:30:00+01:00[Europe/Berlin]',
					'short_day_to_amount_map' => [
						'MO' => 1,
						'TU' => 1,
						'WE' => 1,
						'TH' => 1,
						'FR' => 1,
						'SA' => 0,
						'SU' => 0,
					],
					'duration' => 'PT4H30M',
				],
			],
			'caldav' => ['categories' => 'Control center'],
		], [
			'groupId' => 'Blue Team',
			'name' => 'Early shift',
			'description' => 'Lorem ipsum',
			'color' => '#00001F',
			'active' => true,
			'repetition' => [
				'frequency' => 'weekly',
				'interval' => 1,
				'weekly_type' => 'by_day',
				'config' => [
					'reference' => '2025-01-01T06:00:00+01:00[Europe/Berlin]',
					'short_day_to_amount_map' => [
						'MO' => 1,
						'TU' => 1,
						'WE' => 1,
						'TH' => 1,
						'FR' => 1,
						'SA' => 0,
						'SU' => 0,
					],
					'duration' => 'PT8H',
				],
			],
			'caldav' => ['categories' => 'Control center'],
		], [
			'groupId' => 'Blue Team',
			'name' => 'Late shift',
			'description' => 'Lorem ipsum',
			'color' => '#00003F',
			'active' => true,
			'repetition' => [
				'frequency' => 'weekly',
				'interval' => 1,
				'weekly_type' => 'by_day',
				'config' => [
					'reference' => '2025-01-01T14:00:00+01:00[Europe/Berlin]',
					'short_day_to_amount_map' => [
						'MO' => 1,
						'TU' => 1,
						'WE' => 1,
						'TH' => 1,
						'FR' => 1,
						'SA' => 0,
						'SU' => 0,
					],
					'duration' => 'PT8H',
				],
			],
			'caldav' => ['categories' => 'Control center'],
		], [
			'groupId' => 'Blue Team',
			'name' => 'Night shift',
			'description' => 'Lorem ipsum',
			'color' => '#00007F',
			'active' => false,
			'repetition' => [
				'frequency' => 'weekly',
				'interval' => 1,
				'weekly_type' => 'by_day',
				'config' => [
					'reference' => '2025-01-01T22:00:00+01:00[Europe/Berlin]',
					'short_day_to_amount_map' => [
						'MO' => 1,
						'TU' => 1,
						'WE' => 1,
						'TH' => 1,
						'FR' => 1,
						'SA' => 0,
						'SU' => 0,
					],
					'duration' => 'PT8H',
				],
			],
			'caldav' => ['categories' => 'Control center'],
		], [
			'groupId' => 'Black Team',
			'name' => 'Office duty',
			'description' => 'Lorem ipsum',
			'color' => '#00FF00',
			'active' => true,
			'repetition' => [
				'frequency' => 'weekly',
				'interval' => 1,
				'weekly_type' => 'by_week',
				'config' => [
					'reference' => '2025-W01',
					'amount' => 1,
				],
			],
			'caldav' => ['categories' => 'Control center'],
		],
	];

	public function setUp(): void {
		parent::setUp();

		OC::$server->getAppManager()->enableApp(Application::APP_ID);

		$this->mapper = OC::$server->get(ShiftTypeMapper::class);
	}

	public function testCreate() {
		foreach ($this->testTypes as $testType) {
			$this->mapper->create(...$testType);
		}
	}
}
