import type { ErrorResponse } from '../models/error.ts'
import type { User, UserFilters } from '../models/user.ts'

import axios, { type AxiosError } from '@nextcloud/axios'
import { handleError } from '../error.ts'
import { generateUrlWithSearchParams, USERS_PATH } from '../url.ts'

/**
 * Get users
 *
 * @param filters The filters
 */
export async function getUsers(filters: UserFilters = {}): Promise<User[]> {
	try {
		return (
			await axios.get<User[]>(generateUrlWithSearchParams(USERS_PATH, filters))
		).data
	} catch (error: unknown) {
		handleError(error as AxiosError<ErrorResponse>, 'fetch', 'users')
		throw error
	}
}
