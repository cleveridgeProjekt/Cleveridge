<template>
    <div class="auth-outer">
        <div class="auth-left">
            <div class="auth-card">
                <slot />
            </div>
        </div>
        <transition name="slide-in-img" appear>
            <div class="auth-right" v-if="showImage">
                <img :src="bgImage" alt="Cleveridge Smart Fridge" class="auth-img" />
            </div>
        </transition>
    </div>
</template>

<script>
export default {
    name: 'PublicLayout',
    data() {
        return {
            bgImage: '/media/fridge_explain.webp',
            showImage: true,
        }
    },
    mounted() {
        this.updateVisibility()
        window.addEventListener('resize', this.updateVisibility)
    },
    beforeUnmount() {
        window.removeEventListener('resize', this.updateVisibility)
    },
    methods: {
        updateVisibility() {
            this.showImage = window.innerWidth >= 1200
        }
    }
}
</script>

<style scoped>
.auth-outer {
    min-height: 100vh;
    width: 100vw;
    display: flex;
    background: #94cfff;
}

.auth-left {
    min-width: 650px;
    max-width: 720px;
    width: 38vw;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #0c5288;
    z-index: 2;
}

.auth-card {
    width: 360px;
    max-width: 90vw;
    background: #fff;
    border-radius: 6px;
    box-shadow: 0 10px 38px 0 rgba(0, 0, 0, 0.13);
    padding: 44px 32px 32px 32px;
}

.auth-right {
    position: fixed;
    right: 0;
    top: 0;
    z-index: 1;
    width: 1000px;
    height: 100vh;
    display: flex;
    align-items: stretch;
    justify-content: flex-end;
    background: none;
    pointer-events: none;
}

.auth-img {
    width: 1000px;
    height: 100vh;
    max-width: 100vw;
    object-fit: cover;
    object-position: center;
    border-radius: 0;
    box-shadow: 0 10px 38px 0 rgba(0, 0, 0, 0.10);
    display: block;
    pointer-events: auto;
}

.slide-in-img-enter-active {
    animation: slideInImgRight 2.7s cubic-bezier(.38, 0, .33, 1.05) both;
}

@keyframes slideInImgRight {
    0% {
        opacity: 0;
        transform: translateX(-600px); /* Wider slide distance */
    }
    100% {
        opacity: 1;
        transform: translateX(0);
    }
}

@media (max-width: 1200px) {
    .auth-right, .auth-img {
        display: none !important;
    }

    .auth-left {
        width: 100vw;
        max-width: none;
        justify-content: center;
        background: #94cfff;
        min-width: 0;
    }

    .auth-card {
        margin: 32px auto;
        box-shadow: 0 6px 18px 0 rgba(0, 0, 0, 0.10);
    }
}
</style>
