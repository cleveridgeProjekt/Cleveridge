<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import PrivateLayout from './layout/Layout.vue'
import PublicLayout from './layout/PublicLayout.vue'

const route = useRoute()

const isAuthPage = computed(() => ['/login', '/register'].includes(route.path))
</script>

<template>
    <PublicLayout v-if="isAuthPage">
        <router-view />
    </PublicLayout>
    <PrivateLayout v-else>
        <router-view />
    </PrivateLayout>
</template>
