<template>
    <div class="carousel-grid">
        <button class="carousel-arrow" @click="$emit('prev')" aria-label="Zurück">
            <i class="fas fa-chevron-left"></i>
        </button>
        <div class="carousel-track">
            <div v-for="(prod, i) in visibleCarousel" :key="prod.id" :class="[
          'carousel-item',
          i === 2 ? 'center' : '',
          (i === 1 || i === 3) ? 'side' : '',
          (i === 0 || i === 4) ? 'outer' : ''
          ]"
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
    grid-template-columns: 32px 1fr 32px;
    align-items: center;
    gap: 0 10px;
    margin: 68px auto 68px auto;
    max-width: 1300px;
}

.carousel-track {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 27px;
    align-items: center;
    justify-items: center;
    min-width: 950px;
}

.carousel-item {
    width: 144px;
    height: 202px;
    background: #f8fbff;
    border-radius: 24px;
    box-shadow: 0 3px 14px 0 #b9e8fa18;
    display: grid;
    align-content: center;
    justify-items: center;
    opacity: 0.40;
    cursor: pointer;
    border: 2.7px solid transparent;
    transition:
        transform 0.55s cubic-bezier(.44,.82,.34,.98),
        opacity 0.55s,
        border-color 0.21s;
    position: relative;
    padding: 0 6px;
}

.carousel-item img {
    width: 79px;
    height: 79px;
    object-fit: contain;
    margin-bottom: 14px;
    transition: width 0.5s, height 0.5s;
    display: block;
}

.carousel-item.outer {
    opacity: 0.13;
    filter: blur(1.3px);
}
.carousel-item.side {
    opacity: 0.52;
    transform: scale(1.11);
}
.carousel-item.center {
    transform: scale(1.38);
    background: #fff;
    opacity: 1;
    z-index: 3;
    border: 2.7px solid #32adfa;
    box-shadow: 0 3px 24px 0 #a7e8fc26;
}

.carousel-item.center img {
    width: 110px;
    height: 110px;
}
.carousel-arrow {
    background: none;
    border: none;
    font-size: 29px;
    color: #1790d5;
    cursor: pointer;
    padding: 4px;
    transition: color .15s;
    line-height: 1;
    user-select: none;
}
.carousel-arrow:hover {
    color: #055086;
}

.prod-name {
    font-size: 1.22em;
    color: #25548a;
    font-weight: 700;
    text-align: center;
    max-width: 136px;
    margin-top: 7px;
    text-overflow: ellipsis;
    overflow: hidden;
    white-space: nowrap;
    text-shadow: 0 2px 6px #fff, 0 1px 0 #e9f6ff;
    letter-spacing: 0.01em;
    background: none;
    padding-bottom: 2px;
}
.carousel-item.outer .prod-name { opacity: 0.13; }
.carousel-item.side .prod-name { opacity: 0.55; }
.carousel-item.center .prod-name { opacity: 1; }

</style>
