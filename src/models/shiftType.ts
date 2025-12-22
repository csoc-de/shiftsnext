import type { Temporal } from 'temporal-polyfill'
import type { Group } from '../models/group.ts'
import type { SearchParams } from '../models/url.ts'
import type { IsoWeekDateWithoutDay } from '../utils/date.ts'

import { getDayNames, getFirstDay, t } from '@nextcloud/l10n'
import { APP_ID } from '../utils/appId.ts'
import { rotate } from '../utils/array.ts'

export const REPETITION_FREQUENCIES = ['weekly'] as const

export type RepetitionFrequency = (typeof REPETITION_FREQUENCIES)[number]

export interface RepetitionBase {
	frequency: RepetitionFrequency
	interval: number
}

export const REPETITION_WEEKLY_TYPES = ['by_day', 'by_week'] as const

export type RepetitionWeeklyType = (typeof REPETITION_WEEKLY_TYPES)[number]

export type WeeklyTypeTranslations = Record<RepetitionWeeklyType, string>

export const weeklyTypeTranslations: WeeklyTypeTranslations = {
	by_day: t(APP_ID, 'By day'),
	by_week: t(APP_ID, 'By week'),
}

/**
 * It is important that the order of the elements in this array is the same as
 * the order of the return values of the `getDayNames*` functions from the
 * `@nextcloud/l10n` module, i.e. the first element must be Sunday
 */
export const SHORT_DAYS = ['SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA'] as const

export type ShortDay = (typeof SHORT_DAYS)[number]

export type ShortDayToAmountMap = Record<ShortDay, number>

const firstDay = getFirstDay()
const reorderedlocalDays = rotate(getDayNames(), firstDay, 0)
export const reorderedShortDays = rotate(SHORT_DAYS, firstDay, 0)
// @ts-expect-error Object.fromEntries doesn't infer the proper return type
export const shortDayToLocalDayMap: Record<ShortDay, string>
	= Object.fromEntries(reorderedShortDays.map((shortDay, index) => [
		shortDay,
		reorderedlocalDays[index] ?? 'undefined',
	] as const))

export interface RepetitionWeeklyByDayConfig {
	/**
	 * Together with the properties `frequency` and `interval`, all occurences
	 * of shifts, that can be created from this shift type, can be calculated,
	 * by using the value of this property as the starting point, which marks
	 * the earliest possible occurence.
	 *
	 * The time component of the value determines the beginning of a shift
	 * created from this shift type.
	 *
	 * This property will always be in the time zone that was set when creating
	 * the shift type. It MUST NOT be updated when editing a shift type.
	 */
	reference: Temporal.ZonedDateTime
	short_day_to_amount_map: ShortDayToAmountMap
	duration: Temporal.Duration
}

export interface RepetitionWeeklyByWeekConfig {
	/**
	 * Together with the properties `frequency` and `interval`, all occurences
	 * of shifts, that can be created from this shift type, can be calculated,
	 * by using the value of this property as the starting point, which marks
	 * the earliest possible occurence.
	 */
	reference: IsoWeekDateWithoutDay
	amount: number
}

export type RepetitionWeeklyConfig
	= RepetitionWeeklyByDayConfig | RepetitionWeeklyByWeekConfig

export interface RepetitionWeeklyBase extends RepetitionBase {
	frequency: 'weekly'
	weekly_type: RepetitionWeeklyType
	config: RepetitionWeeklyConfig
}

export interface RepetitionWeeklyByDay extends RepetitionWeeklyBase {
	weekly_type: 'by_day'
	config: RepetitionWeeklyByDayConfig
}

export interface RepetitionWeeklyByWeek extends RepetitionWeeklyBase {
	weekly_type: 'by_week'
	config: RepetitionWeeklyByWeekConfig
}

export type RepetitionWeekly = RepetitionWeeklyByDay | RepetitionWeeklyByWeek

export type Repetition = RepetitionWeekly

export interface Caldav {
	categories: string
}

export interface ShiftType {
	id: number
	group: Group
	name: string
	description: string
	color: string
	active: boolean
	repetition: Repetition
	caldav: Caldav
}

export interface ShiftTypePostPayload extends Omit<ShiftType, 'id' | 'group'> {
	group_id: string
}

export type ShiftTypePutPayload = Omit<ShiftTypePostPayload, 'group_id' | 'repetition'>

export type ShiftTypePayloadType = 'post' | 'put'

export type ShiftTypePayload<T extends ShiftTypePayloadType>
	= T extends 'post' ? ShiftTypePostPayload : ShiftTypePutPayload

export interface ShiftTypeFilters extends SearchParams {
	group_ids?: string[]
	restricted?: boolean
}

export type ShortDayToIsoDayNumberMap = Record<ShortDay, number>

/** DON'T CHANGE THE ORDER OF THE PROPERTIES IN THIS MAP */
export const shortDayToIsoDayNumberMap = {
	MO: 1,
	TU: 2,
	WE: 3,
	TH: 4,
	FR: 5,
	SA: 6,
	SU: 7,
} as const satisfies ShortDayToIsoDayNumberMap
