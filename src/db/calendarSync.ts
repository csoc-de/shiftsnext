import type {
	AbsenceBlocker,
	SynchronizeByGroupsPostPayload,
	SynchronizeByShiftsPostPayload,
	SynchronizeResponsePayload,
} from '../models/calendarSync.ts'
import type { ErrorResponsePayload } from '../models/error.ts'

import axios, { type AxiosError } from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { handleError } from '../utils/error.ts'
import { CALENDAR_PATH, generateUrlWithSearchParams } from '../utils/url.ts'

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

/**
 * Fetch absence blockers for a week and selected users
 *
 * @param weekDate ISO week date, e.g. 2026-W23
 * @param userIds User IDs to include in the response
 */
export async function getAbsenceBlockers(
	weekDate: string,
	userIds: string[],
): Promise<AbsenceBlocker[]> {
	try {
		return (
			await axios.get<AbsenceBlocker[]>(generateUrlWithSearchParams(`${CALENDAR_PATH}/absence-blockers`, {
				week_date: weekDate,
				user_ids: userIds,
			}))
		).data
	} catch (error: unknown) {
		handleError(
			error as AxiosError<ErrorResponsePayload>,
			'fetch',
			'absence blockers',
			false,
		)
		throw error
	}
}
