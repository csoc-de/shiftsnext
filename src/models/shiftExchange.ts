import type { Component } from 'vue'
import type { Shift } from '../models/shift.ts'
import type { User } from '../models/user.ts'

import { t } from '@nextcloud/l10n'
// @ts-expect-error package has no types
import CheckCircleOutline from 'vue-material-design-icons/CheckCircleOutline.vue'
// @ts-expect-error package has no types
import ClockOutline from 'vue-material-design-icons/ClockOutline.vue'
// @ts-expect-error package has no types
import CloseCircleOutline from 'vue-material-design-icons/CloseCircleOutline.vue'
import { APP_ID } from '../utils/appId.ts'

export interface ShiftExchangeBase {
	comment: string
}

export interface ShiftExchangePostPayloadBase extends ShiftExchangeBase {
	shift_a_id: number
}

export interface RegularShiftExchangePostPayload extends ShiftExchangePostPayloadBase {
	shift_b_id: number
}

export interface TransferShiftExchangePostPayload extends ShiftExchangePostPayloadBase {
	transfer_to_user_id: string
}

export type ShiftExchangePostPayload
	= RegularShiftExchangePostPayload | TransferShiftExchangePostPayload

export const APPROVED_OPTIONS = [true, null, false] as const

export type Approved = typeof APPROVED_OPTIONS[number]

export type ApprovedTranslations = Record<`${Approved}`, string>

export const approvedTranslations: ApprovedTranslations = {
	true: t(APP_ID, 'Approved'),
	null: t(APP_ID, 'Pending'),
	false: t(APP_ID, 'Rejected'),
}

export interface Approveds {
	user?: Approved
	admin?: Approved
}

export interface ShiftExchangePatchPayload extends Partial<ShiftExchangeBase> {
	approveds: Approveds
}

export const EXCHANGE_TYPES = ['regular', 'transfer'] as const

export type ShiftExchangeType = (typeof EXCHANGE_TYPES)[number]

export type ExchangeTypeTranslations = Record<ShiftExchangeType, string>

export const exchangeTypeTranslations: ExchangeTypeTranslations = {
	regular: t(APP_ID, 'Regular'),
	transfer: t(APP_ID, 'Transfer'),
}

export interface ExchangeApproval {
	user: User | null
	approved: Approved
}

export interface ShiftExchangeResponsePayloadBase extends ShiftExchangeBase {
	id: number
	shift_a: Shift
	done: boolean
	approved: Approved
	user_a_approval: ExchangeApproval
	user_b_approval: ExchangeApproval
	admin_approval: ExchangeApproval
}

export interface RegularShiftExchange extends ShiftExchangeResponsePayloadBase {
	shift_b: Shift
}

export interface TransferShiftExchange extends ShiftExchangeResponsePayloadBase {
	transfer_to_user: User
}

export type ShiftExchange = RegularShiftExchange | TransferShiftExchange

// Misc

export type ExchangeParticipant = 'userA' | 'userB'

export type ExchangeEditor = ExchangeParticipant | 'admin'

// Component-related

export const approvedIconComponents = {
	true: CheckCircleOutline,
	null: ClockOutline,
	false: CloseCircleOutline,
} as const satisfies Record<`${Approved}`, Component>

export const approvedColorClasses = {
	true: 'text-nc-element-success',
	null: 'text-nc-element-warning',
	false: 'text-nc-element-error',
} as const satisfies Record<`${Approved}`, string>
