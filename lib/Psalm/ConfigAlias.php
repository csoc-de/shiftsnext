<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Psalm;

/**
 * @psalm-type AppConfig = array{
 *     common_calendar_id: int,
 *     absence_calendar_id: int,
 *     sync_to_personal_calendar: bool,
 *     ignore_absence_for_by_week_shifts: bool,
 *     exchange_approval_type: 'users'|'admin'|'all',
 * }
 *
 * @psalm-type UserConfig = array{
 *     defaultGroupIds: string[],
 * }
 */
class ConfigAlias {
}
