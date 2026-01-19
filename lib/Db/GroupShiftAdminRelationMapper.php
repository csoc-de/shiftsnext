<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Db;

use OC\DB\Exceptions\DbalException;
use OCA\ShiftsNext\Exception\GroupShiftAdminRelationNotFoundException;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @extends QBMapper<GroupShiftAdminRelation>
 */
final class GroupShiftAdminRelationMapper extends QBMapper {
	public const string TABLE_NAME = 'shnext_group_sh_admin';

	public function __construct(IDBConnection $db) {
		parent::__construct($db, self::TABLE_NAME);
	}

	/**
	 * Multiple parameters are combined with a logical `AND`
	 *
	 * @param null|string[] $groupIds Adds `WHERE group_id IN($groupIds)`
	 * @param null|string[] $userIds Adds `WHERE user_id IN($userIds)`
	 *
	 * @return list<GroupShiftAdminRelation>
	 */
	public function findAll(
		?array $groupIds = null,
		?array $userIds = null,
	): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName());

		if ($groupIds !== null) {
			$qb->andWhere(
				$qb->expr()->in('group_id', $qb->createNamedParameter($groupIds, IQueryBuilder::PARAM_STR_ARRAY)),
			);
		}
		if ($userIds !== null) {
			$qb->andWhere(
				$qb->expr()->in('user_id', $qb->createNamedParameter($userIds, IQueryBuilder::PARAM_STR_ARRAY)),
			);
		}

		return $this->findEntities($qb);
	}

	/**
	 * @throws GroupShiftAdminRelationNotFoundException if `$groupShiftAdminRelation`
	 *                                                  is an int ID and no GroupShiftAdminRelation with that ID exists
	 */
	public function findById(int|GroupShiftAdminRelation $groupShiftAdminRelation): GroupShiftAdminRelation {
		if ($groupShiftAdminRelation instanceof GroupShiftAdminRelation) {
			return $groupShiftAdminRelation;
		}
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName())
			->where(
				$qb->expr()->eq('id', $qb->createNamedParameter($groupShiftAdminRelation, IQueryBuilder::PARAM_INT)),
			);

		try {
			return $this->findEntity($qb);
		} catch (DoesNotExistException $e) {
			throw new GroupShiftAdminRelationNotFoundException($e->getMessage());
		}
	}

	/**
	 * @throws DbalException The only expected case for this method to throw,
	 *                       is, if the relation already exists. To verify this, check
	 *                       `$e->getPrevious() instanceof Doctrine\DBAL\Exception\UniqueConstraintViolationException`
	 */
	public function create(
		string $groupId,
		string $userId,
	): GroupShiftAdminRelation {
		$groupShiftAdminRelation = new GroupShiftAdminRelation();
		$groupShiftAdminRelation->setGroupId($groupId);
		$groupShiftAdminRelation->setUserId($userId);
		return $this->insert($groupShiftAdminRelation);
	}

	/**
	 * @throws GroupShiftAdminRelationNotFoundException {@see OCA\ShiftsNext\Db\GroupShiftAdminRelationMapper::findById()}
	 */
	public function deleteById(int|GroupShiftAdminRelation $groupShiftAdminRelation): GroupShiftAdminRelation {
		$groupShiftAdminRelation = $this->findById($groupShiftAdminRelation);
		return $this->delete($groupShiftAdminRelation);
	}
}
