<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Db;

use OCA\ShiftsNext\Exception\CalendarChangeNotFoundException;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @extends QBMapper<CalendarChange>
 */
final class CalendarChangeMapper extends QBMapper {
	public const string TABLE_NAME = 'shnext_cal_changes';

	public function __construct(IDBConnection $db) {
		parent::__construct($db, self::TABLE_NAME);
	}

	/**
	 * @param null|string[] $groupIds Adds `WHERE group_id IN($groupIds)`
	 * @param null|int $shiftId Adds `WHERE shift_id = $shiftId`
	 *
	 * @return CalendarChange[]
	 */
	public function findAll(
		?array $groupIds = null,
		?int $shiftId = null,
	): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName());

		if ($groupIds !== null) {
			$qb->andWhere(
				$qb->expr()->in(
					'group_id',
					$qb->createNamedParameter(
						$groupIds,
						IQueryBuilder::PARAM_STR_ARRAY,
					),
				),
			);
		}

		if ($shiftId !== null) {
			$qb->andWhere(
				$qb->expr()->eq(
					'shift_id',
					$qb->createNamedParameter(
						$shiftId,
						IQueryBuilder::PARAM_INT,
					),
				),
			);
		}

		$qb->orderBy('id', 'ASC');

		return $this->findEntities($qb);
	}

	/**
	 * @throws CalendarChangeNotFoundException if `$calendarChange` is an int ID
	 *                                         and no CalendarChange with that ID exists
	 */
	public function findById(int|CalendarChange $calendarChange): CalendarChange {
		if ($calendarChange instanceof CalendarChange) {
			return $calendarChange;
		}
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName())
			->where(
				$qb->expr()->eq(
					'id',
					$qb->createNamedParameter(
						$calendarChange,
						IQueryBuilder::PARAM_INT,
					),
				),
			);

		try {
			return $this->findEntity($qb);
		} catch (DoesNotExistException $e) {
			throw new CalendarChangeNotFoundException($e->getMessage());
		}
	}

	public function create(
		string $groupId,
		string $userId,
		int $shiftId,
	): CalendarChange {
		$calendarChange = new CalendarChange();
		$calendarChange->setGroupId($groupId);
		$calendarChange->setUserId($userId);
		$calendarChange->setShiftId($shiftId);
		return $this->insert($calendarChange);
	}

	/**
	 * @throws CalendarChangeNotFoundException
	 *                                         {@see OCA\ShiftsNext\Db\CalendarChangeMapper::findById()}
	 * @psalm-suppress PossiblyUnusedReturnValue
	 */
	public function deleteById(
		int|CalendarChange $calendarChange,
	): CalendarChange {
		$calendarChange = $this->findById($calendarChange);
		return $this->delete($calendarChange);
	}
}
