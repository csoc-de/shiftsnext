export interface SynchronizeByGroupsPostPayload {
	group_ids: string[]
}

export interface SynchronizeByShiftsPostPayload {
	shift_ids: number[]
}

export type SynchronizeResponsePayload = string[]
