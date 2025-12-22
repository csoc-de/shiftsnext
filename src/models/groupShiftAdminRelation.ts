import type { Group } from '../models/group.ts'
import type { User } from '../models/user.ts'

export interface GroupShiftAdminRelationsByGroupPutPayload {
	group_id: string
	user_ids: string[]
}

export interface GroupShiftAdminRelationsByGroup {
	group: Group
	users: User[]
}
