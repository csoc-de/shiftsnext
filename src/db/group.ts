import type { ErrorResponsePayload } from '../models/error.ts'
import type { Group, GroupFilters } from '../models/group.ts'

import axios, { type AxiosError } from '@nextcloud/axios'
import { handleError } from '../utils/error.ts'
import { generateUrlWithSearchParams, GROUPS_PATH } from '../utils/url.ts'

/**
 * Get the groups
 *
 * @param filters The filters
 */
export async function getGroups(filters: GroupFilters = {}): Promise<Group[]> {
	try {
		return (
			await axios.get<Group[]>(generateUrlWithSearchParams(GROUPS_PATH, filters))
		).data
	} catch (error: unknown) {
		handleError(error as AxiosError<ErrorResponsePayload>, 'fetch', 'groups')
		throw error
	}
}
