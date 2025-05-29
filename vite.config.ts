import { createAppConfig } from '@nextcloud/vite-config'

export default createAppConfig({
	mainApp: 'src/mainApp.ts',
	mainAdminSettings: 'src/mainAdminSettings.ts',
}, {
	inlineCSS: false,
})
