export interface SynchronizeByGroupsRequest {
	group_ids: string[]
}

export interface SynchronizeByShiftRequest {
	shift_id: number
}

export type SynchronizeResponse = string[]
