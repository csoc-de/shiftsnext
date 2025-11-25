import type { Group } from './group.ts'
import type { User } from './user.ts'

export interface GroupUserRelationsByGroup {
	group: Group
	users: User[]
}
