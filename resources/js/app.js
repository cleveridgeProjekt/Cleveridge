import './bootstrap'
import { createApp } from 'vue'
import router from "../router/index.js"
import App from './components/App.vue'
import axios from 'axios'
import { fetchUser } from './composables/useUser'
import { initSanctum } from './bootstrap'
import '@fortawesome/fontawesome-free/css/all.min.css';
import '../css/app.css';


;(async () => {
    await initSanctum()
    await fetchUser()
    createApp(App).use(router).mount('#app')
})()

axios.defaults.withCredentials = true

fetchUser().finally(() => {
    createApp(App)
        .use(router)
        .mount('#app')
})
