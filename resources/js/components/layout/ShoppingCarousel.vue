<template>
    <div class="carousel-grid">
        <button class="carousel-arrow" @click="$emit('prev')" aria-label="Zurück">
            <i class="fas fa-chevron-left"></i>
        </button>
        <div class="carousel-track">
            <div v-for="(prod, i) in visibleCarousel"
                 :key="prod.id"
                 :class="['carousel-item', { active: i === 1 }]"
                 @click="$emit('detail', prod)">
                <img :src="prod.image_url || '/media/products/default.png'" :alt="prod.name" />
                <div class="prod-name">{{ prod.name }}</div>
            </div>
        </div>
        <button class="carousel-arrow" @click="$emit('next')" aria-label="Vor">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
</template>

<script setup>
defineProps({
    visibleCarousel: Array
});
</script>

<style scoped>
.carousel-grid {
    display: grid;
    grid-template-columns: 48px 1fr 48px;
    align-items: center;
    gap: 0 12px;
    margin: 20px auto 38px auto;
    max-width: 730px;
}
.carousel-track {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 22px;
    align-items: center;
    justify-items: center;
}
.carousel-item {
    width: 90px;
    height: 120px;
    background: #f4fbff;
    border-radius: 15px;
    box-shadow: 0 2px 12px 0 #d9e9fa33;
    display: grid;
    align-content: center;
    justify-items: center;
    opacity: 0.7;
    cursor: pointer;
    border: 2px solid transparent;
    transition: transform .24s cubic-bezier(.44,.82,.34,.98), opacity .2s;
}
.carousel-item img {
    width: 62px; height: 62px;
    object-fit: contain;
    margin-bottom: 7px;
}
.carousel-item.active {
    transform: scale(1.25);
    background: #fff;
    opacity: 1;
    z-index: 2;
    border-color: #4ac0fa;
}
.carousel-arrow {
    background: none;
    border: none;
    font-size: 32px;
    color: #188be5;
    cursor: pointer;
    padding: 8px;
    transition: color .16s;
}
.carousel-arrow:hover {
    color: #00477d;
}
.prod-name {
    font-size: 0.97em;
    color: #25548a;
    font-weight: 600;
    text-align: center;
}
</style>
