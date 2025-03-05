import { createApp } from 'vue'

import AdminSettingsView from './views/AdminSettingsView.vue'
import '../css/style.scss'

import { initializeTailwindTheme } from './theme'

import { localTimeZone } from './date'

// We are just importing `localTimeZone` here to definitively initialize it, so
// every component which uses it, will have the runtime's time zone from the
// most recent page reload
// eslint-disable-next-line no-unused-expressions
localTimeZone

initializeTailwindTheme()

const app = createApp(AdminSettingsView)

app.mount('#admin-settings')
