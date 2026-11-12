import type { Primitive } from '../models/native.ts'

export interface SearchParams {
	[x: string]: Primitive | Primitive[]
}
