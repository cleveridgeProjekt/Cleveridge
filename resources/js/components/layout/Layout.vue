<template>
    <div class="app-shell">
        <header class="main-header">
            <span class="logo-box">Cleveridge</span>

            <div class="locale-box">
                <LocaleSelector/>
            </div>

            <div class="user-box" @mouseenter="hovered = true" @mouseleave="hovered = false" @click="toggleDropdown" :class="{ hovered }">
                <i class="fas fa-user mr-2"></i>
                <span class="user-name">{{ fullName }}</span>
                <div v-if="dropdown" class="dropdown-menu">
                    <div class="dropdown-info">
                        <div><strong>{{ user?.name }} {{ user?.surname }}</strong></div>
                        <div>{{ user?.email }}</div>
                    </div>
                    <a @click="logout" class="dropdown-item">
                        <i class="fas fa-sign-out"></i> {{ t('topbar.logout') }}
                    </a>
                </div>
            </div>
        </header>

        <div style="display: flex; min-height: calc(100vh - 56px);">
            <Sidebar/>
            <main style="flex:1; padding: 40px 50px;">
                <slot/>
            </main>
        </div>
    </div>
</template>

<script setup>
import Sidebar from './Sidebar.vue'
import LocaleSelector from './LocaleSelector.vue'
import {ref, computed} from 'vue'
import {useRouter} from 'vue-router'
import {useI18n} from 'vue-i18n'
import {user} from '../../composables/useUser'

const {t} = useI18n()
const router = useRouter()
const dropdown = ref(false)
const hovered = ref(false)

function toggleDropdown() {
    dropdown.value = !dropdown.value
}

async function logout() {
    try {
        await axios.post('/logout')
    } catch (e) {
    }
    user.value = null
    router.push('/login')
}

const fullName = computed(() => user.value ? `${user.value.name} ${user.value.surname}` : 'Benutzer')
</script>

<style scoped>
.app-shell {
    min-height: 100vh;
    background: #f8fbff;
}

.main-header {
    position: sticky;
    top: 0;
    z-index: 20;
    width: 100%;
    height: 72px;
    display: flex;
    align-items: center;
    background: #94cfff;
}

.logo-box {
    display: flex;
    align-items: center;
    height: 100%;
    color: #0c5288;
    font-weight: bold;
    font-size: 20px;
    padding: 0 34px 0 36px;
    margin-right: 30px;
    box-sizing: border-box;
}

.locale-box {
    margin-left: auto;
    padding-right: 18px;
    display: flex;
    align-items: center;
}

.user-box {
    display: flex;
    align-items: center;
    height: 100%;
    background: #0c5288;
    color: #fff;
    padding: 0 40px;
    font-weight: bold;
    font-size: 22px;
    cursor: pointer;
    position: relative;
    transition: background .2s;
    box-sizing: border-box;
}

.user-box.hovered, .user-box:hover {
    background: #005fa3;
    transition: background .2s;
}

.user-name {
    font-size: 20px;
    font-weight: bold;
}

.fal.fa-angle-down {
    font-size: 20px;
    margin-top: 2px;
}

.dropdown-menu {
    position: absolute;
    top: 110%;
    right: 0;
    min-width: 220px;
    background: #fff;
    color: #222;
    border-radius: 14px;
    box-shadow: 0 10px 32px rgba(0, 0, 0, .13);
    padding: 14px 0 10px;
    z-index: 30;
}

.dropdown-info {
    padding: 8px 20px 8px 26px;
    border-bottom: 1px solid #e5e5e5;
    margin-bottom: 8px;
    color: #1163a7;
    font-size: 17px;
}

.dropdown-item {
    display: flex;
    gap: 12px;
    align-items: center;
    color: #333;
    padding: 13px 22px;
    text-decoration: none;
    font-size: 16px;
    font-weight: 500;
    cursor: pointer;
    border-radius: 8px;
}

.dropdown-item:hover {
    background: #f0f7fa;
    color: #0077d9;
}
</style>
