<template>
    <div>
        <PageHeader title="Produkte" icon="fal fa-apple-alt">
            Verwalte deine Produkte. Füge neue hinzu oder bearbeite bestehende Einträge.
        </PageHeader>

        <!-- Create -->
        <div class="card create">
            <input class="ui-input" v-model="form.name" placeholder="Produktname"/>
            <input class="ui-input" v-model="form.barcode" placeholder="Barcode (optional)"/>
            <input class="ui-input small" type="number" min="0" v-model.number="form.default_expiry_days"
                   placeholder="Standard-Haltbarkeit (Tage)"/>
            <input class="ui-input" v-model="form.image_url" placeholder="Bild-URL (optional)"/>
            <button class="btn" :disabled="!form.name" @click="create">Hinzufügen</button>
        </div>

        <!-- Search -->
        <div class="card search">
            <input class="ui-input" v-model="q" placeholder="Suche…"/>
        </div>

        <!-- Table -->
        <div class="card">
            <table class="tbl">
                <thead>
                <tr>
                    <th>Bild</th>
                    <th>Name</th>
                    <th>Barcode</th>
                    <th>Std. Haltbarkeit</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="p in filtered" :key="p.id">
                    <td><img v-if="p.image_url" :src="p.image_url" class="thumb" alt=""></td>
                    <td><input class="ui-input" v-model="p.name" @change="save(p)"></td>
                    <td><input class="ui-input" v-model="p.barcode" @change="save(p)"></td>
                    <td><input class="ui-input small" type="number" min="0" v-model.number="p.default_expiry_days"
                               @change="save(p)"></td>
                    <td class="actions">
                        <button class="btn small" @click="openNutrition(p)">Nährwerte</button>
                        <button class="icon-btn danger" title="Löschen" @click="remove(p)"><i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <NutritionEditor v-if="nutriProduct" :product="nutriProduct" @close="nutriProduct=null"/>
    </div>
</template>

<script>
import axios from 'axios'
import PageHeader from "./layout/PageHeader.vue";
import NutritionEditor from './NutritionEditor.vue'

export default {
    name: 'Products',
    components: {PageHeader, NutritionEditor},
    data() {
        return {
            products: [],
            q: '',
            form: {name: '', barcode: '', default_expiry_days: null, image_url: ''},
            nutriProduct: null,
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
        }
    },
    methods: {
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
            if (!confirm('Produkt wirklich löschen?')) return
            await axios.delete(`/api/products/${p.id}`)
            this.products = this.products.filter(x => x.id !== p.id)
        },
        openNutrition(p) {
            this.nutriProduct = p
        }
    },
    async mounted() {
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

.create, .search {
    display: grid;
    grid-template-columns: 1fr 1fr 220px 1fr auto;
    gap: 10px;
    align-items: center;
}

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

.thumb {
    width: 44px;
    height: 44px;
    object-fit: cover;
    border-radius: 4px;
    background: #f5f7fb;
}

.actions {
    display: flex;
    gap: 8px;
}

.btn.small {
    height: 32px;
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
</style>
