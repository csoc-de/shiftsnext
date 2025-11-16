import type { Temporal } from 'temporal-polyfill'
import type { ShiftType } from '../models/shiftType.ts'
import type { SearchParams } from '../models/url.ts'
import type { User } from '../models/user.ts'
import type { IsoCalendarDate, IsoWeekDateWithoutDay } from '../utils/date.ts'

export interface ShiftRequestBase {
	user_id: string
	shift_type_id: number
}

export interface ShiftByDayRequest extends ShiftRequestBase {
	start: Temporal.ZonedDateTime
	end: Temporal.ZonedDateTime
}

export interface ShiftByWeekRequest extends ShiftRequestBase {
	start: Temporal.PlainDate
	end: Temporal.PlainDate
}

export type ShiftRequest = ShiftByDayRequest | ShiftByWeekRequest

export interface Shift extends Omit<ShiftRequest, 'shift_type_id' | 'user_id'> {
	id: number
	user: User
	shift_type: ShiftType
}

export type ShiftPatchRequest = Pick<ShiftRequest, 'user_id'>

export interface ShiftFilters extends SearchParams {
	group_ids?: string[]
	user_id?: string
	calendar_date?: IsoCalendarDate
	week_date?: IsoWeekDateWithoutDay
}
