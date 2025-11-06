import { getInitialAbsenceCalendar, getInitialCommonCalendar, getInitialGroupShiftAdminRelationsByGroup } from './initialState.ts'

/**
 * Checks if any necessary configuration is missing
 */
export function checkConfig(): boolean {
	try {
		if (!getInitialCommonCalendar()) {
			throw new Error('No common calendar configured')
		}
		if (!getInitialAbsenceCalendar()) {
			throw new Error('No absence calendar configured')
		}
		const relations = getInitialGroupShiftAdminRelationsByGroup()
		const hasAdmins = relations.some(({ users }) => users.length)
		if (!hasAdmins) {
			throw new Error('No group shift admins configured')
		}
		return true
	} catch {
		return false
	}
}
