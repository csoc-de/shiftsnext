export type ExchangeApprovalType = 'users' | 'admin' | 'all'

export interface AppConfig {
	common_calendar_id: number
	absence_calendar_id: number
	sync_to_personal_calendar: boolean
	ignore_absence_for_by_week_shifts: boolean
	exchange_approval_type: ExchangeApprovalType
}

export interface UserConfig {
	defaultGroupIds: string[]
}

export type Config = AppConfig | UserConfig

export interface ConfigPayload<T extends Config> {
	/**
	 * This envelope is necessary so the backend controller receives a single
	 * `values` array as its parameter
	 */
	values: T
}

export interface DefaultGroups {
	group_ids: string[]
}

export interface Calendar {
	id: number
	uri: string
	principalUri: string
	displayName: string
	ownerDisplayName: string
}
