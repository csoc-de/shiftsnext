import type { Group } from '../models/group'
import type { User } from '../models/user'

export interface GroupShiftAdminRelationsByGroupRequest {
	group_id: string
	user_ids: string[]
}

export interface GroupShiftAdminRelationsByGroup {
	group: Group
	users: User[]
}
