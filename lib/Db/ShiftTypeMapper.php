<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Db;

use OCA\ShiftsNext\Exception\ShiftTypeNotFoundException;
use OCA\ShiftsNext\Psalm\ShiftTypeAlias;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @psalm-import-type Repetition from ShiftTypeAlias
 * @psalm-import-type Caldav from ShiftTypeAlias
 *
 * @extends QBMapper<ShiftType>
 */
final class ShiftTypeMapper extends QBMapper {
	public const string TABLE_NAME = 'shnext_types';

	public function __construct(IDBConnection $db) {
		parent::__construct($db, self::TABLE_NAME);
	}

	/**
	 * @param null|string[] $groupIds Adds `WHERE group_id IN($groupIds)`
	 *
	 * @return list<ShiftType>
	 */
	public function findAll(?array $groupIds = null): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName());

		if ($groupIds !== null) {
			$qb->where(
				$qb->expr()->in('group_id', $qb->createNamedParameter($groupIds, IQueryBuilder::PARAM_STR_ARRAY)),
			);
		}

		$qb->orderBy('id', 'DESC');

		return $this->findEntities($qb);
	}

	/**
	 * @throws ShiftTypeNotFoundException if `$shiftType` is an int ID and no
	 *                                    ShiftType with that ID exists
	 */
	public function findById(int|ShiftType $shiftType): ShiftType {
		if ($shiftType instanceof ShiftType) {
			return $shiftType;
		}
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName())
			->where(
				$qb->expr()->eq('id', $qb->createNamedParameter($shiftType, IQueryBuilder::PARAM_INT)),
			);

		try {
			return $this->findEntity($qb);
		} catch (DoesNotExistException $e) {
			throw new ShiftTypeNotFoundException($e->getMessage());
		}
	}

	/**
	 * @param Repetition $repetition
	 * @param Caldav $caldav
	 */
	public function create(
		string $groupId,
		string $name,
		string $description,
		string $color,
		bool $active,
		array $repetition,
		array $caldav,
	): ShiftType {
		$shiftType = new ShiftType();
		$shiftType->setGroupId($groupId);
		$shiftType->setName($name);
		$shiftType->setDescription($description);
		$shiftType->setColor($color);
		$shiftType->setActive($active);
		$shiftType->setRepetition($repetition);
		$shiftType->setCaldav($caldav);
		return $this->insert($shiftType);
	}

	/**
	 * @param null|Repetition $repetition
	 * @param null|Caldav $caldav
	 * @throws ShiftTypeNotFoundException {@see OCA\ShiftsNext\Db\ShiftTypeMapper::findById()}
	 */
	public function updateById(
		int|ShiftType $shiftType,
		?string $groupId = null,
		?string $name = null,
		?string $description = null,
		?string $color = null,
		?bool $active = null,
		?array $repetition = null,
		?array $caldav = null,
	): ShiftType {
		$shiftType = $this->findById($shiftType);
		if ($groupId !== null) {
			$shiftType->setGroupId($groupId);
		}
		if ($name !== null) {
			$shiftType->setName($name);
		}
		if ($description !== null) {
			$shiftType->setDescription($description);
		}
		if ($color !== null) {
			$shiftType->setColor($color);
		}
		if ($active !== null) {
			$shiftType->setActive($active);
		}
		if ($repetition !== null) {
			$shiftType->setRepetition($repetition);
		}
		if ($caldav !== null) {
			$shiftType->setCaldav($caldav);
		}
		return $this->update($shiftType);
	}

	/**
	 * @throws ShiftTypeNotFoundException {@see OCA\ShiftsNext\Db\ShiftTypeMapper::findById()}
	 */
	public function deleteById(int|ShiftType $shiftType): ShiftType {
		$shiftType = $this->findById($shiftType);
		return $this->delete($shiftType);
	}
}
