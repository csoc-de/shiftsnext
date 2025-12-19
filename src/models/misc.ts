export type ReadonlyDeep<T> = {
	readonly [P in keyof T]: ReadonlyDeep<T[P]>
}
