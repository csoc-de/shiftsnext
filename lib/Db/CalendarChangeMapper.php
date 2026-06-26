<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

/**
 * @extends QBMapper<CalendarChange>
 */
final class CalendarChangeMapper extends QBMapper {
	public const string TABLE_NAME = 'shnext_cal_changes';

	public function __construct(IDBConnection $db) {
		parent::__construct($db, self::TABLE_NAME);
	}
}
