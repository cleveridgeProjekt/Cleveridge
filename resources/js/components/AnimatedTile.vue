<template>
    <router-link
        :to="tile.path"
        class="tile-card group"
        :style="{ animationDelay: delay + 's' }"
    >
        <img :src="tile.img" alt="" class="tile-img" />
        <div class="tile-label">{{ tile.label }}</div>
        <div class="tile-desc">{{ tile.desc }}</div>
    </router-link>
</template>

<script setup>
defineProps({
    tile: Object,
    delay: Number,
})
</script>

<style scoped>
.tile-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 210px;
    min-height: 310px;
    background: #fff;
    border-radius: 30px;
    box-shadow: 0 8px 28px 0 rgba(140, 180, 230, 0.11);
    padding: 28px 16px 18px 16px;
    transition: box-shadow 0.15s, transform 0.14s;
    opacity: 0;
    transform: translateX(-130px);
    animation: slidein 0.45s cubic-bezier(.36, .79, .43, 1.11) forwards;
}

@keyframes slidein {
    to {
        opacity: 1;
        transform: none;
    }
}

.tile-card:hover {
    box-shadow: 0 16px 40px 0 rgba(60, 120, 180, 0.16);
    transform: translateY(-3px) scale(1.035);
    background: #e9f8fd;
}

.tile-img {
    width: 90px;
    height: 90px;
    border-radius: 24px;
    margin-bottom: 17px;
    object-fit: cover;
    background: #f7fafb;
    border: none;
    box-shadow: 0 2px 12px 0 #c8e8fa3a;
}

.tile-label {
    font-weight: 800;
    font-size: 1.07rem;
    letter-spacing: 0;
    text-align: center;
    margin-bottom: 7px;
    color: #17263a;
}

.tile-desc {
    color: #333d4d;
    font-size: 0.93rem;
    text-align: center;
    line-height: 1.3;
    opacity: 0.81;
    margin-top: 2px;
}
</style>
