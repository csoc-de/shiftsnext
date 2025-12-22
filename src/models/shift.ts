import type { Temporal } from 'temporal-polyfill'
import type { ShiftType } from '../models/shiftType.ts'
import type { SearchParams } from '../models/url.ts'
import type { User } from '../models/user.ts'
import type { IsoCalendarDate, IsoWeekDateWithoutDay } from '../utils/date.ts'

export interface ShiftPostPayloadBase {
	user_id: string
	shift_type_id: number
}

export interface ShiftByDayPostPayload extends ShiftPostPayloadBase {
	start: Temporal.ZonedDateTime
	end: Temporal.ZonedDateTime
}

export interface ShiftByWeekPostPayload extends ShiftPostPayloadBase {
	start: Temporal.PlainDate
	end: Temporal.PlainDate
}

export type ShiftPostPayload = ShiftByDayPostPayload | ShiftByWeekPostPayload

export type ShiftPatchPayload = Pick<ShiftPostPayload, 'user_id'>

export interface Shift extends Omit<ShiftPostPayload, 'shift_type_id' | 'user_id'> {
	id: number
	user: User
	shift_type: ShiftType
}

export interface ShiftFilters extends SearchParams {
	group_ids?: string[]
	user_id?: string
	calendar_date?: IsoCalendarDate
	week_date?: IsoWeekDateWithoutDay
}
