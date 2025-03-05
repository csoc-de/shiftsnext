/**
 * Gets either a light or dark color, depending on whether
 * `referenceColor` itself is a dark or light color, respectively
 *
 * @param referenceColor Color in HEX format
 *
 * @return {string} Either `"#EBEBEB"` if `referenceColor` is dark, or `"#222222"` if `referenceColor` is light
 *
 * @author ChatGPT
 */
export function getContrastColor(
	referenceColor: string,
): '#EBEBEB' | '#222222' {
	// Remove the '#' if it exists
	if (referenceColor.startsWith('#')) {
		referenceColor = referenceColor.slice(1)
	}

	// Convert 3-digit hex to 6-digit hex
	if (referenceColor.length === 3) {
		referenceColor = referenceColor
			.split('')
			.map((char) => char + char)
			.join('')
	}

	// Parse the red, green, and blue components
	const red = parseInt(referenceColor.slice(0, 2), 16)
	const green = parseInt(referenceColor.slice(2, 4), 16)
	const blue = parseInt(referenceColor.slice(4, 6), 16)

	// Calculate the luminance using the relative luminance formula
	const luminance = 0.2126 * red + 0.7152 * green + 0.0722 * blue

	// Return '#222222' for bright reference colors and '#EBEBEB' for dark reference colors
	return luminance > 128 ? '#222222' : '#EBEBEB'
}
