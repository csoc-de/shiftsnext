import type { InjectionKey } from 'vue'
import type { Shift } from '../models/shift.ts'
import type { User } from '../models/user.ts'
import type { ExchangeApprovalType } from './config.ts'
import type { GroupShiftAdminRelationsByGroup } from './groupShiftAdminRelation.ts'

// Requests

export interface ShiftExchangeBase {
	comment: string
}

export interface ShiftExchangePostRequestBase extends ShiftExchangeBase {
	shift_a_id: number
}

export interface RegularShiftExchangePostRequest
	extends ShiftExchangePostRequestBase {
	shift_b_id: number
}

export interface TransferShiftExchangePostRequest
	extends ShiftExchangePostRequestBase {
	transfer_to_user_id: string
}

export type ShiftExchangePostRequest =
	| RegularShiftExchangePostRequest
	| TransferShiftExchangePostRequest

export type Approved = boolean | null

export interface UserApproved {
	user: Approved
}

export interface AdminApproved {
	admin: Approved
}

export type Approveds = UserApproved | AdminApproved
export interface ShiftExchangePutRequest extends Partial<ShiftExchangeBase> {
	approveds: Approveds
}

export type ShiftExchangeType = 'regular' | 'transfer'

// Responses

export interface ExchangeApproval {
	user: User | null
	approved: Approved
}

export interface ShiftExchangeResponseBase extends ShiftExchangeBase {
	id: number
	shift_a: Shift
	done: boolean
	approved: Approved
	user_a_approval: ExchangeApproval
	user_b_approval: ExchangeApproval
	admin_approval: ExchangeApproval
}

export interface RegularShiftExchange extends ShiftExchangeResponseBase {
	shift_b: Shift
}

export interface TransferShiftExchangeResponse
	extends ShiftExchangeResponseBase {
	transfer_to_user: User
}

export type ShiftExchange =
	| RegularShiftExchange
	| TransferShiftExchangeResponse

// Injection keys

export type CreateShiftExchange = (
	payload: ShiftExchangePostRequest,
) => Promise<void>

export type UpdateShiftExchange = (
	id: number,
	payload: ShiftExchangePutRequest,
) => Promise<void>

export type RemoveShiftExchange = (id: number) => Promise<void>

export const createIK
	= Symbol('createIK') as InjectionKey<CreateShiftExchange>

export const updateIK
	= Symbol('updateIK') as InjectionKey<UpdateShiftExchange>

export const removeIK
	= Symbol('removeIK') as InjectionKey<RemoveShiftExchange>

export const exchangeApprovalTypeIK
	= Symbol('exchangeApprovalTypeIK') as InjectionKey<ExchangeApprovalType>

export const relationsIK
	= Symbol('relationsIK') as InjectionKey<GroupShiftAdminRelationsByGroup[]>

// Misc

export type ExchangeParticipant = 'userA' | 'userB'

export type ExchangeEditor = ExchangeParticipant | 'admin'
