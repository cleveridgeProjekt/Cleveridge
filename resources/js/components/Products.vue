<template>
    <div>
        <PageHeader :title="$t('products.title')" icon="fal fa-apple-alt">
            {{ $t('products.intro') }}
        </PageHeader>

        <div class="card create">
            <input class="ui-input" v-model="form.name" :placeholder="$t('products.create.name_placeholder')" />
            <input class="ui-input" v-model="form.barcode" :placeholder="$t('products.create.barcode_placeholder')" />
            <input class="ui-input small" type="number" min="0" v-model.number="form.default_expiry_days" :placeholder="$t('products.create.shelf_life_placeholder')" />
            <input class="ui-input" v-model="form.image_url" :placeholder="$t('products.create.image_url_placeholder')" />
            <button class="btn" :disabled="!form.name" @click="create">{{ $t('products.create.add_button') }}</button>
        </div>

        <div class="card search">
            <input class="ui-input" v-model="q" :placeholder="$t('products.search.placeholder')" />
        </div>

        <div class="card">
            <table class="tbl">
                <thead>
                <tr>
                    <th>{{ $t('products.table.headers.favorite') }}</th>
                    <th>{{ $t('products.table.headers.image') }}</th>
                    <th>{{ $t('products.table.headers.name') }}</th>
                    <th>{{ $t('products.table.headers.barcode') }}</th>
                    <th>{{ $t('products.table.headers.std_shelf_life') }}</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="p in filtered" :key="p.id">
                    <td class="fav-cell">
                        <input type="checkbox" :value="p.id" v-model="favoriteIds" />
                    </td>
                    <td><img v-if="p.image_url" :src="p.image_url" class="thumb" alt=""></td>
                    <td><input class="ui-input" v-model="p.name" @change="save(p)"></td>
                    <td><input class="ui-input" v-model="p.barcode" @change="save(p)"></td>
                    <td><input class="ui-input small" type="number" min="0" v-model.number="p.default_expiry_days" @change="save(p)"></td>
                    <td class="actions">
                        <button class="btn small" @click="openNutrition(p)">{{ $t('products.table.actions.nutrition') }}</button>
                        <button class="icon-btn danger" :title="$t('products.table.actions.delete')" @click="remove(p)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <!-- Meine Lieblingsprodukte -->
        <div class="card favs">
            <div class="favs-header">
                <h3 class="favs-title">{{ $t('products.favorites.title') }}</h3>
                <div class="favs-actions">
                    <button class="btn small ghost" @click="selectFiltered" :disabled="filtered.length===0">
                        {{ $t('products.favorites.select_filtered') }}
                    </button>
                    <button class="btn small ghost" @click="clearFavorites" :disabled="favoriteIds.length===0">
                        {{ $t('products.favorites.clear_all') }}
                    </button>
                </div>
            </div>

            <div v-if="favoriteProducts.length" class="fav-grid">
                <div v-for="fp in favoriteProducts" :key="fp.id" class="fav-item">
                    <img :src="fp.image_url || '/media/products/default.png'" :alt="fp.name" class="fav-img" />
                    <div class="fav-name">{{ fp.name }}</div>
                    <button class="icon-btn" @click="removeFavorite(fp.id)" :title="$t('products.favorites.remove')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div v-else class="favs-empty">
                {{ $t('products.favorites.empty') }}
            </div>
        </div>

        <NutritionEditor v-if="nutriProduct" :product="nutriProduct" @close="nutriProduct=null"/>
    </div>
</template>

<script>
import axios from 'axios'
import PageHeader from "./layout/PageHeader.vue";
import NutritionEditor from './NutritionEditor.vue'

const FAV_KEY = 'favorites:v1';

