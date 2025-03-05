/**
 * Resolves a Promise after `time` milliseconds
 *
 * Await the returned Promise to delay code execution
 *
 * @example
 * ```ts
 * await sleep(5000) // waits for 5 seconds
 * ```
 *
 * @param time Number of milliseconds to delay the Promise fulfillment
 */
export function sleep(time: number): Promise<void> {
	return new Promise((resolve) => setTimeout(resolve, time))
}
