import type { Calendar, ExchangeApprovalType } from '../models/config.ts'
import type { Group } from '../models/group.ts'
import type { GroupShiftAdminRelationsByGroup } from '../models/groupShiftAdminRelation.ts'
import type { User } from '../models/user.ts'

import { loadState } from '@nextcloud/initial-state'
import { APP_ID } from './appId.ts'

/**
 * Returns all groups for which the logged-in user had shift admin privileges
 * when the browser page was initially loaded
 */
export function getInitialShiftAdminGroups(): Group[] {
	return structuredClone(loadState<Group[]>(APP_ID, 'shift_admin_groups'))
}

/**
 * Returns `true` if the logged-in user had shift admin privileges for at least
 * one group when the browser page was initially loaded
 */
export function getInitialIsShiftAdmin(): boolean {
	return structuredClone(loadState<Group[]>(
		APP_ID,
		'shift_admin_groups',
	).length > 0)
}

/**
 * Returns all approval types that existed when the browser page was initially
 * loaded
 */
export function getInitialApprovalTypes(): ExchangeApprovalType[] {
	return structuredClone(loadState<ExchangeApprovalType[]>(
		APP_ID,
		'exchange_approval_types',
	))
}

/**
 * Returns the approval type that was set when the browser page was initially
 * loaded
 */
export function getInitialApprovalType(): ExchangeApprovalType {
	return structuredClone(loadState<ExchangeApprovalType>(
		APP_ID,
		'exchange_approval_type',
	))
}

/**
 * Returns all group to shift admin relations grouped by group that existed
 * when the browser page was initially loaded
 */
export function getInitialGroupShiftAdminRelationsByGroup(): GroupShiftAdminRelationsByGroup[] {
	return structuredClone(loadState<GroupShiftAdminRelationsByGroup[]>(
		APP_ID,
		'group_shift_admin_relations_by_group',
	))
}

/**
 * Returns all groups that existed when the browser page was initially loaded
 */
export function getInitialGroups(): Group[] {
	return structuredClone(loadState<Group[]>(APP_ID, 'groups'))
}

/**
 * Returns the user selected groups used inside the ShiftsView when the browser
 * page was initially loaded
 */
export function getInitialDefaultGroups(): Group[] {
	return structuredClone(loadState<Group[]>(APP_ID, 'default_groups'))
}

/**
 * Returns all users that existed when the browser page was initially loaded
 */
export function getInitialUsers(): User[] {
	return structuredClone(loadState<User[]>(APP_ID, 'users'))
}

/**
 * Returns all calendars that existed when the browser page was initially loaded
 */
export function getInitialCalendars(): Calendar[] {
	return structuredClone(loadState<Calendar[]>(APP_ID, 'calendars'))
}

/**
 * Returns the common calendar that was set when the browser page was initially
 * loaded
 */
export function getInitialCommonCalendar(): Calendar | null {
	return structuredClone(loadState<Calendar | null>(
		APP_ID,
		'common_calendar',
		null,
	))
}

/**
 * Returns the absence calendar that was set when the browser page was initially
 * loaded
 */
export function getInitialAbsenceCalendar(): Calendar | null {
	return structuredClone(loadState<Calendar | null>(
		APP_ID,
		'absence_calendar',
		null,
	))
}

/**
 * Returns the "sync to personal calendar" setting that was set when the browser
 * page was initially loaded
 */
export function getInitialSyncToPersonalCalendar(): boolean {
	return structuredClone(loadState<boolean>(
		APP_ID,
		'sync_to_personal_calendar',
	))
}

/**
 * Returns the "ignore absence for by week shifts" setting that was set when the
 * browser page was initially loaded
 */
export function getInitialIgnoreAbsenceForByWeekShifts(): boolean {
	return structuredClone(loadState<boolean>(
		APP_ID,
		'ignore_absence_for_by_week_shifts',
	))
}
