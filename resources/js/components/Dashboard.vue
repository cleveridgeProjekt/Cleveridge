<template>
    <div>
        <PageHeader :title="t('dashboard.title')" icon="fal fa-th-large">
            <p v-html="t('dashboard.intro_html')"></p>
        </PageHeader>
        <AnimatedTileGrid :tiles="tiles" />
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import PageHeader from './layout/PageHeader.vue'
import AnimatedTileGrid from './AnimatedTileGrid.vue'

const { t } = useI18n()

const tiles = computed(() => ([
    {
        label: t('dashboard.tiles.fridge.label'),
        path: '/fridge',
        img: '/media/tiles/fridge_shelves.webp',
        desc: t('dashboard.tiles.fridge.desc'),
    },
    {
        label: t('dashboard.tiles.shopping_list.label'),
        path: '/shopping-list',
        img: '/media/tiles/shopping_list.webp',
        desc: t('dashboard.tiles.shopping_list.desc'),
    },
    {
        label: t('dashboard.tiles.status.label'),
        path: '/status',
        img: '/media/tiles/fridge_status.webp',
        desc: t('dashboard.tiles.status.desc'),
    },
    {
        label: t('dashboard.tiles.product_recognition.label'),
        path: '/product-recognition',
        img: '/media/tiles/product_recognition.webp',
        desc: t('dashboard.tiles.product_recognition.desc'),
    },
    {
        label: t('dashboard.tiles.expiry.label'),
        path: '/expiry',
        img: '/media/tiles/expiry_warning.webp',
        desc: t('dashboard.tiles.expiry.desc'),
    },
    {
        label: t('dashboard.tiles.recipes.label'),
        path: '/receipts',
        img: '/media/tiles/blueberry_receipt.png',
        desc: t('dashboard.tiles.recipes.desc'),
    },
]))
</script>
