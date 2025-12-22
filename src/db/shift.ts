import type { ErrorResponsePayload } from '../models/error.ts'
import type {
	Shift,
	ShiftFilters,
	ShiftPatchPayload,
	ShiftPostPayload,
} from '../models/shift.ts'

import axios, { type AxiosError } from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { transformResponse } from '../utils/axios.ts'
import { handleError } from '../utils/error.ts'
import { generateUrlWithSearchParams, SHIFTS_PATH } from '../utils/url.ts'

/**
 * Get shifts
 *
 * @param filters The filters
 */
export async function getShifts(filters: ShiftFilters = {}): Promise<Shift[]> {
	try {
		return (
			await axios.get<Shift[]>(
				generateUrlWithSearchParams(SHIFTS_PATH, filters),
				{ transformResponse },
			)
		).data
	} catch (error: unknown) {
		handleError(error as AxiosError<ErrorResponsePayload>, 'fetch', 'shifts')
		throw error
	}
}

/**
 * Create shift
 *
 * @param payload The shift
 */
export async function postShift(payload: ShiftPostPayload): Promise<Shift> {
	try {
		return (
			await axios.post<Shift>(generateUrl(SHIFTS_PATH), payload, {
				transformResponse,
			})
		).data
	} catch (error: unknown) {
		handleError(error as AxiosError<ErrorResponsePayload>, 'create', 'shift')
		throw error
	}
}

/**
 * Update shift
 *
 * @param id The shift id
 * @param payload The shift
 */
export async function patchShift(
	id: number,
	payload: ShiftPatchPayload,
): Promise<Shift> {
	try {
		return (
			await axios.patch<Shift>(
				generateUrl(`${SHIFTS_PATH}/${id}`),
				payload,
				{ transformResponse },
			)
		).data
	} catch (error: unknown) {
		handleError(error as AxiosError<ErrorResponsePayload>, 'update', 'shift')
		throw error
	}
}

/**
 * Delete shift
 *
 * @param id The shift id
 */
export async function deleteShift(id: number): Promise<Shift> {
	try {
		return (
			await axios.delete<Shift>(generateUrl(`${SHIFTS_PATH}/${id}`), {
				transformResponse,
			})
		).data
	} catch (error: unknown) {
		handleError(error as AxiosError<ErrorResponsePayload>, 'delete', 'shift')
		throw error
	}
}
