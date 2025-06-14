export interface ErrorResponse {
	error: string
}

export class ShiftTypeWrapperNotFoundError extends Error {
	constructor(message: string) {
		super(message)
		this.name = 'ShiftTypeWrapperNotFoundError'
	}
}
