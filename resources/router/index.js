import { createRouter, createWebHistory } from 'vue-router'

import Dashboard from "../js/components/Dashboard.vue"
import LoginForm from '../js/components/LoginForm.vue'
import RegisterForm from '../js/components/RegisterForm.vue'
import { user, fetchUser } from '../js/composables/useUser'

const routes = [
    { path: '/', component: Dashboard, name: 'dashboard' },
    { path: '/login', component: LoginForm, name: 'login' },
    { path: '/register', component: RegisterForm, name: 'register' },
    { path: '/fridge', component: () => import('../js/components/Fridge.vue'), name: 'fridge' },
    { path: '/products', component: () => import('../js/components/Products.vue'), name: 'products' },
    { path: '/receipts', component: () => import('../js/components/Receipts.vue'), name: 'receipts' },
    { path: '/shopping-list', component: () => import('../js/components/ShoppingList.vue'), name: 'shopping-list' },
    { path: '/status', component: () => import('../js/components/Status.vue'), name: 'status' },
    { path: '/expiry', component: () => import('../js/components/Expiry.vue'), name: 'expiry' },
    { path: '/product-recognition', component: () => import('../js/components/ProductRecognition.vue'), name: 'product-recognition' },
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

router.beforeEach(async (to, from, next) => {
    const publicPages = ['/login', '/register']
    const authRequired = !publicPages.includes(to.path)

    if (user.value === null && authRequired) {
        await fetchUser()
    }

    if (authRequired && !user.value) {
        return next('/login')
    }
    if (publicPages.includes(to.path) && user.value) {
        return next('/')
    }
    next()
})

export default router
