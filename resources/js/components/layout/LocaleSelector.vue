<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useI18n } from 'vue-i18n'

const { locale } = useI18n()
const open = ref(false)

const options = [
    { key: 'de', label: 'DE', flag: '/media/topbar/de-flag.svg' },
    { key: 'en', label: 'EN', flag: '/media/topbar/en-flag.svg' },
    { key: 'es',  label: 'ES', flag: '/media/topbar/es-flag.svg' },
    { key: 'ukr', label: 'UA', flag: '/media/topbar/ukr-flag.svg' }
]

const current = computed(() => options.find(o => o.key === locale.value) ?? options[0])

function toggle() { open.value = !open.value }
function close() { open.value = false }

function pick(code) {
    locale.value = code
    localStorage.setItem('locale', code)
    close()
}

// close on outside click
function onDocClick(e) {
    if (!e.target.closest('.locale-selector')) close()
}
onMounted(() => document.addEventListener('click', onDocClick))
onBeforeUnmount(() => document.removeEventListener('click', onDocClick))
</script>

<template>
    <div class="locale-selector">
        <button class="current" type="button" @click.stop="toggle" aria-haspopup="listbox" :aria-expanded="open">
            <span class="code">{{ current?.label }}</span>
            <img class="flag" :src="current?.flag" :alt="current?.label + ' flag'">
        </button>

        <ul v-if="open" class="menu" role="listbox">
            <li v-for="o in options" :key="o.key" v-show="o.key !== current?.key">
                <button type="button" class="item" role="option" @click.stop="pick(o.key)">
                    <span class="code">{{ o.label }}</span>
                    <img class="flag" :src="o.flag" :alt="o.label + ' flag'">
                </button>
            </li>
        </ul>
    </div>
</template>

<style scoped>
.locale-selector {
    position: relative;
    height: 100%;
    display: flex;
    align-items: center;
}
.current {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 8px 12px;
    border: 1px solid #a7c8e6;
    background: #ffffff;
    border-radius: 6px;
    cursor: pointer;
    line-height: 1;
}
.current:hover { background: #f4f8fd; }
.code { font-size: 14px; color: #666; font-weight: 600; letter-spacing: .02em; }
.flag { width: 20px; height: 20px; object-fit: contain; }

.menu {
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    min-width: 120px;
    background: #fff;
    border: 1px solid #a7c8e6;
    border-radius: 8px;
    box-shadow: 0 10px 28px rgba(0,0,0,.12);
    padding: 6px 0;
    z-index: 40;
}
.item {
    width: 100%;
    display: flex;
    justify-content: space-between;
    gap: 10px;
    align-items: center;
    padding: 10px 12px;
    background: transparent;
    border: 0;
    cursor: pointer;
}
.item:hover { background: #e9f3ff; }
</style>