export default {
    name: 'Products',
    components: {PageHeader, NutritionEditor},
    data() {
        return {
            products: [],
            q: '',
            form: {name: '', barcode: '', default_expiry_days: null, image_url: ''},
            nutriProduct: null,
            favoriteIds: []
        }
    },
    computed: {
        filtered() {
            const q = this.q.trim().toLowerCase()
            if (!q) return this.products
            return this.products.filter(p =>
                (p.name || '').toLowerCase().includes(q) ||
                (p.barcode || '').toLowerCase().includes(q)
            )
        },
        favoriteProducts() {
            if (!this.favoriteIds?.length) return []
            const set = new Set(this.favoriteIds)
            return this.products.filter(p => set.has(p.id))
        }
    },
    watch: {
        favoriteIds: {
            deep: true,
            handler(ids) {
                try {
                    const unique = Array.from(new Set(ids)).filter(x => typeof x !== 'undefined' && x !== null)
                    localStorage.setItem(FAV_KEY, JSON.stringify(unique))
                } catch {
                }
            }
        }
    },
    methods: {
        loadFavorites() {
            try {
                const raw = localStorage.getItem(FAV_KEY)
                const arr = JSON.parse(raw || '[]')
                this.favoriteIds = Array.isArray(arr) ? arr : []
            } catch {
                this.favoriteIds = []
            }
        },
        removeFavorite(id) {
            this.favoriteIds = this.favoriteIds.filter(x => x !== id)
        },
        selectFiltered() {
            const ids = this.filtered.map(p => p.id)
            const set = new Set([...(this.favoriteIds || []), ...ids])
            this.favoriteIds = Array.from(set)
        },
        clearFavorites() {
            this.favoriteIds = []
        },
        async fetch() {
            const {data} = await axios.get('/api/products')
            this.products = Array.isArray(data) ? data : []
        },
        async create() {
            const {data} = await axios.post('/api/products', this.form)
            this.products.unshift(data)
            this.form = {name: '', barcode: '', default_expiry_days: null, image_url: ''}
        },
        async save(p) {
            const payload = {
                name: p.name,
                barcode: p.barcode,
                default_expiry_days: p.default_expiry_days,
                image_url: p.image_url
            }
            const {data} = await axios.put(`/api/products/${p.id}`, payload)
            Object.assign(p, data)
        },
        async remove(p) {
            if (!confirm(this.$t('products.prompts.confirm_delete'))) return
            await axios.delete(`/api/products/${p.id}`)
            this.products = this.products.filter(x => x.id !== p.id)
            this.removeFavorite(p.id)
        },
        openNutrition(p) {
            this.nutriProduct = p
        }
    },
    async mounted() {
        this.loadFavorites()
        await this.fetch()
    }
}
</script>

<style scoped>
.card {
    background: #fff;
    border: 1px solid #eaeef4;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 14px;
    box-shadow: 0 2px 14px 0 #b9e8fa14;
}

/* grids */
.create, .search {
    display: grid;
    grid-template-columns: 1fr 1fr 220px 1fr auto;
    gap: 10px;
    align-items: center;
}

.favs-header {
    display: grid;
    grid-template-columns: 1fr auto;
    align-items: center;
    margin-bottom: 10px;
    gap: 10px;
}

.fav-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 12px;
}

.fav-item {
    display: grid;
    grid-template-columns: 56px 1fr 36px;
    align-items: center;
    gap: 10px;
    border: 1px solid #eaf2f9;
    border-radius: 10px;
    padding: 8px 10px;
    background: #f9fcff;
}

.fav-img {
    width: 56px;
    height: 56px;
    object-fit: cover;
    border-radius: 6px;
    background: #f5f7fb;
}

.fav-name {
    font-weight: 700;
    color: #25548a;
}

.favs-title {
    margin: 0;
    font-size: 1.06rem;
    color: #0c5288;
}

.favs-actions {
    display: grid;
    grid-auto-flow: column;
    gap: 8px;
}

.favs-empty {
    color: #6b7b8a;
}

/* table */
.tbl {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.tbl th, .tbl td {
    padding: 10px 8px;
    border-bottom: 1px solid #f0f5fa;
    text-align: left;
}

.fav-cell {
    width: 48px;
}

/* inputs/buttons */
.ui-input {
    height: 36px;
    border: 1px solid #cfe1ef;
    border-radius: 2px;
    padding: 0 10px;
    background: #f6fbff;
}

.ui-input.small {
    width: 220px;
}

.btn {
    height: 36px;
    padding: 0 14px;
    border-radius: 2px;
    border: 1px solid #b7e9fa;
    background: #f4fbff;
    color: #2568ad;
    font-weight: 600;
}

.btn.small {
    height: 32px;
}

.btn.ghost {
    background: #fff;
    color: #2568ad;
}

.icon-btn {
    height: 32px;
    min-width: 32px;
    border: 1px solid #d9e7f2;
    border-radius: 8px;
    background: #fff;
}

.icon-btn.danger {
    color: #b90000;
    border-color: #f1d2d2;
}

.thumb {
    width: 44px;
    height: 44px;
    object-fit: cover;
    border-radius: 4px;
    background: #f5f7fb;
}

.actions {
    display: grid;
    grid-auto-flow: column;
    gap: 8px;
}
</style>
