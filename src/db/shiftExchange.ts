import type { ErrorResponsePayload } from '../models/error.ts'
import type {
	ShiftExchange,
	ShiftExchangePatchPayload,
	ShiftExchangePostPayload,
} from '../models/shiftExchange.ts'

import axios, { type AxiosError } from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { transformResponse } from '../utils/axios.ts'
import { handleError } from '../utils/error.ts'
import { SHIFT_EXCHANGES_PATH } from '../utils/url.ts'

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
			error as AxiosError<ErrorResponsePayload>,
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
export async function postShiftExchange(payload: ShiftExchangePostPayload): Promise<ShiftExchange> {
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
			error as AxiosError<ErrorResponsePayload>,
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
export async function patchShiftExchange(
	id: number,
	payload: ShiftExchangePatchPayload,
): Promise<ShiftExchange> {
	try {
		return (
			await axios.patch<ShiftExchange>(
				generateUrl(`${SHIFT_EXCHANGES_PATH}/${id}`),
				payload,
				{ transformResponse },
			)
		).data
	} catch (error: unknown) {
		handleError(
			error as AxiosError<ErrorResponsePayload>,
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
			error as AxiosError<ErrorResponsePayload>,
			'delete',
			'shift exchange',
		)
		throw error
	}
}
