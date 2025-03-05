import { upperFirst } from 'lodash-es'
import type { Calendar, ExchangeApprovalType } from './models/config'
import type {
	NcSelectCalendarOption,
	NcSelectExchangeApprovalTypeOption,
	NcSelectShiftOption,
	NcSelectUserOption,
} from './models/nextcloudVue'
import type { Shift } from './models/shift'
import type { User } from './models/user'
import { t } from '@nextcloud/l10n'
import { APP_ID } from './appId'

/**
 * Get the NcSelectUserOption for a User
 * @param user User
 */
export function getNcSelectUserOption(user: User): NcSelectUserOption {
	return {
		displayName: user.display_name,
		id: user.id,
		showUserStatus: false,
		user: user.id,
	}
}

/**
 * Get the NcSelectShiftOption for a Shift
 * @param shift Shift
 */
export function getNcSelectShiftOption(shift: Shift): NcSelectShiftOption {
	const {
		shift_type: { group, name },
	} = shift
	return { ...shift, label: `${group.display_name} - ${name}` }
}

/**
 * Get the NcSelectCalendarOption for a Calendar
 * @param calendar Calendar
 */
export function getNcSelectCalendarOption(
	calendar: Calendar,
): NcSelectCalendarOption {
	return {
		...calendar,
		label: `${calendar.ownerDisplayName} - ${calendar.displayName}`,
	}
}

/**
 * Get the NcSelectExchangeApprovalTypeOption for a ExchangeApprovalType
 * @param exchangeApprovalType ExchangeApprovalType
 */
export function getNcSelectExchangeApprovalTypeOption(
	exchangeApprovalType: ExchangeApprovalType,
): NcSelectExchangeApprovalTypeOption {
	return {
		id: exchangeApprovalType,
		label: t(APP_ID, upperFirst(exchangeApprovalType)),
	}
}
