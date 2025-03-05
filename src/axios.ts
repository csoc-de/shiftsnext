import { reviver } from './date'

/**
 * See {@link reviver} for when to use this function
 *
 * @param data The data to transform
 */
export function transformResponse(data: string): unknown {
	return JSON.parse(data, reviver)
}
