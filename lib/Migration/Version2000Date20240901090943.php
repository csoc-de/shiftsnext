<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Migration;

use Closure;
use OCA\ShiftsNext\Db\CalendarChangeMapper;
use OCA\ShiftsNext\Db\GroupShiftAdminRelationMapper;
use OCA\ShiftsNext\Db\ShiftExchangeApprovalMapper;
use OCA\ShiftsNext\Db\ShiftExchangeMapper;
use OCA\ShiftsNext\Db\ShiftMapper;
use OCA\ShiftsNext\Db\ShiftTypeMapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version2000Date20240901090943 extends SimpleMigrationStep {
	#[\Override]
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
		$schema = $schemaClosure();

		if (!$schema->hasTable(ShiftMapper::TABLE_NAME)) {
			$table = $schema->createTable(ShiftMapper::TABLE_NAME);
			$table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
			$table->addColumn('user_id', Types::STRING, ['length' => 64]);
			$table->addColumn('shift_type_id', Types::BIGINT);
			$table->addColumn('start', Types::STRING, ['length' => 128]);
			$table->addColumn('end', Types::STRING, ['length' => 128]);
			$table->setPrimaryKey(['id']);
		}

		if (!$schema->hasTable(ShiftTypeMapper::TABLE_NAME)) {
			$table = $schema->createTable(ShiftTypeMapper::TABLE_NAME);
			$table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
			$table->addColumn('group_id', Types::STRING, ['length' => 64]);
			$table->addColumn('name', Types::STRING, ['length' => 64]);
			$table->addColumn('description', Types::TEXT);
			$table->addColumn('color', Types::STRING, ['length' => 64]);
			$table->addColumn('active', Types::BOOLEAN, ['default' => false, 'notnull' => false]);
			$table->addColumn('repetition', Types::JSON);
			$table->addColumn('caldav', Types::JSON);
			$table->setPrimaryKey(['id']);
		}

		if (!$schema->hasTable(ShiftExchangeMapper::TABLE_NAME)) {
			$table = $schema->createTable(ShiftExchangeMapper::TABLE_NAME);
			$table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
			$table->addColumn('shift_a_id', Types::BIGINT);
			$table->addColumn('shift_b_id', Types::BIGINT, ['notnull' => false]);
			$table->addColumn('transfer_to_user_id', Types::STRING, ['length' => 64, 'notnull' => false]);
			$table->addColumn('comment', Types::TEXT);
			$table->addColumn('done', Types::BOOLEAN, ['default' => false, 'notnull' => false]);
			$table->addColumn('approved', Types::BOOLEAN, ['default' => null, 'notnull' => false]);
			$table->addColumn('user_a_approval_id', Types::BIGINT);
			$table->addColumn('user_b_approval_id', Types::BIGINT);
			$table->addColumn('admin_approval_id', Types::BIGINT);
			$table->setPrimaryKey(['id']);
		}

		if (!$schema->hasTable(ShiftExchangeApprovalMapper::TABLE_NAME)) {
			$table = $schema->createTable(ShiftExchangeApprovalMapper::TABLE_NAME);
			$table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
			$table->addColumn('user_id', Types::STRING, ['length' => 64, 'notnull' => false]);
			$table->addColumn('approved', Types::BOOLEAN, ['default' => null, 'notnull' => false]);
			$table->setPrimaryKey(['id']);
		}

		if (!$schema->hasTable(GroupShiftAdminRelationMapper::TABLE_NAME)) {
			$table = $schema->createTable(GroupShiftAdminRelationMapper::TABLE_NAME);
			$table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
			$table->addColumn('group_id', Types::STRING, ['length' => 64]);
			$table->addColumn('user_id', Types::STRING, ['length' => 64]);
			$table->setPrimaryKey(['id']);
			$table->addUniqueIndex(['group_id', 'user_id'], 'uniq_sgsa_group_id_user_id');
		}

		if (!$schema->hasTable(CalendarChangeMapper::TABLE_NAME)) {
			$table = $schema->createTable(CalendarChangeMapper::TABLE_NAME);
			$table->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
			$table->addColumn('group_id', Types::STRING, ['length' => 64]);
			$table->addColumn('user_id', Types::STRING, ['length' => 64]);
			$table->addColumn('shift_id', Types::BIGINT);
			$table->setPrimaryKey(['id']);
		}

		$options = ['onDelete' => 'CASCADE', 'onUpdate' => 'CASCADE'];

		$shiftsTable = $schema->getTable(ShiftMapper::TABLE_NAME);
		$shiftTypesTable = $schema->getTable(ShiftTypeMapper::TABLE_NAME);
		$shiftExchangesTable = $schema->getTable(ShiftExchangeMapper::TABLE_NAME);
		$shiftExchangeApprovalsTable = $schema->getTable(ShiftExchangeApprovalMapper::TABLE_NAME);

		$shiftsTable->addForeignKeyConstraint($shiftTypesTable, ['shift_type_id'], ['id'], $options);

		$shiftExchangesTable->addForeignKeyConstraint($shiftsTable, ['shift_a_id'], ['id'], $options);
		$shiftExchangesTable->addForeignKeyConstraint($shiftsTable, ['shift_b_id'], ['id'], $options);
		$shiftExchangesTable->addForeignKeyConstraint($shiftExchangeApprovalsTable, ['user_a_approval_id'], ['id'], $options);
		$shiftExchangesTable->addForeignKeyConstraint($shiftExchangeApprovalsTable, ['user_b_approval_id'], ['id'], $options);
		$shiftExchangesTable->addForeignKeyConstraint($shiftExchangeApprovalsTable, ['admin_approval_id'], ['id'], $options);

		return $schema;
	}
}
