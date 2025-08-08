<script setup>
import { ref, onMounted } from "vue";
import { RouterLink } from 'vue-router';

const showSubmenu = ref(false);

onMounted(() => {
    showSubmenu.value = localStorage.getItem("submenuOpen") === "true";
});

const toggleSubmenu = () => {
    showSubmenu.value = !showSubmenu.value;
    localStorage.setItem("submenuOpen", showSubmenu.value);
};
</script>

<template>
    <div id="sidebar" class="sidebar">
        <RouterLink class="nav-item" to="/"><i class="fas fa-home"></i> Dashboard</RouterLink>
        <RouterLink class="nav-item" to="/fridge"><i class="fas fa-ice-cream"></i> What's in your fridge</RouterLink>
        <RouterLink class="nav-item" to="/shopping-list"><i class="fas fa-list-alt"></i> Einkaufsliste</RouterLink>
        <div class="submenu-toggle" @click="toggleSubmenu">
            <span class="submenu-link">
                <i class="fas fa-layer-group"></i>Einstellungen
                <i :class="showSubmenu ? 'fas fa-angle-up' : 'fas fa-angle-down'"></i>
            </span>
        </div>
        <div v-if="showSubmenu" class="submenu">
            <RouterLink class="submenu-item" to="/status"><i class="fas fa-lightbulb"></i> Cleveridge Status</RouterLink>
            <RouterLink class="submenu-item" to="/expiry"><i class="fas fa-exclamation-triangle"></i> Ablaufwarnungen</RouterLink>
            <RouterLink class="submenu-item" to="/product-recognition"><i class="fas fa-barcode"></i> Barcode Scannen!</RouterLink>
        </div>
        <RouterLink class="nav-item" to="/products"><i class="fas fa-apple-alt"></i> Produkte</RouterLink>
        <RouterLink class="nav-item" to="/receipts"><i class="fas fa-receipt"></i> Rezepte</RouterLink>

    </div>
</template>

<style scoped>
#sidebar {
    position: sticky;
    top: 62px;
    padding-top: 28px;
    width: 300px;
    height: calc(100vh - 62px);
    background-color: #0c5288;
    color: white;
    display: grid;
    grid-auto-rows: max-content;
    /*gap: 10px;*/
    box-shadow: 1px 0 0 #e3eefa;
    z-index: 10;
    overflow-y: auto;
}

.nav-item {
    display: grid;
    grid-template-columns: 40px 1fr;
    align-items: center;
    padding: 22px 32px 22px 38px;
    font-weight: bold;
    font-size: 17px;
    color: white;
    text-decoration: none;
    box-sizing: border-box;
    border-radius: 0;
    transition: background .18s;
}
.nav-item i {
    justify-self: center;
    font-size: 17px;
}
.nav-item:hover,
.nav-item.router-link-active {
    background-color: #005fa3;
    color: #fff;
}

.submenu-toggle {
    display: grid;
    grid-template-columns: 1fr 40px;
    align-items: center;
    padding: 22px 32px 22px 49px;
    font-size: 19px;
    color: white;
    cursor: pointer;
    box-sizing: border-box;
    border-radius: 0;
    background: none;
    transition: background .18s;
}
.submenu-toggle:hover {
    background-color: #005fa3;
}

.submenu-link {
    display: grid;
    grid-template-columns: 40px 1fr 40px;
    align-items: center;
    color: white;
    font-weight: bold;
    font-size: 17px;
    text-decoration: none;
}
.submenu {
    background-color: #0067b8;
    width: 100%;
    display: grid;
    grid-auto-rows: max-content;
    border-left: 4px solid #005fa3;
    row-gap: 5px;
}
.submenu-item {
    display: grid;
    grid-template-columns: 40px 1fr;
    align-items: center;
    padding: 15px 30px 15px 75px;
    font-size: 16px;
    transition: background .18s;
}
.submenu-item i {
    justify-self: center;
    font-size: 17px;
}
.submenu-item:hover,
.submenu-item.router-link-active {
    background-color: #005fa3;
    color: #fff;
}

</style>
