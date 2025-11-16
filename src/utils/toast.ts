import { showSuccess } from '@nextcloud/dialogs'
import { t } from '@nextcloud/l10n'
import { APP_ID } from './appId.ts'

export const TOAST_TIMEOUT = 3000

/**
 * Shows a toast that indicates that the save operation was successful
 */
export function showSavedToast(): void {
	showSuccess(t(APP_ID, 'Saved successfully'), { timeout: TOAST_TIMEOUT })
}
