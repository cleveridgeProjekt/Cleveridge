import { createRouter, createWebHistory } from 'vue-router'
import { user, fetchUser } from '../js/composables/useUser'

const Dashboard           = () => import('../js/components/Dashboard.vue')
const LoginForm           = () => import('../js/components/LoginForm.vue')
const RegisterForm        = () => import('../js/components/RegisterForm.vue')
const Fridge              = () => import('../js/components/Fridge.vue')
const Products            = () => import('../js/components/Products.vue')
const Receipts            = () => import('../js/components/Receipts.vue')
const ShoppingList        = () => import('../js/components/ShoppingList.vue')
const Status              = () => import('../js/components/Status.vue')
const Expiry              = () => import('../js/components/Expiry.vue')
const ProductRecognition  = () => import('../js/components/ProductRecognition.vue')

const routes = [
    // Public pages
    { path: '/login',    name: 'login',    component: LoginForm,    meta: { public: true } },
    { path: '/register', name: 'register', component: RegisterForm, meta: { public: true } },

    { path: '/',                   name: 'dashboard',           component: Dashboard },
    { path: '/fridge',             name: 'fridge',              component: Fridge },
    { path: '/products',           name: 'products',            component: Products },
    { path: '/receipts',           name: 'receipts',            component: Receipts },
    { path: '/shopping-list',      name: 'shopping-list',       component: ShoppingList },
    { path: '/status',             name: 'status',              component: Status },
    { path: '/expiry',             name: 'expiry',              component: Expiry },
    { path: '/product-recognition',name: 'product-recognition', component: ProductRecognition },

    { path: '/:pathMatch(.*)*', redirect: '/' },
]

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior() {
        return { top: 0 }
    },
})

router.beforeEach(async (to, from, next) => {
    const isPublic = to.matched.some(r => r.meta?.public)
    if (!isPublic && user.value === null) {
        await fetchUser()
    }

    if (!isPublic && !user.value) {
        return next({ name: 'login', query: { redirect: to.fullPath } })
    }

    if (isPublic && user.value) {
        return next({ name: 'dashboard' })
    }

    return next()
})

export default router
