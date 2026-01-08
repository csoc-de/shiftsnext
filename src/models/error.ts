export interface ErrorResponsePayload {
	error: string
	localizedError?: string
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
