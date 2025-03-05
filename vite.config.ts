import { createAppConfig } from '@nextcloud/vite-config'
import path from 'path'

export default createAppConfig({
	mainApp: path.join(__dirname, 'src', 'mainApp.ts'),
	mainAdminSettings: path.join(__dirname, 'src', 'mainAdminSettings.ts'),
}, {
	inlineCSS: false,
})
