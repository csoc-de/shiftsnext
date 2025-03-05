<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Db;

use OCA\ShiftsNext\Exception\ShiftExchangeApprovalNotFoundException;
use OCA\ShiftsNext\Psalm\MiscAlias;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

use function array_all;
use function func_get_args;

/**
 * @extends QBMapper<ShiftExchangeApproval>
 *
 * @psalm-import-type NullColumnParam from MiscAlias
 */
class ShiftExchangeApprovalMapper extends QBMapper {
	public const string TABLE_NAME = 'shnext_ex_approvals';

	public function __construct(IDBConnection $db) {
		parent::__construct($db, self::TABLE_NAME);
	}

	/**
	 * @param null|NullColumnParam|string $userId Adds `WHERE user_id = $userId`
	 *
	 * @return ShiftExchangeApproval[]
	 */
	public function findAll(
		?string $userId = null,
	): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName());

		if ($userId !== null) {
			switch ($userId) {
				case 'IS_NULL':
					$qb->andWhere($qb->expr()->isNull('user_id'));
					break;
				case 'IS_NOT_NULL':
					$qb->andWhere($qb->expr()->isNotNull('user_id'));
					break;
				default:
					$qb->andWhere(
						$qb->expr()->eq('user_id', $qb->createNamedParameter($userId, IQueryBuilder::PARAM_STR)),
					);
					break;
			}
		}

		return $this->findEntities($qb);
	}

	/**
	 * @throws ShiftExchangeApprovalNotFoundException if `$shiftExchangeApproval`
	 *                                                is an int ID and no ShiftExchangeApproval with that ID exists
	 */
	public function findById(int|ShiftExchangeApproval $shiftExchangeApproval): ShiftExchangeApproval {
		if ($shiftExchangeApproval instanceof ShiftExchangeApproval) {
			return $shiftExchangeApproval;
		}
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName())
			->where(
				$qb->expr()->eq('id', $qb->createNamedParameter($shiftExchangeApproval, IQueryBuilder::PARAM_INT)),
			);

		try {
			return $this->findEntity($qb);
		} catch (DoesNotExistException $e) {
			throw new ShiftExchangeApprovalNotFoundException($e->getMessage());
		}
	}

	public function create(
		?string $userId = null,
		?bool $approved = null,
	): ShiftExchangeApproval {
		$shiftExchangeApproval = new ShiftExchangeApproval();
		$shiftExchangeApproval->setUserId($userId);
		$shiftExchangeApproval->setApproved($approved);

		$provider = $this->db->getDatabaseProvider();
		if (
			$provider === IDBConnection::PLATFORM_POSTGRES &&
			array_all(func_get_args(), fn (mixed $arg) => $arg === null)
		) {
			$table = $this->db->getQueryBuilder()->getTableName($this->getTableName());
			$result = $this->db->executeQuery("INSERT INTO $table DEFAULT VALUES RETURNING id");
			/** @var array{id: int} */
			$row = $result->fetch();
			$shiftExchangeApproval->setId($row['id']);
			return $shiftExchangeApproval;
		} else {
			return $this->insert($shiftExchangeApproval);
		}

	}

	/**
	 * @throws ShiftExchangeApprovalNotFoundException {@see OCA\ShiftsNext\Db\ShiftExchangeApprovalMapper::findById()}
	 */
	public function updateById(
		int|ShiftExchangeApproval $shiftExchangeApproval,
		?string $userId = null,
		?bool $approved = null,
	): ShiftExchangeApproval {
		$shiftExchangeApproval = $this->findById($shiftExchangeApproval);
		if ($userId !== null) {
			$shiftExchangeApproval->setUserId($userId);
		}
		$shiftExchangeApproval->setApproved($approved);
		return $this->update($shiftExchangeApproval);
	}

	/**
	 * @throws ShiftExchangeApprovalNotFoundException {@see OCA\ShiftsNext\Db\ShiftExchangeApprovalMapper::findById()}
	 */
	public function deleteById(int|ShiftExchangeApproval $shiftExchangeApproval): ShiftExchangeApproval {
		$shiftExchangeApproval = $this->findById($shiftExchangeApproval);
		return $this->delete($shiftExchangeApproval);
	}
}
