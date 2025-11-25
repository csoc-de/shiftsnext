<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Db;

use OCA\ShiftsNext\Exception\GroupUserRelationNotFoundException;
use OCA\ShiftsNext\Psalm\GroupUserRelationAlias;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @psalm-import-type GroupUserRow from GroupUserRelationAlias
 *
 * @extends QBMapper<GroupUserRelation>
 */
final class GroupUserRelationMapper extends QBMapper {
	public const string TABLE_NAME = 'group_user';

	public function __construct(IDBConnection $db) {
		parent::__construct($db, self::TABLE_NAME);
	}

	/**
	 * Multiple parameters are combined with a logical `AND`
	 *
	 * @param null|string[] $groupIds Adds `WHERE gid IN($groupIds)`
	 * @param null|string[] $userIds Adds `WHERE uid IN($userIds)`
	 *
	 * @return GroupUserRelation[]
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
				$qb->expr()->in('gid', $qb->createNamedParameter($groupIds, IQueryBuilder::PARAM_STR_ARRAY)),
			);
		}
		if ($userIds !== null) {
			$qb->andWhere(
				$qb->expr()->in('uid', $qb->createNamedParameter($userIds, IQueryBuilder::PARAM_STR_ARRAY)),
			);
		}

		return $this->findEntities($qb);
	}

	/**
	 * @param GroupUserRow|GroupUserRelation $groupUserRelation
	 *
	 * @throws GroupUserRelationNotFoundException if `$groupUserRelation`
	 *                                            is an array and no GroupUserRelation with the passed IDs exists
	 */
	public function findById(array|GroupUserRelation $groupUserRelation): GroupUserRelation {
		if ($groupUserRelation instanceof GroupUserRelation) {
			return $groupUserRelation;
		}
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName())
			->andWhere(
				$qb->expr()->eq('gid', $qb->createNamedParameter($groupUserRelation['gid'], IQueryBuilder::PARAM_STR)),
			)
			->andWhere(
				$qb->expr()->eq('uid', $qb->createNamedParameter($groupUserRelation['uid'], IQueryBuilder::PARAM_STR)),
			);

		try {
			return $this->findEntity($qb);
		} catch (DoesNotExistException $e) {
			throw new GroupUserRelationNotFoundException($e->getMessage());
		}
	}
}
