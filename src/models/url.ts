import type { Primitive } from '../models/native.ts'

export type SearchParamValue = Primitive | Date

export interface SearchParams {
	[x: string]: SearchParamValue | SearchParamValue[]
}
