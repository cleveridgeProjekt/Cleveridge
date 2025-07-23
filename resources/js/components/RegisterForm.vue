<template>
    <div class="register-box">
        <h2 style="text-align:center;font-size:28px;color:#0077d9;font-weight:700;margin-bottom:24px">Registrieren</h2>
        <form @submit.prevent="submit">
            <input v-model="name" placeholder="Vorname" required />
            <input v-model="surname" placeholder="Nachname" required />
            <input v-model="username" placeholder="Benutzername" required />
            <input v-model="email" type="email" placeholder="Email" required />
            <input v-model="password" type="password" placeholder="Passwort" required />
            <input v-model="password_confirmation" type="password" placeholder="Passwort wiederholen" required />
            <button type="submit">Registrieren</button>
            <div class="switch-link">
                Schon ein Account? <router-link to="/login">Login</router-link>
            </div>
        </form>
        <div v-if="error" class="error">{{ error }}</div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
const name = ref(''), surname = ref(''), username = ref(''), email = ref(''), password = ref(''), password_confirmation = ref(''), error = ref('')
const router = useRouter()
async function submit() {
    error.value = ''
    try {
        await axios.post('/register', {
            name: name.value,
            surname: surname.value,
            username: username.value,
            email: email.value,
            password: password.value,
            password_confirmation: password_confirmation.value
        })
        router.push('/') // Go to dashboard after registration
    } catch (e) {
        error.value = e?.response?.data?.message || 'Registrierung fehlgeschlagen'
    }
}
</script>
<style scoped>
.register-box {
    max-width: 380px;
    margin: 80px auto;
    background: #fff;
    border-radius: 16px;
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
    background: #0077d9;
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
