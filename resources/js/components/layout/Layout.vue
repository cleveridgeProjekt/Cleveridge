<script setup>
import Sidebar from './Sidebar.vue'
</script>

<template>
    <div class="layout-wrapper">
        <!-- Header -->
        <header class="layout-header">
            <div class="header-grid">
                <span class="header-title">Cleveridge</span>
                <a href="/logout" class="logout-link"><i class="fal fa-user"></i> Abmelden</a>
            </div>
        </header>
        <!-- Sidebar + Page Content -->
        <div class="layout-body">
            <Sidebar />
            <main class="layout-content">
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
.layout-wrapper {
    display: grid;
    grid-template-rows: 60px 1fr;
    height: 100vh;
    margin: 0;
    padding: 0;
    font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
    background: #f9fafb;
}
.layout-header {
    background-color: #005598;
    color: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.08);
    margin: 0;
}
.header-grid {
    display: grid;
    grid-template-columns: 1fr auto;
    align-items: center;
    height: 60px;
    padding: 0 32px;
}
.header-title {
    font-size: 20px;
    font-weight: 600;
    letter-spacing: 1px;
}
.logout-link {
    color: white;
    font-weight: 500;
    display: inline-grid;
    grid-auto-flow: column;
    align-items: center;
    column-gap: 10px;
    padding: 18px 16px;
    transition: background-color 0.2s ease;
    text-decoration: none;
}
.logout-link:hover {
    background-color: #0067b8;
}
.layout-body {
    display: grid;
    grid-template-columns: 250px 1fr;
    height: 100%;
}
main.layout-content {
    padding: 32px 32px 0 32px;
    background: #f9fafb;
    min-height: 0;
    height: 100%;
    overflow-y: auto;
    font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
}
</style>
