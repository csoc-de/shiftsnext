import type { Temporal } from 'temporal-polyfill'
import type { InjectionKey, Ref } from 'vue'
import type { IsoWeekDateWithoutDay } from '../date.ts'
import type { Shift } from './shift.ts'
import type { RepetitionWeeklyType, ShiftType } from './shiftType.ts'
import type { User } from './user.ts'

export interface ShiftTypeWrapperBase {
	shiftType: ShiftType
	weeklyType: RepetitionWeeklyType
	shiftStart: Temporal.ZonedDateTime | Temporal.PlainDate
	shiftEnd: Temporal.ZonedDateTime | Temporal.PlainDate
	/** Specifies how many assignments of `shiftType` are left on a specific day or week */
	amount: number
}

export interface ShiftTypeWeeklyByDayWrapper extends ShiftTypeWrapperBase {
	weeklyType: 'by_day'
	/** Start of the shift, already adjusted for the runtimes time zone */
	shiftStart: Temporal.ZonedDateTime
	/** End of the shift, already adjusted for the runtimes time zone */
	shiftEnd: Temporal.ZonedDateTime
}

export interface ShiftTypeWeeklyByWeekWrapper extends ShiftTypeWrapperBase {
	weeklyType: 'by_week'
	/** Start of the shift */
	shiftStart: Temporal.PlainDate
	/** End of the shift */
	shiftEnd: Temporal.PlainDate
}

export type ShiftTypeWrapper
	= ShiftTypeWeeklyByDayWrapper | ShiftTypeWeeklyByWeekWrapper

// Cell interfaces, each representing a single cell type

export interface BaseCell {
	type:
		| 'string'
		| 'week'
		| 'zoned-date-time'
		| 'user'
		| 'shift-types'
		| 'shifts'
	data: unknown
}

export interface StringCell extends BaseCell {
	type: 'string'
	data: string
}

export interface WeekCell extends BaseCell {
	type: 'week'
	data: IsoWeekDateWithoutDay
}

export interface ZonedDateTimeDataCell extends BaseCell {
	type: 'zoned-date-time'
	data: Temporal.ZonedDateTime
}

export interface UserCell extends BaseCell {
	type: 'user'
	data: User
}

export interface ShiftTypesDataCell extends BaseCell {
	type: 'shift-types'
	data: ShiftTypeWrapper[]
}

export interface ShiftsDataCell extends BaseCell {
	type: 'shifts'
	data: Shift[]
}

// Type aliases for cell tuples, each alias represents the cell types of a specific row type

/** Represents the cell types of the header row */
export type HeaderRow = [StringCell, WeekCell, ...ZonedDateTimeDataCell[]]

/** Represents the cell types of the shift types row */
export type ShiftTypesRow = [StringCell, ...ShiftTypesDataCell[]]

/** Represents the cell types of a single shift row */
export type ShiftsRow = [UserCell, ...ShiftsDataCell[]]

// Multi step action interfaces

export interface MultiStepActionBase {
	type: 'creation' | 'motion' | undefined
	columnIndex: number
}

export interface UndefinedMultiStepAction extends MultiStepActionBase {
	type: undefined
	columnIndex: -1
}

export interface DefinedMultiStepActionBase extends MultiStepActionBase {
	type: 'creation' | 'motion'
}

export interface CreationMultiStepAction extends DefinedMultiStepActionBase {
	type: 'creation'
	shiftTypeWrapper: ShiftTypeWrapper
}

export interface MotionMultiStepAction extends DefinedMultiStepActionBase {
	type: 'motion'
	shift: Shift
}

export type DefinedMultiStepAction
	= CreationMultiStepAction | MotionMultiStepAction

export type MultiStepAction = UndefinedMultiStepAction | DefinedMultiStepAction

// Injection keys for multi step action

export const setMultiStepActionIK
	= Symbol('setMultiStepActionIK') as InjectionKey<
		(action: MultiStepAction) => void
	>

export const resetMultiStepActionIK
	= Symbol('resetMultiStepActionIK') as InjectionKey<() => void>

export const multiStepActionIK
	= Symbol('multiStepActionIK') as InjectionKey<Ref<MultiStepAction>>

// Injection keys for shift deletion

export const onShiftDeletionAttemptIK
	= Symbol('onShiftDeletionAttemptIK') as InjectionKey<
		(shift: Shift, columnIndex: number) => Promise<Shift>
	>

export const deletionShiftIK
	= Symbol('deletionShiftIK') as InjectionKey<Ref<Shift | undefined>>

export const setDeletionShiftIK
	= Symbol('setDeletionShiftIK') as InjectionKey<(shift: Shift) => void>

export const resetDeletionShiftIK
	= Symbol('resetDeletionShiftIK') as InjectionKey<() => void>
