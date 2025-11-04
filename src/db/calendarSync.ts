import type {
	SynchronizeByGroupsRequest,
	SynchronizeByShiftsRequest,
	SynchronizeResponse,
} from '../models/calendarSync.ts'
import type { ErrorResponse } from '../models/error.ts'

import axios, { type AxiosError } from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { handleError } from '../error.ts'
import { CALENDAR_PATH } from '../url.ts'

/**
 * Synchronize the calendar by groups
 *
 * @param payload The request payload
 */
export async function postSynchronizeByGroups(payload: SynchronizeByGroupsRequest): Promise<SynchronizeResponse> {
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
 * Synchronize the calendar by shifts
 *
 * @param payload The request payload
 */
export async function postSynchronizeByShifts(payload: SynchronizeByShiftsRequest): Promise<SynchronizeResponse> {
	try {
		return (
			await axios.post<SynchronizeResponse>(
				generateUrl(`${CALENDAR_PATH}/synchronize-by-shifts`),
				payload,
			)
		).data
	} catch (error: unknown) {
		handleError(
			error as AxiosError<ErrorResponse>,
			'synchronize',
			'calendar by shifts',
		)
		throw error
	}
}
