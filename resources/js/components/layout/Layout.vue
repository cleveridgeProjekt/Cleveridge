<template>
    <div style="display: flex; min-height: 100vh;">
        <Sidebar />
        <div style="flex:1; display: flex; flex-direction: column;">
            <header class="layout-header">
                <div class="header-grid">
                    <span class="header-title">Cleveridge</span>
                    <div class="user-dropdown" @click="toggleDropdown">
                        <i class="fal fa-user"></i>
                        <span>{{ fullName }}</span>
                        <i class="fal fa-angle-down"></i>
                        <div v-if="dropdown" class="dropdown-menu">
                            <div class="dropdown-info">
                                <div><strong>{{ user?.name }} {{ user?.surname }}</strong></div>
                                <div>{{ user?.email }}</div>
                            </div>
                            <a @click="logout" class="dropdown-item">
                                <i class="fal fa-sign-out"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </header>
            <main style="flex:1; padding: 40px 50px;">
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import Sidebar from './Sidebar.vue'
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { user, fetchUser } from '../../composables/useUser'

const router = useRouter()
const dropdown = ref(false)
function toggleDropdown() { dropdown.value = !dropdown.value }
function logout() {
    axios.post('/logout').then(() => router.push('/login'))
}
const fullName = computed(() => user.value ? `${user.value.name} ${user.value.surname}` : 'Benutzer')

onMounted(() => {
    fetchUser()
})
</script>
<style scoped>
.layout-header {
    height: 64px;
    display: flex;
    align-items: center;
    background: #f8fbff;
    border-bottom: 1px solid #e3eefa;
    padding: 0 30px 0 0;
}
.header-grid {
    display: flex;
    align-items: center;
    width: 100%;
}
.header-title {
    font-weight: bold;
    font-size: 1.3rem;
    color: #0067b8;
    margin-right: 18px;
}
.user-dropdown {
    margin-left: auto;
    position: relative;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 9px;
}
.dropdown-menu {
    position: absolute;
    top: 115%;
    right: 0;
    min-width: 210px;
    background: #fff;
    color: #222;
    border-radius: 12px;
    box-shadow: 0 10px 32px 0 rgba(0,0,0,.16);
    padding: 12px 0;
    z-index: 30;
}
.dropdown-info {
    padding: 8px 18px 8px 22px;
    border-bottom: 1px solid #e5e5e5;
    margin-bottom: 8px;
    color: #1163a7;
}
.dropdown-item {
    display: flex; gap: 12px; align-items: center;
    color: #333;
    padding: 12px 20px; text-decoration: none;
    font-size: 15px; font-weight: 500;
    cursor: pointer;
}
.dropdown-item:hover { background: #f0f7fa; color: #0077d9; }
</style>
