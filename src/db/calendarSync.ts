import type {
	SynchronizeByGroupsPostPayload,
	SynchronizeByShiftsPostPayload,
	SynchronizeResponsePayload,
} from '../models/calendarSync.ts'
import type { ErrorResponsePayload } from '../models/error.ts'

import axios, { type AxiosError } from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { handleError } from '../utils/error.ts'
import { CALENDAR_PATH } from '../utils/url.ts'

/**
 * Synchronize the calendar by groups
 *
 * @param payload The request payload
 */
export async function postSynchronizeByGroups(payload: SynchronizeByGroupsPostPayload): Promise<SynchronizeResponsePayload> {
	try {
		return (
			await axios.post<SynchronizeResponsePayload>(
				generateUrl(`${CALENDAR_PATH}/synchronize-by-groups`),
				payload,
			)
		).data
	} catch (error: unknown) {
		handleError(
			error as AxiosError<ErrorResponsePayload>,
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
export async function postSynchronizeByShifts(payload: SynchronizeByShiftsPostPayload): Promise<SynchronizeResponsePayload> {
	try {
		return (
			await axios.post<SynchronizeResponsePayload>(
				generateUrl(`${CALENDAR_PATH}/synchronize-by-shifts`),
				payload,
			)
		).data
	} catch (error: unknown) {
		handleError(
			error as AxiosError<ErrorResponsePayload>,
			'synchronize',
			'calendar by shifts',
		)
		throw error
	}
}
