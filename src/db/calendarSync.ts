import axios, { type AxiosError } from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { handleError } from '../error'
import type {
	SynchronizeByGroupsRequest,
	SynchronizeByShiftRequest,
	SynchronizeResponse,
} from '../models/calendarSync'
import { CALENDAR_PATH } from '../url'
import type { ErrorResponse } from '../models/error'

/**
 * Synchronize the calendar by groups
 * @param payload The request payload
 */
export async function postSynchronizeByGroups(
	payload: SynchronizeByGroupsRequest,
): Promise<SynchronizeResponse> {
	try {
		return (
			await axios.post<SynchronizeResponse>(
				generateUrl(`${CALENDAR_PATH}/synchronize-by-groups`),
				payload,
			)
		).data
	} catch (error: unknown) {
		handleError(
			error as AxiosError<ErrorResponse>,
			'synchronize',
			'calendar by groups',
		)
		throw error
	}
}

/**
 * Synchronize the calendar by shift
 * @param payload The request payload
 */
export async function postSynchronizeByShift(
	payload: SynchronizeByShiftRequest,
): Promise<SynchronizeResponse> {
	try {
		return (
			await axios.post<SynchronizeResponse>(
				generateUrl(`${CALENDAR_PATH}/synchronize-by-shift`),
				payload,
			)
		).data
	} catch (error: unknown) {
		handleError(
			error as AxiosError<ErrorResponse>,
			'synchronize',
			'calendar by shift',
		)
		throw error
	}
}
