<template>
    <div>
        <h1 class="text-3xl font-bold mb-8">Dashboard</h1>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8">
            <router-link
                v-for="tile in tiles"
                :key="tile.label"
                :to="tile.path"
                class="rounded-xl bg-white p-6 shadow hover:shadow-lg transition flex flex-col items-center justify-center hover:bg-blue-50 group"
            >
                <img :src="tile.img" alt="" class="w-24 h-24 mb-4 rounded-full object-cover border-2 border-blue-200 group-hover:border-blue-400" />
                <div class="text-lg font-semibold mb-2 text-blue-900">{{ tile.label }}</div>
                <span class="text-blue-700 text-sm group-hover:underline">Mehr</span>
            </router-link>
        </div>
    </div>
</template>

<script setup>
const tiles = [
    { label: "What's in your fridge", path: "/fridge", img: "/media/fridge.jpg" },
    { label: "Produkte", path: "/products", img: "/media/produkte.jpg" },
    { label: "Einkaufsliste", path: "/shopping-list", img: "/media/einkauf.jpg" },
    { label: "Cleveridge Status", path: "/status", img: "/media/status.jpg" },
    { label: "Ablaufwarnungen", path: "/expiry", img: "/media/warning.jpg" },
    { label: "Barcode Scannen!", path: "/barcode", img: "/media/barcode.jpg" },
];
</script>
