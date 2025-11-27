/** @type {import('tailwindcss').Config} */
export default {
	darkMode: ['selector', '[data-tw-theme="dark"]'],
	important: 'body[id][id][data-tw-theme]',
	content: ['./src/**/*.{vue,js,ts,jsx,tsx}'],
	plugins: [],
	theme: {
		colors: {
			transparent: 'transparent',
			current: 'currentColor',
			'nc-favorite': 'var(--color-favorite)', // Color to mark favorites, can be used to color e.g. a star icon for favorites
		},
		backgroundColor: ({ theme }) => ({
			...theme('colors'),
			// Primary
			// 'nc-primary': 'var(--color-primary)', // Primary color configured by user
			// 'nc-primary-hover': 'var(--color-primary-hover)', // Variant of --color-primary for hover effects
			'nc-primary-element': 'var(--color-primary-element)', // Accessibility adjusted variant of --color-primary to be used on interactive elements
			'nc-primary-element-hover': 'var(--color-primary-element-hover)', // Variant of --color-primary-element for hover effects
			// 'nc-primary-light': 'var(--color-primary-light)', // Light variant of --color-primary used for secondary actions
			// 'nc-primary-light-hover': 'var(--color-primary-light-hover)', // Variant of --color-primary-light for hover effects
			'nc-primary-element-light': 'var(--color-primary-element-light)', // Light variant of --color-primary-element used for secondary actions
			'nc-primary-element-light-hover': 'var(--color-primary-element-light-hover)', // Variant of --color-primary-element-light for hover effects

			// Background
			'nc-plain': 'var(--color-background-plain)', // The background color of the body element // Note: can be configured by the user

			// General
			'nc-main': 'var(--color-main-background)', // Main background color // Note: is always #FFFFFF in light mode and #171717 in dark mode
			'nc-hover': 'var(--color-background-hover)', // Background color for hover effects
			'nc-dark': 'var(--color-background-dark)', // Can be used e.g. to colorize selected table rows
			'nc-darker': 'var(--color-background-darker)', // Should only be used for elements, not as a text background! Otherwise it will not work for accessibility.
			'nc-loading-light': 'var(--color-loading-light)', // Color for the loading spinner (the bright part of it)
			'nc-loading-dark': 'var(--color-loading-dark)', // Color for the loading spinner (the dark part of it)

			// State
			'nc-error': 'var(--color-error)', // Color to show error state, this should not be used for text but for element backgrounds
			'nc-error-hover': 'var(--color-error-hover)', // Background color for hover effects of --color-error
			'nc-warning': 'var(--color-warning)', // Color to show warning state, this should not be used for text but for element backgrounds
			'nc-warning-hover': 'var(--color-warning-hover)', // Background color for hover effects of --color-warning
			'nc-success': 'var(--color-success)', // Color to show success state, this should not be used for text but for element backgrounds
			'nc-success-hover': 'var(--color-success-hover)', // Background color for hover effects of --color-success
			'nc-info': 'var(--color-info)', // Color to show info state, this should not be used for text but for element backgrounds
			'nc-info-hover': 'var(--color-info-hover)', // Background color for hover effects of --color-info
		}),
		borderColor: ({ theme }) => ({
			...theme('colors'),
			// General
			nc: 'var(--color-border)', // Default element border color
			'nc-dark': 'var(--color-border-dark)', // Dark variant of --color-border for dark themes
			'nc-maxcontrast': 'var(--color-border-maxcontrast)', // Darkest possible border color for accessibility

			// State
			'nc-error': 'var(--color-border-error)', // Border color for elements which have an error state like inputs with failing validity
			'nc-warning': 'var(--color-border-warning)',
			'nc-success': 'var(--color-border-success)', // Border color for elements which have a success state like inputs which have been saved
			'nc-info': 'var(--color-border-info)',
		}),
		borderRadius: {
			'nc-small': 'var(--border-radius-small)', // Border radius used for smaller elements
			'nc-element': 'var(--border-radius-element)', // Border radius of interactive elements such as buttons, input, navigation and list items.
			'nc-container': 'var(--border-radius-container)', // For smaller containers like action menus.
			'nc-container-large': 'var(--border-radius-container-large)', // For larger containers like body or modals.
			'nc-pill': 'var(--border-radius-pill)',
		},
		boxShadowColor: ({ theme }) => ({
			...theme('colors'),
			nc: 'var(--color-box-shadow)', // Color for box shadow effects
		}),
		fontSize: {
			nc: 'var(--default-font-size)', // Font size for normal text
			'nc-small': 'var(--font-size-small)',
		},
		lineHeight: {},
		outlineColor: ({ theme }) => theme('borderColor'),
		placeholderColor: ({ theme }) => ({
			...theme('colors'),
			'nc-light': 'var(--color-placeholder-light)', // Color for input placeholders
			'nc-dark': 'var(--color-placeholder-dark)', // Darker version of --color-placeholder-light
		}),
		ringColor: ({ theme }) => theme('borderColor'),
		ringOffsetColor: ({ theme }) => theme('borderColor'),
		spacing: {
			0: 'calc(0 * var(--default-grid-baseline))',
			0.5: 'calc(0.5 * var(--default-grid-baseline))',
			1: 'calc(1 * var(--default-grid-baseline))',
			1.5: 'calc(1.5 * var(--default-grid-baseline))',
			2: 'calc(2 * var(--default-grid-baseline))',
			2.5: 'calc(2.5 * var(--default-grid-baseline))',
			3: 'calc(3 * var(--default-grid-baseline))',
			3.5: 'calc(3.5 * var(--default-grid-baseline))',
			4: 'calc(4 * var(--default-grid-baseline))',
			5: 'calc(5 * var(--default-grid-baseline))',
			6: 'calc(6 * var(--default-grid-baseline))',
			7: 'calc(7 * var(--default-grid-baseline))',
			8: 'calc(8 * var(--default-grid-baseline))',
			9: 'calc(9 * var(--default-grid-baseline))',
			10: 'calc(10 * var(--default-grid-baseline))',
			11: 'calc(11 * var(--default-grid-baseline))',
			12: 'calc(12 * var(--default-grid-baseline))',
			14: 'calc(14 * var(--default-grid-baseline))',
			16: 'calc(16 * var(--default-grid-baseline))',
			20: 'calc(20 * var(--default-grid-baseline))',
			24: 'calc(24 * var(--default-grid-baseline))',
			28: 'calc(28 * var(--default-grid-baseline))',
			32: 'calc(32 * var(--default-grid-baseline))',
			36: 'calc(36 * var(--default-grid-baseline))',
			40: 'calc(40 * var(--default-grid-baseline))',
			44: 'calc(44 * var(--default-grid-baseline))',
			48: 'calc(48 * var(--default-grid-baseline))',
			52: 'calc(52 * var(--default-grid-baseline))',
			56: 'calc(56 * var(--default-grid-baseline))',
			60: 'calc(60 * var(--default-grid-baseline))',
			64: 'calc(64 * var(--default-grid-baseline))',
			72: 'calc(72 * var(--default-grid-baseline))',
			80: 'calc(80 * var(--default-grid-baseline))',
			96: 'calc(96 * var(--default-grid-baseline))',

			'nc-clickable-area': 'var(--default-clickable-area)', // Default size (width and height) for interactive elements like buttons
			'nc-clickable-area-large': 'var(--clickable-area-large)', // Larger size for the main UI elements
			'nc-clickable-area-small': 'var(--clickable-area-small)', // Smallest possible size of interactive elements, used by tertiary actions like filter chips
			'nc-grid-baseline': 'var(--default-grid-baseline)', // Foundation of all spacing sizes used on Nextcloud which are multiples of the baseline size
		},
		textColor: ({ theme }) => ({
			...theme('colors'),
			// Note: the values of the text color variables cannot be configured by the user directly,
			// because they are automatically set to high contrast colors relative to their background counterparts by Nextcloud itself.
			// For most of the text color variables, this means either #FFFFFF or #000000.

			// Primary
			// 'nc-primary': 'var(--color-primary-text)', // Text color to be used on --color-primary
			'nc-primary-element': 'var(--color-primary-element-text)', // Text color to be used on --color-primary-element
			// 'nc-primary-light': 'var(--color-primary-light-text)', // Text color to be used on --color-primary-light
			'nc-primary-element-light': 'var(--color-primary-element-light-text)', // Text color to be used on --color-primary-element-light

			// Background
			'nc-plain': 'var(--color-background-plain-text)', // Text color to be used directly on the background (e.g. header menu)

			// General
			'nc-main': 'var(--color-main-text)', // Main text color // Note: is always #222222 in light mode and #EBEBEB in dark mode
			'nc-maxcontrast': 'var(--color-text-maxcontrast)', // Brighter text color that still fulfills accessibility requirements

			// State
			'nc-element-error': 'var(--color-element-error)', // Color with proper contrast for elements which have an error state for example icons
			'nc-element-warning': 'var(--color-element-warning)', // Color with proper contrast for elements which have an warning state for example icons
			'nc-element-success': 'var(--color-element-success)', // Color with proper contrast for elements which have an success state for example icons
			'nc-element-info': 'var(--color-element-info)', // Color with proper contrast for elements which have an info state for example icons
			'nc-error': 'var(--color-error-text)', // Text color on elements using --color-error as background
			'nc-warning': 'var(--color-warning-text)', // Text color on elements using --color-warning as background
			'nc-success': 'var(--color-success-text)', // Text color on elements using --color-success as background
			'nc-info': 'var(--color-info-text)', // Text color on elements using --color-info as background
		}),
		transitionDuration: {
			'nc-quick': 'var(--animation-quick)', // Animation time for snappy CSS transitions
			'nc-slow': 'var(--animation-slow)', // Animation time for more complex transitions
		},
		extend: {
			borderWidth: {
				'nc-input': 'var(--border-width-input)', // Border width for interactive elements such as text fields and selects
				'nc-input-focused': 'var(--border-width-input-focused)', // Border width for interactive elements when focussed (adjusted for accessibility)
			},
		},
	},
}
