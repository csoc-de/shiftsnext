import axios from '@nextcloud/axios'
import { t } from '@nextcloud/l10n'
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

let reloadPromptShown = false
let reloadingAfterSwUpdate = false

/**
 * Prompts the user to reload if an update is available.
 *
 * @param registration The active service worker registration
 */
function promptForAppReload(registration: ServiceWorkerRegistration): void {
	if (reloadPromptShown) {
		return
	}
	reloadPromptShown = true
	const shouldReload = globalThis.confirm(t(APP_ID, 'A new version is available. Reload now?'))
	if (!shouldReload) {
		return
	}
	if (registration.waiting) {
		registration.waiting.postMessage({ type: 'SKIP_WAITING' })
		return
	}
	globalThis.location.reload()
}

/**
 * Sets up service worker update detection.
 *
 * @param registration The active service worker registration
 */
function setupServiceWorkerUpdates(registration: ServiceWorkerRegistration): void {
	if (registration.waiting) {
		promptForAppReload(registration)
	}

	registration.addEventListener('updatefound', () => {
		const installing = registration.installing
		if (!installing) {
			return
		}
		installing.addEventListener('statechange', () => {
			if (
				installing.state === 'installed'
				&& navigator.serviceWorker.controller
			) {
				promptForAppReload(registration)
			}
		})
	})

	navigator.serviceWorker.addEventListener('controllerchange', () => {
		if (reloadingAfterSwUpdate) {
			return
		}
		reloadingAfterSwUpdate = true
		globalThis.location.reload()
	})

	globalThis.setInterval(() => {
		registration.update().catch(() => undefined)
	}, 60_000)
}

if (
	import.meta.env.PROD
		&& 'serviceWorker' in navigator
		&& globalThis.isSecureContext
) {
	const swUrl = linkTo(APP_ID, 'service-worker.js')
	navigator.serviceWorker
		.register(swUrl)
		.then((registration) => {
			setupServiceWorkerUpdates(registration)
		})
		.catch(() => undefined)
}
