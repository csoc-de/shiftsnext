<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Db;

use OCA\ShiftsNext\Exception\ShiftExchangeNotFoundException;
use OCA\ShiftsNext\Psalm\MiscAlias;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @extends QBMapper<ShiftExchange>
 *
 * @psalm-import-type NullColumnParam from MiscAlias
 */
final class ShiftExchangeMapper extends QBMapper {
	public const string TABLE_NAME = 'shnext_exchanges';

	public function __construct(IDBConnection $db) {
		parent::__construct($db, self::TABLE_NAME);
	}

	/**
	 * @param null|int $shiftAId Adds `WHERE shift_a_id = $shiftAId`
	 * @param null|NullColumnParam|int $shiftBId Adds `WHERE shift_b_id = $shiftBId`
	 * @param null|NullColumnParam|string $transferToUserId Adds `WHERE transfer_to_user_id = $transferToUserId`
	 * @param null|bool $done Adds `WHERE done = $done`
	 *
	 * @return ShiftExchange[]
	 */
	public function findAll(
		?int $shiftAId = null,
		null|string|int $shiftBId = null,
		?string $transferToUserId = null,
		?bool $done = null,
	): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName());

		if ($shiftAId !== null) {
			$qb->andWhere(
				$qb->expr()->eq('shift_a_id', $qb->createNamedParameter($shiftAId, IQueryBuilder::PARAM_INT)),
			);
		}

		if ($shiftBId !== null) {
			switch ($shiftBId) {
				case 'IS_NULL':
					$qb->andWhere($qb->expr()->isNull('shift_b_id'));
					break;
				case 'IS_NOT_NULL':
					$qb->andWhere($qb->expr()->isNotNull('shift_b_id'));
					break;
				default:
					$qb->andWhere(
						$qb->expr()->eq('shift_b_id', $qb->createNamedParameter($shiftBId, IQueryBuilder::PARAM_INT)),
					);
					break;
			}

		}

		if ($transferToUserId !== null) {
			switch ($transferToUserId) {
				case 'IS_NULL':
					$qb->andWhere($qb->expr()->isNull('transfer_to_user_id'));
					break;
				case 'IS_NOT_NULL':
					$qb->andWhere($qb->expr()->isNotNull('transfer_to_user_id'));
					break;
				default:
					$qb->andWhere(
						$qb->expr()->eq('transfer_to_user_id', $qb->createNamedParameter($transferToUserId, IQueryBuilder::PARAM_STR)),
					);
					break;
			}
		}

		if ($done !== null) {
			$qb->andWhere(
				$qb->expr()->eq('done', $qb->createNamedParameter($done, IQueryBuilder::PARAM_BOOL)),
			);
		}

		$qb->orderBy('id', 'DESC');

		return $this->findEntities($qb);
	}

	/**
	 * @throws ShiftExchangeNotFoundException if `$shiftExchange` is an int ID
	 *                                        and no ShiftExchange with that ID exists
	 */
	public function findById(int|ShiftExchange $shiftExchange): ShiftExchange {
		if ($shiftExchange instanceof ShiftExchange) {
			return $shiftExchange;
		}
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName())
			->where(
				$qb->expr()->eq('id', $qb->createNamedParameter($shiftExchange, IQueryBuilder::PARAM_INT)),
			);

		try {
			return $this->findEntity($qb);
		} catch (DoesNotExistException $e) {
			throw new ShiftExchangeNotFoundException($e->getMessage());
		}
	}

	public function create(
		int $shiftAId,
		?int $shiftBId,
		?string $transferToUserId,
		string $comment,
		bool $done,
		?bool $approved,
		int $userAApprovalId,
		int $userBApprovalId,
		int $adminApprovalId,
	): ShiftExchange {
		$shiftExchange = new ShiftExchange();
		$shiftExchange->setShiftAId($shiftAId);
		$shiftExchange->setShiftBId($shiftBId);
		$shiftExchange->setTransferToUserId($transferToUserId);
		$shiftExchange->setComment($comment);
		$shiftExchange->setDone($done);
		$shiftExchange->setApproved($approved);
		$shiftExchange->setUserAApprovalId($userAApprovalId);
		$shiftExchange->setUserBApprovalId($userBApprovalId);
		$shiftExchange->setAdminApprovalId($adminApprovalId);
		return $this->insert($shiftExchange);
	}

	/**
	 * @throws ShiftExchangeNotFoundException {@see OCA\ShiftsNext\Db\ShiftExchangeMapper::findById()}
	 */
	public function updateById(
		int|ShiftExchange $shiftExchange,
		?string $comment = null,
		?bool $done = null,
		?bool $approved = null,
	): ShiftExchange {
		$shiftExchange = $this->findById($shiftExchange);
		if ($comment !== null) {
			$shiftExchange->setComment($comment);
		}
		if ($done !== null) {
			$shiftExchange->setDone($done);
		}
		$shiftExchange->setApproved($approved);
		return $this->update($shiftExchange);
	}

	/**
	 * @throws ShiftExchangeNotFoundException {@see OCA\ShiftsNext\Db\ShiftExchangeMapper::findById()}
	 *
	 * @psalm-suppress PossiblyUnusedReturnValue
	 */
	public function deleteById(int|ShiftExchange $shiftExchange): ShiftExchange {
		$shiftExchange = $this->findById($shiftExchange);
		return $this->delete($shiftExchange);
	}
}
