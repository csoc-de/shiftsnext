import type { AppConfig, ConfigPayload, DefaultGroups } from '../models/config.ts'
import type { ErrorResponse } from '../models/error.ts'

import axios, { type AxiosError } from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { handleError } from '../utils/error.ts'
import { CONFIG_PATH } from '../utils/url.ts'

/**
 * Save the default groups
 *
 * @param payload The default groups
 */
export async function putDefaultGroups(payload: DefaultGroups): Promise<void> {
	try {
		await axios.put(generateUrl(`${CONFIG_PATH}/user/default-groups`), payload)
	} catch (error: unknown) {
		handleError(
			error as AxiosError<ErrorResponse>,
			'save',
			'default groups',
			false,
		)
		throw error
	}
}

/**
 * Save the app config
 *
 * @param payload The app config
 */
export async function putAppConfig(payload: ConfigPayload<AppConfig>): Promise<void> {
	try {
		await axios.put(generateUrl(`${CONFIG_PATH}/app`), payload)
	} catch (error: unknown) {
		handleError(
			error as AxiosError<ErrorResponse>,
			'save',
			'app config',
		)
		throw error
	}
}
