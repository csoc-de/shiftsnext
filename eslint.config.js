import { recommended } from '@nextcloud/eslint-config'

export default [
	...recommended,
	{
		ignores: [
			'js/**',
			'l10n/**',
		],
	},
]
