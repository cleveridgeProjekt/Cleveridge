import axios from 'axios';

axios.defaults.baseURL = 'https://cleveridge.onrender.com';
axios.defaults.withCredentials = true;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

export async function initSanctum() {
    try { await axios.get('/sanctum/csrf-cookie'); } catch (e) { console.error(e); }
}

window.axios = axios;
