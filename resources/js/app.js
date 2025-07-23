import './bootstrap'
import { createApp } from 'vue'
import router from "../router/index.js"
import App from './components/App.vue'
import axios from 'axios'
import { fetchUser } from './composables/useUser'

axios.defaults.withCredentials = true

fetchUser().finally(() => {
    createApp(App)
        .use(router)
        .mount('#app')
})
