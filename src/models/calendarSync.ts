export interface SynchronizeByGroupsRequest {
	group_ids: string[]
}

export interface SynchronizeByShiftsRequest {
	shift_ids: number[]
}

export type SynchronizeResponse = string[]
