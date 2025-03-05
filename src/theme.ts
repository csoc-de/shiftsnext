/**
 * Initialize the Tailwind theme based on the user's preferred color scheme.
 */
export function initializeTailwindTheme() {
	const body = document.querySelector('body')!
	const theme = body.dataset.themes
	if (!theme || theme === 'default') {
		addDarkModeChangeEventListener()
	} else {
		updateTailwindThemeAttribute(theme.includes('dark'))
	}
}

/**
 * Add a listener for changes to the user's preferred color scheme.
 */
function addDarkModeChangeEventListener() {
	const darkModeMediaQuery = window.matchMedia('(prefers-color-scheme: dark)')
	darkModeMediaQuery.addEventListener('change', (event) => {
		updateTailwindThemeAttribute(event.matches)
	})
	// Call immediately to accommodate for current state
	updateTailwindThemeAttribute(darkModeMediaQuery.matches)
}

/**
 * Update the `data-tw-theme` attribute on the body element to reflect the
 * current theme.
 * @param isDarkMode Whether the current theme is dark mode.
 */
function updateTailwindThemeAttribute(isDarkMode: boolean) {
	const body = document.querySelector('body')!
	body.dataset.twTheme = isDarkMode ? 'dark' : 'light'
}
