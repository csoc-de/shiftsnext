import type { ErrorResponse } from '../models/error.ts'
import type {
	ShiftExchange,
	ShiftExchangePostRequest,
	ShiftExchangePutRequest,
} from '../models/shiftExchange.ts'

import axios, { type AxiosError } from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { transformResponse } from '../axios.ts'
import { handleError } from '../error.ts'
import { SHIFT_EXCHANGES_PATH } from '../url.ts'

/**
 * Get shift exchanges
 */
export async function getShiftExchanges(): Promise<ShiftExchange[]> {
	try {
		return (
			await axios.get<ShiftExchange[]>(
				generateUrl(SHIFT_EXCHANGES_PATH),
				{ transformResponse },
			)
		).data
	} catch (error: unknown) {
		handleError(
			error as AxiosError<ErrorResponse>,
			'fetch',
			'shift exchanges',
		)
		throw error
	}
}

/**
 * Create shift exchange
 *
 * @param payload The shift exchange
 */
export async function postShiftExchange(payload: ShiftExchangePostRequest): Promise<ShiftExchange> {
	try {
		return (
			await axios.post<ShiftExchange>(
				generateUrl(SHIFT_EXCHANGES_PATH),
				payload,
				{ transformResponse },
			)
		).data
	} catch (error: unknown) {
		handleError(
			error as AxiosError<ErrorResponse>,
			'create',
			'shift exchange',
		)
		throw error
	}
}

/**
 * Update shift exchange
 *
 * @param id The shift exchange id
 * @param payload The shift exchange
 */
export async function putShiftExchange(
	id: number,
	payload: ShiftExchangePutRequest,
): Promise<ShiftExchange> {
	try {
		return (
			await axios.put<ShiftExchange>(
				generateUrl(`${SHIFT_EXCHANGES_PATH}/${id}`),
				payload,
				{ transformResponse },
			)
		).data
	} catch (error: unknown) {
		handleError(
			error as AxiosError<ErrorResponse>,
			'update',
			'shift exchange',
		)
		throw error
	}
}

/**
 * Delete shift exchange
 *
 * @param id The shift exchange id
 */
export async function deleteShiftExchange(id: number): Promise<ShiftExchange> {
	try {
		return (
			await axios.delete<ShiftExchange>(
				generateUrl(`${SHIFT_EXCHANGES_PATH}/${id}`),
				{ transformResponse },
			)
		).data
	} catch (error: unknown) {
		handleError(
			error as AxiosError<ErrorResponse>,
			'delete',
			'shift exchange',
		)
		throw error
	}
}
