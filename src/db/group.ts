import axios, { type AxiosError } from '@nextcloud/axios'
import { handleError } from '../error'
import type { ErrorResponse } from '../models/error'
import type { Group, GroupFilters } from '../models/group'
import { generateUrlWithSearchParams, GROUPS_PATH } from '../url'

/**
 * Get the groups
 * @param filters The filters
 */
export async function getGroups(filters: GroupFilters = {}): Promise<Group[]> {
	try {
		return (
			await axios.get<Group[]>(
				generateUrlWithSearchParams(GROUPS_PATH, filters),
			)
		).data
	} catch (error: unknown) {
		handleError(error as AxiosError<ErrorResponse>, 'fetch', 'groups')
		throw error
	}
}
