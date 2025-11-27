import type { GroupUserRelationsByGroup } from '../models/groupUserRelation.ts'

import { getInitialGroupUserRelationsByGroup } from './initialState.ts'

let groupUserRelationsByGroup: GroupUserRelationsByGroup[]

/**
 * Checks if the user identified by `userId` is a member of the group identified
 * by `groupId`
 *
 * @param groupId The group ID to check against
 * @param userId The user ID to check
 */
export function isMember(groupId: string, userId: string): boolean {
	groupUserRelationsByGroup ??= getInitialGroupUserRelationsByGroup()
	return groupUserRelationsByGroup
		.some(({ group, users }) => group.id === groupId && users.some((user) => user.id === userId))
}
