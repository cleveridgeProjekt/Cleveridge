<template>
    <div class="login-box">
        <h2 style="text-align:center;font-size:28px;color:#0c5288;font-weight:700;margin-bottom:24px">Login</h2>
        <form @submit.prevent="submitLogin">
            <input v-model="email" type="email" placeholder="Email" required />
            <input v-model="password" type="password" placeholder="Passwort" required />
            <button type="submit">Login</button>
            <div class="switch-link">
                Noch kein Account? <router-link to="/register">Registrieren</router-link>
            </div>
        </form>
        <div v-if="error" class="error">{{ error }}</div>
    </div>
</template>
<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
const router = useRouter()
const email = ref('')
const password = ref('')
const error = ref('')

async function submitLogin() {
    error.value = ''
    try {
        await axios.post('/login', { email: email.value, password: password.value })
        router.push('/') // Go to dashboard
    } catch (e) {
        error.value = e?.response?.data?.message || 'Login fehlgeschlagen'
    }
}
</script>
<style scoped>
.login-box {
    max-width: 380px;
    margin: 80px auto;
    background: #fff;
    border-radius: 5px;
    padding: 36px 32px;
    box-shadow: 0 8px 32px 0 rgba(0,0,0,0.08);
}
input {
    display: block;
    width: 100%;
    margin-bottom: 18px;
    padding: 11px 12px;
    border-radius: 8px;
    border: 1px solid #d3dbe7;
    background: #f6f9fc;
    font-size: 16px;
}
button {
    width: 100%;
    padding: 10px 0;
    border: none;
    background: #0c5288;
    color: #fff;
    border-radius: 8px;
    font-weight: 600;
    font-size: 17px;
    margin-top: 10px;
    cursor: pointer;
    transition: background .18s;
}
button:hover { background: #005fa3; }
.error { color: #b90000; font-size: 15px; margin-top: 12px;}
.switch-link { text-align: right; font-size: 14px; margin-top: 12px;}
</style>
