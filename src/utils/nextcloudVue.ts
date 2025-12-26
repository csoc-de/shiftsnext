import type {
	NcSelectCalendarOption,
	NcSelectExchangeApprovalTypeOption,
	NcSelectShiftOption,
	NcSelectUsersOption,
} from '../models/nextcloudVue.ts'
import type { Shift } from '../models/shift.ts'
import type { User } from '../models/user.ts'

import {
	type Calendar,
	type ExchangeApprovalType,

	exchangeApprovalTypeTranslations,
} from '../models/config.ts'

/**
 * Get the NcSelectUsersOption for a User
 *
 * @param user User
 */
export function getNcSelectUsersOption(user: User): NcSelectUsersOption {
	return {
		displayName: user.display_name,
		id: user.id,
	}
}

/**
 * Get the NcSelectShiftOption for a Shift
 *
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
 *
 * @param calendar Calendar
 */
export function getNcSelectCalendarOption(calendar: Calendar): NcSelectCalendarOption {
	return {
		...calendar,
		label: `${calendar.ownerDisplayName} - ${calendar.displayName}`,
	}
}

/**
 * Get the NcSelectExchangeApprovalTypeOption for a ExchangeApprovalType
 *
 * @param exchangeApprovalType ExchangeApprovalType
 */
export function getNcSelectExchangeApprovalTypeOption(exchangeApprovalType: ExchangeApprovalType): NcSelectExchangeApprovalTypeOption {
	return {
		id: exchangeApprovalType,
		label: exchangeApprovalTypeTranslations[exchangeApprovalType],
	}
}
