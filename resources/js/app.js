import './bootstrap'
import { createApp } from 'vue'
import router from '../router'
import App from './components/App.vue'
import { initSanctum } from './bootstrap'
import { fetchUser } from './composables/useUser'
import '@fortawesome/fontawesome-free/css/all.min.css'
import '../css/app.css'

    ;(async () => {
    try { await initSanctum() } catch {}
    try { await fetchUser() } catch {}

    const app = createApp(App)
    app.use(router)
    app.mount('#app')
})()
