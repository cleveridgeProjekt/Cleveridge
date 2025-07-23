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
        <RouterLink class="nav-item" to="/"><i class="fal fa-home"></i> Dashboard</RouterLink>
        <RouterLink class="nav-item" to="/fridge"><i class="fal fa-ice-cream"></i> What's in your fridge</RouterLink>
        <RouterLink class="nav-item" to="/products"><i class="fal fa-apple-alt"></i> Produkte</RouterLink>
        <RouterLink class="nav-item" to="/shopping-list"><i class="fal fa-list-alt"></i> Einkaufsliste</RouterLink>
        <div class="submenu-toggle" @click="toggleSubmenu">
            <span class="submenu-link"><i class="fal fa-layer-group"></i> Finanzen <i :class="showSubmenu ? 'fal fa-angle-up' : 'fal fa-angle-down'"></i></span>
        </div>
        <div v-if="showSubmenu" class="submenu">
            <RouterLink class="submenu-item" to="/status"><i class="fal fa-lightbulb"></i> Cleveridge Status</RouterLink>
            <RouterLink class="submenu-item" to="/expiry"><i class="fal fa-exclamation-triangle"></i> Ablaufwarnungen</RouterLink>
            <RouterLink class="submenu-item" to="/barcode"><i class="fal fa-barcode"></i> Barcode Scannen!</RouterLink>
        </div>
    </div>
</template>

<style scoped>
#sidebar {
    padding-top: 40px;
    width: 330px;
    height: 100vh;
    background-color: rgb(0, 103, 184);
    color: white;
    display: grid;
    grid-auto-rows: max-content;
    gap: 10px;
}

.nav-item {
    display: grid;
    grid-template-columns: 35px auto;
    column-gap: 12px;
    align-items: center;
    padding: 15px 40px;
    font-weight: bold;
    color: white;
    text-decoration: none;

    i {
        justify-self: center;
    }

    &:hover {
        background-color: rgb(0, 85, 152);
    }
}

.submenu-toggle {
    display: grid;
    grid-template-columns: auto 35px;
    align-items: center;
    padding: 15px 40px;
    column-gap: 12px;
    color: white;
    cursor: pointer;

    .submenu-link {
        display: grid;
        grid-template-columns: 35px 150px 35px; /* icon, text, arrow */
        align-items: center;
        column-gap: 12px;
        color: white;
        text-decoration: none;

        &:hover {
            background-color: rgb(0, 85, 152);
        }
    }

    .icon {
        display: flex;
        justify-content: center;
        cursor: pointer;

        i {
            font-size: 14px;
        }
    }
}

.submenu {
    background-color: rgb(0, 103, 184);
    display: grid;
    grid-auto-rows: max-content;

    .submenu-item {
        display: grid;
        grid-template-columns: 35px auto;
        column-gap: 12px;
        align-items: center;
        padding: 10px 75px;
        color: white;
        text-decoration: none;

        i {
            justify-self: center;
        }

        &:hover {
            background-color: rgb(0, 85, 152);
        }
    }
}
</style>
