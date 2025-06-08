import { createApp } from 'vue'
import AdminSettingsView from './views/AdminSettingsView.vue'
import { APP_ID } from './appId.ts'
import { initializeTailwindTheme } from './theme.ts'

import './date.ts' // Import for side effects: initializes localTimeZone
import '../css/style.scss'

initializeTailwindTheme()

const app = createApp(AdminSettingsView)

app.config.idPrefix = APP_ID

app.mount('#admin-settings')
