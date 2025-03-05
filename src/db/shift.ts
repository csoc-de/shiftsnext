import axios, { type AxiosError } from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { transformResponse } from '../axios'
import { handleError } from '../error'
import type { ErrorResponse } from '../models/error'
import type {
	Shift,
	ShiftFilters,
	ShiftPatchRequest,
	ShiftRequest,
} from '../models/shift'
import { generateUrlWithSearchParams, SHIFTS_PATH } from '../url'

/**
 * Get shifts
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
		handleError(error as AxiosError<ErrorResponse>, 'fetch', 'shifts')
		throw error
	}
}

/**
 * Create shift
 * @param payload The shift
 */
export async function postShift(payload: ShiftRequest): Promise<Shift> {
	try {
		return (
			await axios.post<Shift>(generateUrl(SHIFTS_PATH), payload, {
				transformResponse,
			})
		).data
	} catch (error: unknown) {
		handleError(error as AxiosError<ErrorResponse>, 'create', 'shift')
		throw error
	}
}

/**
 * Update shift
 * @param id The shift id
 * @param payload The shift
 */
export async function patchShift(
	id: number,
	payload: ShiftPatchRequest,
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
		handleError(error as AxiosError<ErrorResponse>, 'update', 'shift')
		throw error
	}
}

/**
 * Delete shift
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
		handleError(error as AxiosError<ErrorResponse>, 'delete', 'shift')
		throw error
	}
}
