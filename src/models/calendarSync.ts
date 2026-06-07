export interface SynchronizeByGroupsPostPayload {
	group_ids: string[]
}

export interface SynchronizeByShiftsPostPayload {
	shift_ids: number[]
}

export type SynchronizeResponsePayload = string[]

export interface AbsenceBlocker {
	user_id: string
	start: string
	end: string
	all_day: boolean
	title: string
}
