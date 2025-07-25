<template>
    <div class="dashboard-tiles">
        <AnimatedTile
            v-for="(tile, idx) in tiles"
            :key="tile.label"
            :tile="tile"
            :delay="idx * 0.10"
        />
    </div>
</template>

<script setup>
import AnimatedTile from './AnimatedTile.vue'
defineProps({ tiles: Array })
</script>

<style scoped>
.dashboard-tiles {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 34px 36px;
    margin: 36px 0 0 0;
    justify-items: center;
}
</style>
