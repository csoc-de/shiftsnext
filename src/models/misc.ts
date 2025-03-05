export type ReadonlyDeep<T> = {
	readonly [P in keyof T]: ReadonlyDeep<T[P]>
}

export type StringifiedBoolean = 'true' | 'false'

export type StringifiedNullableBoolean = StringifiedBoolean | 'null'
