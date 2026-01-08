import type { AxiosError } from '@nextcloud/axios'
import type { ErrorResponsePayload } from '../models/error.ts'

import { showError, TOAST_PERMANENT_TIMEOUT } from '@nextcloud/dialogs'
import { t } from '@nextcloud/l10n'
import { APP_ID } from './appId.ts'
import { logger } from './logger.ts'

/**
 * Prints an error to the console
 *
 * Also shows a toast if `showToast` is `true`
 *
 * @example
 * ```JavaScript
 * handleError(error, "create", "foo")
 * // Prints "Failed to create foo" followed by the actual error message
 * // Shows the backend error message within an error toast
 * ```
 *
 * @param error The Axios error
 * @param operation The operation that failed
 * @param subject The subject of the operation
 * @param showToast By default, this method shows an error toast.
 * Pass `false` if this is not desired.
 */
export function handleError(
	error: AxiosError<ErrorResponsePayload>,
	operation: string,
	subject: string,
	showToast: boolean = true,
): void {
	const message = error.response?.data.error ?? error.message
	const localizedMessage
		= error.response?.data.localizedError || t(APP_ID, 'An error occurred')
	logger.error(`Failed to ${operation} ${subject}: ${message}`)
	if (showToast) {
		showError(localizedMessage, { timeout: TOAST_PERMANENT_TIMEOUT })
	}
}
