import type { ReadonlyDeep } from '../models/misc.ts'
import type { SearchParams } from '../models/url.ts'

export type Group = ReadonlyDeep<{
	id: string
	display_name: string
}>

export interface GroupFilters extends SearchParams {
	restricted?: boolean
}
