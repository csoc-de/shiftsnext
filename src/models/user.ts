import type { ReadonlyDeep } from '../models/misc'
import type { SearchParams } from '../models/url'

export type User = ReadonlyDeep<{
	id: string
	display_name: string
}>

export interface UserFilters extends SearchParams {
	group_ids?: string[]
	restricted?: boolean
}
