import type { Shift } from '../models/shift.ts'
import type { ShiftType } from '../models/shiftType.ts'

// We are using "en-US" here to be consistent with the
// `Util::naturalSortCompare` method provided by Nextcloud (backend).
// Furthermore, some initial tests revealed switching to another locale
// apparently makes no difference
const collator = new Intl.Collator('en-US', { numeric: true })

export type CompareResult = -1 | 0 | 1

/**
 * Compares `a` and `b`
 *
 * @param a The first value to compare
 * @param b The second value to compare
 *
 * @return Either
 * - `-1` if `a < b` or
 * - `1` if `a > b` or
 * - `0` if `a === b`
 */
export function compare<T extends number | string>(a: T, b: T): CompareResult {
	if (typeof a === 'string' && typeof b === 'string') {
		return collator.compare(a, b) as CompareResult
	}
	return a === b ? 0 : a < b ? -1 : 1
}

/**
 * Compares two shifts by their `start` property
 *
 * @param a First shift
 * @param b Second shift
 */
export function compareShifts(a: Shift, b: Shift) {
	return compare(a.start.toString(), b.start.toString())
}

/**
 * Compares two shift types by their `repetition.config.reference` property
 *
 * @param a First shift type
 * @param b Second shift type
 */
export function compareShiftTypes(a: ShiftType, b: ShiftType) {
	const valueA = a.repetition.weekly_type === 'by_day'
		? a.repetition.config.reference.toPlainTime().toString()
		: a.repetition.config.reference
	const valueB = b.repetition.weekly_type === 'by_day'
		? b.repetition.config.reference.toPlainTime().toString()
		: b.repetition.config.reference
	return compare(valueA, valueB)
}
