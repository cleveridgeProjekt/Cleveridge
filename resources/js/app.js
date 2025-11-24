import './bootstrap'
import { createApp } from 'vue'
import router from '../router'
import App from './components/App.vue'
import { initSanctum } from './bootstrap'
import { fetchUser } from './composables/useUser'
import '@fortawesome/fontawesome-free/css/all.min.css'
import '../css/app.css'
import i18n from './i18n'

    ;(async () => {
    try { await initSanctum() } catch {}
    try { await fetchUser() } catch {}

    const app = createApp(App)
    app.use(router)
    app.use(i18n)
    app.mount('#app')
})()
