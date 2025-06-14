export interface ErrorResponse {
	error: string
}

export class ShiftsRowNotFoundError extends Error {
	constructor(message: string) {
		super(message)
		this.name = 'ShiftsRowNotFoundError'
	}
}

export class ShiftTypeWrapperNotFoundError extends Error {
	constructor(message: string) {
		super(message)
		this.name = 'ShiftTypeWrapperNotFoundError'
	}
}
