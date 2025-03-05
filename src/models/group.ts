import type { ReadonlyDeep } from '../models/misc'
import type { SearchParams } from '../models/url'

export type Group = ReadonlyDeep<{
	id: string
	display_name: string
}>

export interface GroupFilters extends SearchParams {
	restricted?: boolean
}
