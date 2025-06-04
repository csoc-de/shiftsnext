import { createPinia } from 'pinia'
import { createApp } from 'vue'
import App from './App.vue'
import router from './router/index.ts'
import { initializeTailwindTheme } from './theme.ts'

import './date.ts' // Import for side effects: initializes localTimeZone
import '../css/style.scss'
import 'floating-vue/dist/style.css'

initializeTailwindTheme()

const app = createApp(App)

app.use(router)
app.use(createPinia())

app.mount('#content')
