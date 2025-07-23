<template>
    <div class="auth-bg">
        <div class="auth-card">
            <slot />
        </div>
    </div>
</template>
<style scoped>
.auth-bg {
    min-height: 100vh;
    background: #0077d9;
    display: flex;
    align-items: center;
    justify-content: center;
}
.auth-card {
    background: #fff;
    padding: 44px 32px 32px 32px;
    border-radius: 20px;
    box-shadow: 0 10px 38px 0 rgba(0,0,0,0.13);
    min-width: 360px;
    max-width: 430px;
    width: 100%;
}
</style>
