<template>
    <div>
        <PageHeader title="What's in your fridge" icon="fal fa-snowflake">
            Hier siehst du alle Produkte, die aktuell in deinem Kühlschrank sind.
            <br> Du kannst Produkte hinzufügen/entfernen, Mengen & Ablaufdaten verwalten.
        </PageHeader>

        <div class="fridge-grid">
            <section class="card">
                <h3 class="card-title">Meine Kühlschränke</h3>

                <ul class="fridge-list">
                    <li v-for="f in fridges" :key="f.id"
                        :class="['fridge-row', { active: f.id === selectedFridgeId }]"
                        @click="selectFridge(f.id)">
                        <span>{{ f.name || 'Ohne Name' }}</span>
                        <div class="row-actions" @click.stop>
                            <button class="icon-btn" title="Umbenennen" @click="promptRename(f)"><i
                                class="fas fa-pen"></i></button>
                            <button class="icon-btn danger" title="Löschen" @click="deleteFridge(f)"><i
                                class="fas fa-trash"></i></button>
                        </div>
                    </li>
                </ul>

                <div class="new-fridge">
                    <input class="ui-input" v-model="newFridgeName" placeholder="Neuen Kühlschrank benennen…"/>
                    <button class="btn" @click="createFridge"><i class="fas fa-plus"></i> Anlegen</button>
                </div>
            </section>

            <section class="card" v-if="currentFridge">
                <div class="card-title-row">
                    <h3 class="card-title">
                        Inhalt: {{ currentFridge.name || 'Ohne Name' }}
                    </h3>
                </div>

                <div class="add-item">
                    <select class="ui-select" v-model="add.product_id">
                        <option disabled value="">Produkt wählen…</option>
                        <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                    </select>

                    <input class="ui-input qty" type="number" min="1" v-model.number="add.quantity"
                           placeholder="Menge"/>

                    <input class="ui-input" type="date" v-model="add.expiry_date"/>

                    <button class="btn" :disabled="!add.product_id || busyAdd" @click="addItem">
                        <i class="fas fa-plus"></i> Hinzufügen
                    </button>
                </div>

                <div v-if="(currentFridge.items || []).length === 0" class="empty">
                    <i class="fas fa-ice-cream"></i> Noch keine Produkte in diesem Kühlschrank.
                </div>

                <table v-else class="items-table">
                    <thead>
                    <tr>
                        <th>Bild</th>
                        <th>Produkt</th>
                        <th>Menge</th>
                        <th>Ablaufdatum</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="it in currentFridge.items" :key="it.id">
                        <td>
                            <img v-if="it.product?.image_url" :src="it.product.image_url" class="thumb" alt="">
                        </td>
                        <td>{{ it.product?.name || '—' }}</td>
                        <td>
                            <input class="ui-input qty" type="number" min="1"
                                   v-model.number="it.quantity"
                                   @change="updateItem(it)">
                        </td>
                        <td>
                            <input class="ui-input" type="date"
                                   :value="formatDateForInput(it.expiry_date)"
                                   @change="e => updateItem(it, { expiry_date: e.target.value })">
                            <span class="badge" :class="expiryClass(it.expiry_date)">{{
                                    expiryLabel(it.expiry_date)
                                }}</span>
                        </td>
                        <td>
                            <button class="icon-btn danger" title="Entfernen" @click="deleteItem(it)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </section>
        </div>
    </div>
</template>

<script>
import axios from 'axios'
import PageHeader from "./layout/PageHeader.vue";

