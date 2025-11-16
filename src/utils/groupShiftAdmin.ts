import type { Group } from '../models/group.ts'

import { getInitialShiftAdminGroups } from './initialState.ts'

let shiftAdminGroups: Group[]

/**
 * Checks if the logged-in user is a group shift admin of `groupId`
 *
 * @param groupId The group ID to check
 */
export function isShiftAdmin(groupId: string): boolean {
	shiftAdminGroups ??= getInitialShiftAdminGroups()
	return shiftAdminGroups.some(({ id }) => id === groupId)
}
