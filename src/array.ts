/**
 * Rotates `arr` so that the element at `sourceIndex` will be moved to
 * `targetIndex`. The other elements will be rotated accordingly.
 *
 * @param arr The array to rotate
 * @param sourceIndex The index of the element to move
 * @param targetIndex The index to move the element to
 */
export function rotate<T>(
	arr: readonly T[],
	sourceIndex: number,
	targetIndex: number,
): T[] {
	if (
		![sourceIndex, targetIndex].every(
			(i) => Number.isInteger(i) && i >= 0 && i < arr.length,
		)
	) {
		throw new Error('Invalid indices')
	}
	const diff = sourceIndex - targetIndex
	return [...arr.slice(diff), ...arr.slice(0, diff)]
}

/**
 * Rotates `arr` so that all elements will be moved by `amount` positions.
 * Elements that reach the lower/upper array bounds are moved to the end/start
 * of the array, depending on the direction of the motion
 *
 * @param arr The array to rotate
 * @param amount The amount to move
 * - Negative values will move the elements backwards (to the left)
 * - Positive values will move the elements forwards (to the right)
 */
export function rotateBy<T>(arr: readonly T[], amount: number) {
	if (arr.length < 2 || amount === 0) {
		return arr
	}
	amount = amount % arr.length
	const targetIndex = amount < 0 ? amount + arr.length : amount
	return rotate(arr, 0, targetIndex)
}
