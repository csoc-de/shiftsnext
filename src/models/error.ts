export interface ErrorResponse {
	error: string
}

export class RecoverableError extends Error {
	constructor(message: string) {
		super(message)
		this.name = 'RecoverableError'
	}
}
