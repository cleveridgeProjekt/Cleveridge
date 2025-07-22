import { createRouter, createWebHistory } from 'vue-router';

import Dashboard from "../js/components/Dashboard.vue";


const routes = [
    { path: '/', component: Dashboard, name: 'dashboard' },
    { path: '/fridge', component: () => import('../js/components/Fridge.vue'), name: 'fridge' },
    { path: '/products', component: () => import('../js/components/Products.vue'), name: 'products' },
    { path: '/shopping-list', component: () => import('../js/components/ShoppingList.vue'), name: 'shopping-list' },
    { path: '/status', component: () => import('../js/components/Status.vue'), name: 'status' },
    { path: '/expiry', component: () => import('../js/components/Expiry.vue'), name: 'expiry' },
    { path: '/barcode', component: () => import('../js/components/Barcode.vue'), name: 'barcode' },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
