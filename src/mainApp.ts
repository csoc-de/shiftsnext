import { createPinia } from 'pinia'
import { createApp } from 'vue'
import router from './router'

import App from './App.vue'
import '../css/style.scss'

import 'floating-vue/dist/style.css'

import { initializeTailwindTheme } from './theme'

import { localTimeZone } from './date'

// We are just importing `localTimeZone` here to definitively initialize it, so
// every component which uses it, will have the runtime's time zone from the
// most recent page reload
// eslint-disable-next-line no-unused-expressions
localTimeZone

initializeTailwindTheme()

const app = createApp(App)

app.use(router)
app.use(createPinia())

app.mount('#content')
