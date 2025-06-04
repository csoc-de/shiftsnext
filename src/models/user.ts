import type { ReadonlyDeep } from '../models/misc.ts'
import type { SearchParams } from '../models/url.ts'

export type User = ReadonlyDeep<{
	id: string
	display_name: string
}>

export interface UserFilters extends SearchParams {
	group_ids?: string[]
	restricted?: boolean
}
