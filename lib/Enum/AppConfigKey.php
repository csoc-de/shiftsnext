<?php

declare(strict_types=1);

namespace OCA\ShiftsNext\Enum;

enum AppConfigKey: string {
	case CommonCalendarId = 'common_calendar_id';
	case AbsenceCalendarId = 'absence_calendar_id';
	case SyncToPersonalCalendar = 'sync_to_personal_calendar';
	case IgnoreAbsenceForByWeekShifts = 'ignore_absence_for_by_week_shifts';
	case ExchangeApprovalType = 'exchange_approval_type';
}
