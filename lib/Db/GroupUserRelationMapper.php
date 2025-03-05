<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Db;

use OCA\ShiftsNext\Psalm\GroupUserRelationAlias;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @psalm-suppress MissingTemplateParam
 * @psalm-suppress UnusedClass Currently unused
 *
 * @psalm-import-type Row from GroupUserRelationAlias
 * @psalm-import-type Relation from GroupUserRelationAlias
 */
class GroupUserRelationMapper extends QBMapper {
	public const string TABLE_NAME = 'group_user';

	public function __construct(IDBConnection $db) {
		parent::__construct($db, self::TABLE_NAME);
	}

	/**
	 * Multiple parameters are combined with a logical `AND`
	 *
	 * @param null|string[] $groupIds Adds `WHERE group_id IN($groupIds)`
	 * @param null|string[] $userIds Adds `WHERE user_id IN($userIds)`
	 *
	 * @return Relation[]
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

		$result = $qb->executeQuery();

		try {
			/** @var Relation[] */
			$entities = [];
			while (/** @var Row */ $row = $result->fetch()) {
				$entities[] = [
					'group_id' => $row['gid'],
					'user_id' => $row['uid'],
				];
			}
			return $entities;
		} finally {
			$result->closeCursor();
		}
	}
}
