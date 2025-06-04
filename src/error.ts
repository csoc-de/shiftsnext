import type { AxiosError } from '@nextcloud/axios'
import type { ErrorResponse } from './models/error.ts'

import { showError } from '@nextcloud/dialogs'
import { t } from '@nextcloud/l10n'
import { APP_ID } from './appId.ts'
import { logger } from './logger.ts'
import { TOAST_TIMEOUT } from './toast.ts'

/**
 * Prints an error to the console consisting of the passed parameters
 *
 * Also shows a toast if `showToast` is `true`
 *
 * @example
 * ```JavaScript
 * handleError(error, "create", "foo")
 * // Prints "Failed to create foo" followed by the actual error message
 * // Shows "Failed to create foo" in an error toast
 * ```
 *
 * @param error The Axios error
 * @param operation The operation that failed
 * @param subject The subject of the operation
 * @param showToast By default, this method shows an error toast.
 * Pass `false` if this is not desired.
 */
export function handleError(
	error: AxiosError<ErrorResponse>,
	operation: string,
	subject: string,
	showToast: boolean = true,
): void {
	logger.error(`Failed to ${operation} ${subject}: ${error.response?.data.error ?? error.message}`)
	const translated = t(APP_ID, 'Failed to {operation} {subject}', {
		operation,
		subject,
	})
	if (showToast) {
		showError(translated, { timeout: TOAST_TIMEOUT })
	}
}
