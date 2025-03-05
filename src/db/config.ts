import axios, { type AxiosError } from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { handleError } from '../error'
import type { AppConfig, ConfigPayload, DefaultGroups } from '../models/config'
import { CONFIG_PATH } from '../url'
import type { ErrorResponse } from '../models/error'

/**
 * Save the default groups
 * @param payload The default groups
 */
export async function putDefaultGroups(payload: DefaultGroups): Promise<void> {
	try {
		await axios.put(
			generateUrl(`${CONFIG_PATH}/user/default-groups`), payload,
		)
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
 * @param payload The app config
 */
export async function putAppConfig(
	payload: ConfigPayload<AppConfig>,
): Promise<void> {
	try {
		await axios.put(generateUrl(`${CONFIG_PATH}/app`), payload)
	} catch (error: unknown) {
		handleError(
			error as AxiosError<ErrorResponse>,
			'save',
			'app config',
			false,
		)
		throw error
	}
}
