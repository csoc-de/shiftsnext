<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Db;

use DateTimeImmutable;
use DateTimeZone;
use OCA\ShiftsNext\Exception\ShiftNotFoundException;
use OCA\ShiftsNext\Psalm\ShiftAlias;
use OCA\ShiftsNext\Util\DateTimeInterface;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @psalm-import-type TemporalColumnQueryOptions from ShiftAlias
 *
 * @extends QBMapper<Shift>
 */
final class ShiftMapper extends QBMapper {
	public const string TABLE_NAME = 'shnext_shifts';

	public function __construct(IDBConnection $db) {
		parent::__construct($db, self::TABLE_NAME);
	}

	/**
	 * Multiple parameters are combined with a logical `AND`
	 *
	 * @param null|string[] $groupIds Adds `WHERE group_id IN($groupIds)`
	 * @param null|string $userId Adds `WHERE user_id = $userId`
	 * @param null|TemporalColumnQueryOptions $start
	 * @param null|int $shiftTypeId Adds `WHERE shift_type_id = $shiftTypeId`
	 * @param null|int[] $shiftIds Adds `WHERE id IN($shiftIds)`
	 *
	 * @return Shift[]
	 */
	public function findAll(
		?array $groupIds = null,
		?string $userId = null,
		?array $start = null,
		?int $shiftTypeId = null,
		?array $shiftIds = null,
	): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('s.*')
			->from($this->getTableName(), 's');

		if ($groupIds !== null) {
			$qb->join('s', ShiftTypeMapper::TABLE_NAME, 'st', 's.shift_type_id = st.id');
			$qb->andWhere(
				$qb->expr()->in('st.group_id', $qb->createNamedParameter($groupIds, IQueryBuilder::PARAM_STR_ARRAY)),
			);
		}
		if ($userId !== null) {
			$qb->andWhere(
				$qb->expr()->eq('user_id', $qb->createNamedParameter($userId, IQueryBuilder::PARAM_STR)),
			);
		}
		if ($start !== null) {
			$plainDateStringLength = 10;
			$dateTimeZone = new DateTimeZone($start['timeZone']);
			$utcDateTimeZone = new DateTimeZone('UTC');

			$firstCalendarDate = $start['calendarDates'][0];
			$lastCalendarDate = $start['calendarDates'][1] ?? $firstCalendarDate;

			$minDateTimeString = (new DateTimeImmutable($firstCalendarDate, $dateTimeZone))
				->setTimezone($utcDateTimeZone)
				->format(DateTimeInterface::RFC9557_NC_NF);
			$maxDateTimeString = (new DateTimeImmutable($lastCalendarDate, $dateTimeZone))
				->setTime(23, 59, 59, 999999)
				->setTimezone($utcDateTimeZone)
				->format(DateTimeInterface::RFC9557_NC_NF);

			$dateOnlyArgs = $firstCalendarDate === $lastCalendarDate
				? [
					$qb->expr()->eq('start', $qb->createNamedParameter($firstCalendarDate, IQueryBuilder::PARAM_STR)),
				]
				: [
					$qb->expr()->gte('start', $qb->createNamedParameter($firstCalendarDate, IQueryBuilder::PARAM_STR)),
					$qb->expr()->lte('start', $qb->createNamedParameter($lastCalendarDate, IQueryBuilder::PARAM_STR)),
				];

			$qb->andWhere(
				$qb->expr()->orX(
					$qb->expr()->andX( // Full date time strings
						$qb->expr()->gt($qb->func()->charLength('start'), $qb->createNamedParameter($plainDateStringLength, IQueryBuilder::PARAM_INT)),
						$qb->expr()->gte('start', $qb->createNamedParameter($minDateTimeString, IQueryBuilder::PARAM_STR)),
						$qb->expr()->lte('start', $qb->createNamedParameter($maxDateTimeString, IQueryBuilder::PARAM_STR)),
					),
					$qb->expr()->andX( // Date only strings
						$qb->expr()->eq($qb->func()->charLength('start'), $qb->createNamedParameter($plainDateStringLength, IQueryBuilder::PARAM_INT)),
						...$dateOnlyArgs,
					),
				)
			);
		}
		if ($shiftTypeId !== null) {
			$qb->andWhere(
				$qb->expr()->eq('shift_type_id', $qb->createNamedParameter($shiftTypeId, IQueryBuilder::PARAM_INT)),
			);
		}
		if ($shiftIds !== null) {
			$qb->andWhere(
				$qb->expr()->in('s.id', $qb->createNamedParameter($shiftIds, IQueryBuilder::PARAM_INT_ARRAY)),
			);
		}

		$qb->orderBy('start', 'ASC');

		return $this->findEntities($qb);
	}

	/**
	 * @throws ShiftNotFoundException if `$shift` is an int ID and no
	 *                                Shift with that ID exists
	 */
	public function findById(int|Shift $shift): Shift {
		if ($shift instanceof Shift) {
			return $shift;
		}
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName())
			->where(
				$qb->expr()->eq('id', $qb->createNamedParameter($shift, IQueryBuilder::PARAM_INT)),
			);

		try {
			return $this->findEntity($qb);
		} catch (DoesNotExistException $e) {
			throw new ShiftNotFoundException($e->getMessage());
		}
	}

	public function create(
		string $userId,
		int $shiftTypeId,
		string $start,
		string $end,
	): Shift {
		$shift = new Shift();
		$shift->setUserId($userId);
		$shift->setShiftTypeId($shiftTypeId);
		$shift->setStart($start);
		$shift->setEnd($end);
		return $this->insert($shift);
	}

	/**
	 * @throws ShiftNotFoundException {@see OCA\ShiftsNext\Db\ShiftMapper::findById()}
	 */
	public function updateById(
		int|Shift $shift,
		?string $userId = null,
		?int $shiftTypeId = null,
		?string $start = null,
		?string $end = null,
	): Shift {
		$shift = $this->findById($shift);
		if ($userId !== null) {
			$shift->setUserId($userId);
		}
		if ($shiftTypeId !== null) {
			$shift->setShiftTypeId($shiftTypeId);
		}
		if ($start !== null) {
			$shift->setStart($start);
		}
		if ($end !== null) {
			$shift->setEnd($end);
		}
		return $this->update($shift);
	}

	/**
	 * @throws ShiftNotFoundException {@see OCA\ShiftsNext\Db\ShiftMapper::findById()}
	 */
	public function deleteById(int|Shift $shift): Shift {
		$shift = $this->findById($shift);
		return $this->delete($shift);
	}
}