export default {
    name: 'Fridge',
    components: {PageHeader},
    data() {
        return {
            fridges: [],
            selectedFridgeId: null,
            products: [],
            newFridgeName: '',
            add: {product_id: '', quantity: 1, expiry_date: ''},
            busyAdd: false,
        }
    },
    computed: {
        currentFridge() {
            return this.fridges.find(f => f.id === this.selectedFridgeId)
        }
    },
    methods: {
        async fetchFridges() {
            const {data} = await axios.get('/api/fridges')
            this.fridges = (data || []).map(f => ({...f, items: f.items || []}))
            if (!this.selectedFridgeId && this.fridges.length) {
                this.selectedFridgeId = this.fridges[0].id
            }
        },
        async fetchProducts() {
            try {
                const {data} = await axios.get('/api/products')
                this.products = Array.isArray(data) ? data : []
            } catch {
                this.products = []
            }
        },
        selectFridge(id) {
            this.selectedFridgeId = id
        },

        async createFridge() {
            const {data} = await axios.post('/api/fridges', {name: this.newFridgeName || null})
            this.fridges.push({...data, items: []})
            this.selectedFridgeId = data.id
            this.newFridgeName = ''
        },
        async promptRename(f) {
            const name = window.prompt('Neuer Name:', f.name || '')
            if (name === null) return
            const {data} = await axios.put(`/api/fridges/${f.id}`, {name})
            Object.assign(f, data)
        },
        async deleteFridge(f) {
            if (!confirm('Diesen Kühlschrank löschen?')) return
            await axios.delete(`/api/fridges/${f.id}`)
            this.fridges = this.fridges.filter(x => x.id !== f.id)
            if (this.selectedFridgeId === f.id) {
                this.selectedFridgeId = this.fridges[0]?.id || null
            }
        },

        async addItem() {
            if (!this.currentFridge) return
            this.busyAdd = true
            try {
                const {data} = await axios.post(`/api/fridges/${this.currentFridge.id}/items`, this.add)
                this.currentFridge.items.push(data)
                this.add = {product_id: '', quantity: 1, expiry_date: ''}
            } finally {
                this.busyAdd = false
            }
        },
        async updateItem(it, extra = {}) {
            const payload = { quantity: it.quantity, ...extra }
            const d = this.formatDateForInput(it.expiry_date)
            if (d) payload.expiry_date = d
            const { data } = await axios.patch(`/api/fridge-items/${it.id}`, payload)
            Object.assign(it, data)
        },
        async deleteItem(it) {
            if (!confirm('Produkt entfernen?')) return
            await axios.delete(`/api/fridge-items/${it.id}`)
            this.currentFridge.items = this.currentFridge.items.filter(x => x.id !== it.id)
        },

        formatDateForInput(val) {
            if (!val) return ''
            const d = new Date(val)
            const yyyy = d.getFullYear()
            const mm = String(d.getMonth() + 1).padStart(2, '0')
            const dd = String(d.getDate()).padStart(2, '0')
            return `${yyyy}-${mm}-${dd}`
        },
        expiryClass(date) {
            if (!date) return 'neutral'
            const today = new Date();
            today.setHours(0, 0, 0, 0)
            const d = new Date(date);
            d.setHours(0, 0, 0, 0)
            if (d < today) return 'expired'
            const diff = (d - today) / (1000 * 60 * 60 * 24)
            if (diff <= 3) return 'soon'
            return 'ok'
        },
        expiryLabel(date) {
            if (!date) return '–'
            const d = new Date(date)
            return d.toLocaleDateString()
        }
    },
    async mounted() {
        await Promise.all([this.fetchProducts(), this.fetchFridges()])
    }
}
</script>

<style scoped>
.fridge-grid {
    display: grid;
    grid-template-columns: 520px 1fr;
    gap: 20px;
}

.card {
    background: #fff;
    border: 1px solid #eaeef4;
    border-radius: 12px;
    padding: 16px;
    box-shadow: 0 2px 14px 0 #b9e8fa14;
}

.card-title {
    margin: 0 0 10px;
    color: #25548a;
}

.card-title-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.fridge-list {
    list-style: none;
    padding: 0;
    margin: 0 0 12px;
}

.fridge-row {
    display: grid;
    grid-template-columns: 1fr auto;
    align-items: center;
    padding: 10px 10px;
    border: 1px solid #eef3f7;
    border-radius: 10px;
    margin-bottom: 8px;
    cursor: pointer;
}

.fridge-row.active {
    border-color: #94cfff;
    background: #f4fbff;
}

.row-actions {
    display: flex;
    gap: 6px;
}

.new-fridge {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 8px;
    margin-top: 10px;
}

.add-item {
    display: grid;
    grid-template-columns: 1fr 120px 180px auto;
    gap: 10px;
    margin-bottom: 12px;
}

.ui-input, .ui-select {
    height: 36px;
    border: 1px solid #cfe1ef;
    border-radius: 2px;
    padding: 0 10px;
    background: #f6fbff;
}

.qty {
    width: 88px;
    text-align: center;
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

.icon-btn {
    height: 30px;
    min-width: 30px;
    border: 1px solid #d9e7f2;
    border-radius: 8px;
    background: #fff;
}

.icon-btn.danger {
    color: #b90000;
    border-color: #f1d2d2;
}

.items-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.items-table th, .items-table td {
    padding: 10px 8px;
    border-bottom: 1px solid #f0f5fa;
    text-align: left;
}

.thumb {
    width: 48px;
    height: 48px;
    object-fit: cover;
    border-radius: 4px;
    background: #f5f7fb;
}

.badge {
    margin-left: 8px;
    padding: 3px 8px;
    border-radius: 10px;
    font-size: .85rem;
    border: 1px solid #e6eef6;
}

.badge.expired {
    color: #b90000;
    background: #fff5f5;
    border-color: #f4d1d1;
}

.badge.soon {
    color: #855b00;
    background: #fff9e6;
    border-color: #f3e2b8;
}

.badge.ok {
    color: #1b6a2a;
    background: #e9fff1;
    border-color: #cdebd8;
}
</style>
