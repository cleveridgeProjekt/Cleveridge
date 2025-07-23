<script setup>
defineProps({
    title: { type: String, required: true },
    icon: { type: String, default: '' },
})
</script>

<template>
    <div class="page-header">
        <div class="header-row">
            <span class="header-title">{{ title }}</span>
        </div>
        <div class="header-subline">
            <span class="subline-box">
                <slot />
                <!-- Use 'fas' or 'far' here -->
                <i class="fas fa-info-circle info-icon"></i>
            </span>
        </div>
    </div>
</template>

<style scoped>
.page-header {
    width: 100%;
    margin: 0 0 20px 0;
    padding: 14px 20px 12px 24px;
    border-radius: 9px;
    background: rgba(255,255,255,0.87);
    box-shadow: 0 4px 22px 0 rgba(80,130,180,0.09), 0 1.5px 5px 0 rgba(120,170,240,0.06);
    position: relative;
    backdrop-filter: blur(1.5px);
}

.header-row {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 7px;
}

.header-title {
    font-size: 1.38rem;
    font-weight: 900;
    color: #1761a0;
    letter-spacing: -0.2px;
    line-height: 1.08;
}

.header-subline {
    width: 100%;
}

.subline-box {
    display: inline-flex;
    align-items: center;
    background: linear-gradient(90deg, #e8f1fc 75%, #e1effd 100%);
    color: #1761a0;
    font-weight: 500;
    padding: 6px 18px 6px 10px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(80,130,180,0.05);
    font-size: 1rem;

}

.info-icon {
    margin-left: 10px;
    font-size: 17px;
    color: #222;
    opacity: 0.68;
}
</style>
