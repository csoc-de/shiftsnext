import { loadState } from '@nextcloud/initial-state'
import { APP_ID } from './appId'
import type { GroupShiftAdminRelationsByGroup } from './models/groupShiftAdminRelation'

/**
 * Checks if any necessary configuration is missing
 */
export function checkConfig(): boolean {
	try {
		loadState(APP_ID, 'common_calendar')
		loadState(APP_ID, 'absence_calendar')
		const relations = loadState<GroupShiftAdminRelationsByGroup[]>(
			APP_ID,
			'group_shift_admin_relations_by_group',
			[],
		)
		const hasAdmins = relations.some(({ users }) => users.length)
		if (!hasAdmins) {
			throw new Error('No group shift admins configured')
		}
		return true
	} catch (error: unknown) {
		return false
	}
}
