import { ref } from 'vue'
import axios from 'axios'

export const user = ref(null)

export async function fetchUser() {
    try {
        const { data } = await axios.get('/api/user')
        user.value = data
    } catch {
        user.value = null
    }
}
