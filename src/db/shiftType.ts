import type { ErrorResponse } from '../models/error.ts'
import type {
	ShiftType,
	ShiftTypeFilters,
	ShiftTypePostPayload,
	ShiftTypePutPayload,
} from '../models/shiftType.ts'

import axios, { type AxiosError } from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { transformResponse } from '../axios.ts'
import { handleError } from '../error.ts'
import { generateUrlWithSearchParams, SHIFT_TYPES_PATH } from '../url.ts'

/**
 * Get shift types
 *
 * @param filters The filters
 */
export async function getShiftTypes(filters: ShiftTypeFilters = {}): Promise<ShiftType[]> {
	try {
		return (
			await axios.get<ShiftType[]>(
				generateUrlWithSearchParams(SHIFT_TYPES_PATH, filters),
				{ transformResponse },
			)
		).data
	} catch (error: unknown) {
		handleError(error as AxiosError<ErrorResponse>, 'fetch', 'shift types')
		throw error
	}
}

/**
 * Create shift type
 *
 * @param payload The shift type
 */
export async function postShiftType(payload: ShiftTypePostPayload): Promise<ShiftType> {
	try {
		return (
			await axios.post<ShiftType>(
				generateUrl(SHIFT_TYPES_PATH),
				payload,
				{ transformResponse },
			)
		).data
	} catch (error: unknown) {
		handleError(error as AxiosError<ErrorResponse>, 'create', 'shift type')
		throw error
	}
}

/**
 * Update shift type
 *
 * @param id The shift type id
 * @param payload The shift type
 */
export async function putShiftType(
	id: number,
	payload: ShiftTypePutPayload,
): Promise<ShiftType> {
	try {
		return (
			await axios.put<ShiftType>(
				generateUrl(`${SHIFT_TYPES_PATH}/${id}`),
				payload,
				{ transformResponse },
			)
		).data
	} catch (error: unknown) {
		handleError(error as AxiosError<ErrorResponse>, 'update', 'shift type')
		throw error
	}
}

/**
 * Delete shift type
 *
 * @param id The shift type id
 */
export async function deleteShiftType(id: number): Promise<ShiftType> {
	try {
		return (
			await axios.delete<ShiftType>(
				generateUrl(`${SHIFT_TYPES_PATH}/${id}`),
				{ transformResponse },
			)
		).data
	} catch (error: unknown) {
		handleError(error as AxiosError<ErrorResponse>, 'delete', 'shift type')
		throw error
	}
}
