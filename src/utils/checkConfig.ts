import { getInitialAbsenceCalendar, getInitialCommonCalendar, getInitialGroupShiftAdminRelationsByGroup } from './initialState.ts'
import { logger } from './logger.ts'

/**
 * Checks if any necessary configuration is missing
 *
 * @return An array indicating which configuration settings are missing
 */
export function checkConfig(): string[] {
	const missingConfigs: string[] = []
	if (!getInitialCommonCalendar()) {
		logger.error('No common calendar configured')
		missingConfigs.push('Common calendar')
	}
	if (!getInitialAbsenceCalendar()) {
		logger.error('No absence calendar configured')
		missingConfigs.push('Absence calendar')
	}
	const relations = getInitialGroupShiftAdminRelationsByGroup()
	const hasAdmins = relations.some(({ users }) => users.length)
	if (!hasAdmins) {
		logger.error('No shift admins configured')
		missingConfigs.push('Shift admins')
	}
	return missingConfigs
}
