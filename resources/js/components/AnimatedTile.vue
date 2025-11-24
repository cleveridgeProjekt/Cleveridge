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
    width: 420px;
    min-height: 620px;
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 14px 46px 0 rgba(140,180,230,0.13);
    padding: 56px 32px 34px 32px;
    transition: box-shadow 0.15s, transform 0.14s;
    opacity: 0;
    transform: translateX(-180px);
    animation: slidein 0.48s cubic-bezier(.36,.79,.43,1.11) forwards;
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
    width: 280px;
    height: 280px;
    border-radius: 8px;
    margin-bottom: 34px;
    object-fit: cover;
    background: #f7fafb;
    box-shadow: 0 3px 22px 0 #c8e8fa3a;
}

.tile-label {
    font-weight: 900;
    font-size: 1.9rem;
    letter-spacing: 0;
    text-align: center;
    margin-bottom: 17px;
    color: #17263a;
}

.tile-desc {
    color: #333d4d;
    font-size: 1.5rem;
    text-align: center;
    line-height: 1.33;
    opacity: 0.85;
    margin-top: 8px;
}
</style>
