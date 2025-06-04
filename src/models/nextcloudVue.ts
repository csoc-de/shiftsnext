import type { Calendar, ExchangeApprovalType } from '../models/config.ts'
import type { Shift } from '../models/shift.ts'

export interface NcSelectShiftOption extends Shift {
	label: string
}

export interface NcSelectCalendarOption extends Calendar {
	label: string
}

export interface NcSelectExchangeApprovalTypeOption {
	id: ExchangeApprovalType
	label: string
}

export interface NcSelectUsersOption {
	displayName: string
	id: string
}
