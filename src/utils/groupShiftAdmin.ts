import type { Group } from '../models/group.ts'
import type { GroupShiftAdminRelationsByGroup } from '../models/groupShiftAdminRelation.ts'
import type { User } from '../models/user.ts'

import { getInitialGroupShiftAdminRelationsByGroup, getInitialShiftAdminGroups } from './initialState.ts'
import { compare } from './sort.ts'

let shiftAdminGroups: Group[]
let groupedRelations: GroupShiftAdminRelationsByGroup[]

/**
 * Checks if the logged-in user is a group shift admin of `groupId`
 *
 * @param groupId The group ID to check
 */
export function isShiftAdmin(groupId: string): boolean {
	shiftAdminGroups ??= getInitialShiftAdminGroups()
	return shiftAdminGroups.some(({ id }) => id === groupId)
}

/**
 * Checks if the logged-in user is a group shift admin of all `groupIds`
 *
 * @param groupIds The group IDs to check
 */
export function isShiftAdminAll(groupIds: string[]): boolean {
	shiftAdminGroups ??= getInitialShiftAdminGroups()
	return groupIds
		.every((groupId) => shiftAdminGroups.some(({ id }) => id === groupId))
}

/**
 * Returns the shift admins for `groupIds` sorted by their display name
 *
 * @param groupIds The group IDs to get the shift admins for
 */
export function getShiftAdmins(groupIds: string[]): User[] {
	groupedRelations ??= getInitialGroupShiftAdminRelationsByGroup()
	const shiftAdmins = new Map<string, User>()
	for (const groupId of groupIds) {
		const _shiftAdmins = groupedRelations
			.find(({ group }) => group.id === groupId)?.users ?? []
		for (const _shiftAdmin of _shiftAdmins) {
			shiftAdmins.set(_shiftAdmin.id, _shiftAdmin)
		}
	}
	return Array
		.from(shiftAdmins.values())
		.sort((a, b) => compare(a.display_name, b.display_name))
}
