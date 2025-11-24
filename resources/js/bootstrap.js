import axios from 'axios';

axios.defaults.baseURL = 'http://cleveridge';
axios.defaults.withCredentials = true;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

export async function initSanctum() {
    try { await axios.get('/sanctum/csrf-cookie'); } catch (e) { console.error(e); }
}

window.axios = axios;
