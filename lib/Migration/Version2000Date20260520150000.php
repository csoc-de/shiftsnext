<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Migration;

use Closure;
use OCA\ShiftsNext\Db\ShiftTypeMapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;
use Override;

final class Version2000Date20260520150000 extends SimpleMigrationStep {
	#[Override]
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
		$schema = $schemaClosure();

		$table = $schema->getTable(ShiftTypeMapper::TABLE_NAME);
		if (!$table->hasColumn('sync_to_calendar')) {
			$table->addColumn('sync_to_calendar', Types::BOOLEAN, ['default' => true, 'notnull' => false]);
		}
		if (!$table->hasColumn('calendar_id')) {
			$table->addColumn('calendar_id', Types::BIGINT, ['default' => null, 'notnull' => false]);
		}
		return $schema;
	}
}
