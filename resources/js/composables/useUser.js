import { ref, computed } from 'vue'
import axios from 'axios'

export const user = ref(null)
export const loadingUser = ref(false)
let inflight = null

export async function fetchUser(force = false) {
    if (inflight && !force) return inflight

    loadingUser.value = true
    inflight = (async () => {
        try {
            const { data } = await axios.get('/api/user')
            user.value = data
        } catch (e) {
            if (e?.response?.status === 401) user.value = null
            else console.error('fetchUser failed:', e)
        } finally {
            loadingUser.value = false
            inflight = null
        }
    })()

    return inflight
}

export const isAuthenticated = computed(() => !!user.value)

export async function logout() {
    try { await axios.post('/logout') } catch {}
    user.value = null
}
