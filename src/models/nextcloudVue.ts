import type { Calendar, ExchangeApprovalType } from '../models/config'
import type { Shift } from '../models/shift'

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

/**
 * @see {@link https://next--nextcloud-vue-components.netlify.app/#/Components/NcSelect?id=ncselect-1|NcSelect}
 * @see {@link https://next--nextcloud-vue-components.netlify.app/#/Components/NcListItems?id=nclistitemicon|NcListItemIcon}
 * @see {@link https://next--nextcloud-vue-components.netlify.app/#/Components/NcAvatar|NcAvatar}
 */
export interface NcSelectUserOption {
	displayName: string
	id: string
	showUserStatus: boolean
	user: string
}
