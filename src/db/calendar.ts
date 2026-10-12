import type { AxiosError } from '@nextcloud/axios'
import type { AbsenceBlocker } from '../models/calendar.ts'
import type { ErrorResponsePayload } from '../models/error.ts'

import axios from '@nextcloud/axios'
import { handleError } from '../utils/error.ts'
import { CALENDAR_PATH, generateUrlWithSearchParams } from '../utils/url.ts'

/**
 * Get the absence blockers for a week and the given users
 *
 * @param weekDate An ISO week date without day, e.g. `2026-W23`
 * @param userIds The users to include in the response
 */
export async function getAbsenceBlockers(
	weekDate: string,
	userIds: string[],
): Promise<AbsenceBlocker[]> {
	try {
		const url = generateUrlWithSearchParams(`${CALENDAR_PATH}/absence-blockers`, {
			week_date: weekDate,
			user_ids: userIds,
		})
		return (await axios.get<AbsenceBlocker[]>(url)).data
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
