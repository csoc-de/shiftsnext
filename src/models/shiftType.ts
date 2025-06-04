import type { Temporal } from 'temporal-polyfill'
import type { InjectionKey } from 'vue'
import type { IsoWeekDateWithoutDay } from '../date.ts'
import type { Group } from '../models/group.ts'
import type { SearchParams } from '../models/url.ts'

import { t } from '@nextcloud/l10n'
import { APP_ID } from '../appId.ts'

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

export interface ShiftTypeRequest {
	group_id: string
	name: string
	description: string
	color: string
	active: boolean
	repetition: Repetition
	caldav: Caldav
}

export interface ShiftType extends Omit<ShiftTypeRequest, 'group_id'> {
	id: number
	group: Group
}

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

export type CreateShiftType = (
	payload: ShiftTypeRequest,
) => Promise<void>

export type UpdateShiftType = (
	id: number,
	payload: ShiftTypeRequest,
) => Promise<void>

export type RemoveShiftType = (id: number) => Promise<void>

export const createInjectionKey
	= Symbol('createInjectionKey') as InjectionKey<CreateShiftType>

export const updateInjectionKey
	= Symbol('updateInjectionKey') as InjectionKey<UpdateShiftType>

export const removeInjectionKey
	= Symbol('removeInjectionKey') as InjectionKey<RemoveShiftType>
