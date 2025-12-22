import type { AppConfig, ConfigPutPayload, DefaultGroupsPutPayload } from '../models/config.ts'
import type { ErrorResponsePayload } from '../models/error.ts'

import axios, { type AxiosError } from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { handleError } from '../utils/error.ts'
import { CONFIG_PATH } from '../utils/url.ts'

/**
 * Save the default groups
 *
 * @param payload The request payload
 */
export async function putDefaultGroups(payload: DefaultGroupsPutPayload): Promise<void> {
	try {
		await axios.put(generateUrl(`${CONFIG_PATH}/user/default-groups`), payload)
	} catch (error: unknown) {
		handleError(
			error as AxiosError<ErrorResponsePayload>,
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
 * @param payload The request payload
 */
export async function putAppConfig(payload: ConfigPutPayload<AppConfig>): Promise<void> {
	try {
		await axios.put(generateUrl(`${CONFIG_PATH}/app`), payload)
	} catch (error: unknown) {
		handleError(
			error as AxiosError<ErrorResponsePayload>,
			'save',
			'app config',
		)
		throw error
	}
}
