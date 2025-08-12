<template>
    <div class="overlay" @click.self="$emit('close')">
        <div class="modal">
            <div class="modal-head">
                <h3>Nährwerte: {{ product.name }}</h3>
                <button class="icon-btn" @click="$emit('close')"><i class="fas fa-times"></i></button>
            </div>

            <form class="grid" @submit.prevent="save">
                <label>Kurzbeschreibung <textarea v-model="f.short_description"/></label>
                <label>Portionsgröße <input v-model="f.serving_size"/></label>
                <label>Kalorien <input type="number" v-model.number="f.calories"/></label>
                <label>Protein (g) <input type="number" step="0.1" v-model.number="f.protein"/></label>
                <label>Kohlenhydrate (g) <input type="number" step="0.1" v-model.number="f.carbs"/></label>
                <label>Zucker (g) <input type="number" step="0.1" v-model.number="f.sugar"/></label>
                <label>Ballaststoffe (g) <input type="number" step="0.1" v-model.number="f.fiber"/></label>
                <label>Fett (g) <input type="number" step="0.1" v-model.number="f.fat"/></label>
                <label>gesättigt (g) <input type="number" step="0.1" v-model.number="f.saturated_fat"/></label>
                <label>einfach unges. (g) <input type="number" step="0.1"
                                                 v-model.number="f.monounsaturated_fat"/></label>
                <label>mehrfach unges. (g) <input type="number" step="0.1"
                                                  v-model.number="f.polyunsaturated_fat"/></label>
                <label>Salz (mg) <input type="number" step="0.1" v-model.number="f.salt"/></label>
                <label>Vitamine/Mineralstoffe <input v-model="f.vitamins_minerals"/></label>
                <label>Allergene <input v-model="f.allergens"/></label>
                <label>Häufige Verwendung <input v-model="f.common_uses"/></label>

                <div class="actions">
                    <button class="btn" type="submit">Speichern</button>
                    <button class="btn ghost" type="button" @click="$emit('close')">Schließen</button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
import axios from 'axios'

export default {
    name: 'NutritionEditor',
    props: {product: Object},
    data() {
        return {
            f: {
                short_description: '', serving_size: '',
                calories: null, protein: null, carbs: null, sugar: null, fiber: null,
                fat: null, saturated_fat: null, monounsaturated_fat: null, polyunsaturated_fat: null,
                salt: null, vitamins_minerals: '', allergens: '', common_uses: ''
            },
            exists: false,
            busy: false
        }
    },
    methods: {
        async load() {
            try {
                const {data} = await axios.get(`/api/products/${this.product.id}/nutrition`)
                this.exists = true
                this.f = {...this.f, ...data}
            } catch {
                this.exists = false
            }
        },
        async save() {
            this.busy = true
            try {
                const url = `/api/products/${this.product.id}/nutrition`
                if (this.exists) await axios.put(url, this.f)
                else await axios.post(url, this.f)
                this.$emit('close')
            } finally {
                this.busy = false
            }
        }
    },
    mounted() {
        this.load()
    }
}
</script>

<style scoped>
.overlay {
    position: fixed;
    inset: 0;
    background: #0008;
    display: grid;
    place-items: center;
    z-index: 99;
}

.modal {
    background: #fff;
    width: min(860px, 95vw);
    border-radius: 12px;
    padding: 18px;
    box-shadow: 0 10px 40px #0003;
}

.modal-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
}

.grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px 12px;
}

.grid label {
    display: grid;
    gap: 6px;
    color: #234;
    font-size: .95rem;
}

textarea {
    min-height: 70px;
    resize: vertical;
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

.btn.ghost {
    background: #fff;
    border-color: #d5e7f5;
    color: #2b5c8c;
}

.actions {
    grid-column: 1 / -1;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 8px;
}

.icon-btn {
    height: 32px;
    min-width: 32px;
    border: 1px solid #d9e7f2;
    border-radius: 8px;
    background: #fff;
}
</style>
