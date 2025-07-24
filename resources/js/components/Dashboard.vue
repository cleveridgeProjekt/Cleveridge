<template>
    <div>
        <PageHeader title="Dashboard" icon="fal fa-th-large">
            Übersicht über deine wichtigsten Cleveridge-Funktionen.
            Klicke auf eine Kachel, um direkt zum gewünschten Bereich zu gelangen.
        </PageHeader>
        <div class="dashboard-tiles grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8 mt-6">
            <router-link v-for="tile in tiles" :key="tile.label" :to="tile.path" class="dashboard-tile rounded-xl bg-white/90 p-6 shadow-lg hover:shadow-xl transition flex flex-col items-center justify-center hover:bg-blue-50 group">
                <img :src="tile.img" alt="" class="w-24 h-24 mb-4 rounded-full object-cover border-2 border-blue-200 group-hover:border-blue-400 shadow"/>
                <div class="text-lg font-semibold mb-2 text-blue-900 tracking-tight">{{ tile.label }}</div>
                <span class="text-blue-700 text-sm group-hover:underline">Mehr</span>
            </router-link>
        </div>
    </div>
</template>

<script>
import PageHeader from './layout/PageHeader.vue';
export default {
    name: 'Dashboard',
    components: { PageHeader },
    data() {
        return {
            tiles: [
                { label: "What's in your fridge", path: "/fridge", img: "/media/fridge.jpg" },
                { label: "Produkte", path: "/products", img: "/media/produkte.jpg" },
                { label: "Einkaufsliste", path: "/shopping-list", img: "/media/einkauf.jpg" },
                { label: "Cleveridge Status", path: "/status", img: "/media/status.jpg" },
                { label: "Ablaufwarnungen", path: "/expiry", img: "/media/warning.jpg" },
                { label: "Barcode Scannen!", path: "/barcode", img: "/media/barcode.jpg" },
            ]
        }
    }
}
</script>

<style scoped>
.dashboard-tiles {
    margin: 0 auto;
}

.dashboard-tile {
    min-height: 260px;
    transition: box-shadow 0.2s, background 0.2s;
    box-shadow: 0 2px 14px 0 rgba(140,180,230,0.08);
    background: rgba(255,255,255,0.95);
}
.dashboard-tile:hover {
    background: #e6f2fd;
    box-shadow: 0 6px 22px 0 rgba(80,130,180,0.18);
    cursor: pointer;
}
</style>
