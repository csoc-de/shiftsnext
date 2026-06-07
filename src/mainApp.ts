import axios from '@nextcloud/axios'
import { linkTo } from '@nextcloud/router'
import { createPinia } from 'pinia'
import { createApp } from 'vue'
import App from './App.vue'
import router from './router/index.ts'
import { APP_ID, APP_VERSION } from './utils/appId.ts'
import { initializeTailwindTheme } from './utils/theme.ts'

import './utils/date.ts' // Import for side effects: initializes userTimeZone
import '../css/style.scss'

initializeTailwindTheme()

axios.defaults.headers[`X-${APP_ID}-appVersion`] = APP_VERSION

const app = createApp(App)

app.config.idPrefix = APP_ID

app.use(router)
app.use(createPinia())

app.mount('#content')

if (
	import.meta.env.PROD
		&& 'serviceWorker' in navigator
		&& globalThis.isSecureContext
) {
	const swUrl = linkTo(APP_ID, 'service-worker.js')
	navigator.serviceWorker.register(swUrl).catch(() => undefined)
}
