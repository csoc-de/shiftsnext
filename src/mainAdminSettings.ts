import { createApp } from 'vue'
import AdminSettingsView from './views/AdminSettingsView.vue'
import { APP_ID } from './utils/appId.ts'
import { initializeTailwindTheme } from './utils/theme.ts'

import './utils/date.ts' // Import for side effects: initializes localTimeZone
import '../css/style.scss'

initializeTailwindTheme()

const app = createApp(AdminSettingsView)

app.config.idPrefix = APP_ID

app.mount('#admin-settings')
