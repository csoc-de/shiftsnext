import axios from '@nextcloud/axios'
import { createApp } from 'vue'
import AdminSettingsView from './views/AdminSettingsView.vue'
import { APP_ID, APP_VERSION } from './utils/appId.ts'
import { initializeTailwindTheme } from './utils/theme.ts'

import './utils/date.ts' // Import for side effects: initializes userTimeZone
import '../css/style.scss'

initializeTailwindTheme()

axios.defaults.headers[`X-${APP_ID}-appVersion`] = APP_VERSION

const app = createApp(AdminSettingsView)

app.config.idPrefix = APP_ID

app.mount('#admin-settings')
