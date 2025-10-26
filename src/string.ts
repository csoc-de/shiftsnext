/**
 * Convers the first character of `value` to upper case
 *
 * @param value The string to convert
 */
export function upperFirst<T extends string>(value: T): Capitalize<T> {
	return (value.substring(0, 1).toUpperCase() + value.substring(1)) as Capitalize<T>
}
