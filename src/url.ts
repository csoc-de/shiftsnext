import type { SearchParams } from './models/url.ts'

import { generateUrl, getBaseUrl } from '@nextcloud/router'
import { APP_ID } from './appId.ts'

// These constants should be used in calls to `generateUrl`

export const BASE_PATH = `/apps/${APP_ID}/api`

export const SHIFTS_PATH = `${BASE_PATH}/shifts`
export const SHIFT_TYPES_PATH = `${BASE_PATH}/shift-types`
export const SHIFT_EXCHANGES_PATH = `${BASE_PATH}/shift-exchanges`
export const GROUP_SHIFT_ADMIN_RELATIONS_PATH = `${BASE_PATH}/group-shift-admin-relations`
export const GROUP_USER_RELATIONS_PATH = `${BASE_PATH}/group-user-relations`
export const GROUPS_PATH = `${BASE_PATH}/groups`
export const USERS_PATH = `${BASE_PATH}/users`
export const CALENDAR_PATH = `${BASE_PATH}/calendars`
export const CONFIG_PATH = `${BASE_PATH}/config`

/**
 * Generates a URL with search parameters
 *
 * @param url The URL to generate
 * @param searchParams The search parameters to append
 */
export function generateUrlWithSearchParams(
	url: string,
	searchParams: SearchParams,
): string {
	const urlInstance = new URL(generateUrl(url, undefined, { baseURL: getBaseUrl() }))
	for (let [key, value] of Object.entries(searchParams)) {
		if (Array.isArray(value)) {
			key = `${key}[]`
		} else {
			value = [value]
		}
		for (const item of value) {
			const stringified
				= item instanceof Date ? item.toISOString() : String(item)
			urlInstance.searchParams.append(key, stringified)
		}
	}
	return urlInstance.toString()
}
