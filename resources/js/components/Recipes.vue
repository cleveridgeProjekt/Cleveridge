<template>
    <div>
        <PageHeader title="Rezepte" icon="fas fa-receipt">
            <p>Hier findest du alle Rezepte und Ideen für deine gespeicherten Zutaten!</p>
        </PageHeader>

        <section class="card">
            <div class="controls">
                <select class="ui-input" v-model.number="fridgeId">
                    <option :value="null">Alle Kühlschränke</option>
                    <option v-for="f in fridges" :key="f.id" :value="f.id">
                        {{ f.name || 'Ohne Name' }}
                    </option>
                </select>

                <input class="ui-input" v-model="diet" placeholder="Ernährung (z.B. vegetarisch)" />
                <input class="ui-input qty" type="number" min="1" max="8" v-model.number="count" />
                <button class="btn" :disabled="loading" @click="fetchRecipes">
                    <i class="fas fa-magic"></i> Vorschläge holen
                </button>
            </div>

            <div v-if="loading" class="empty-state">
                <i class="fas fa-spinner fa-spin"></i> Laden…
            </div>

            <div v-else-if="recipes.length === 0" class="empty-state">
                Noch keine Vorschläge.
            </div>

            <div v-else class="recipes">
                <article class="recipe" v-for="r in recipes" :key="r.title">
                    <h3>{{ r.title }}</h3>
                    <div class="meta">
                        ⏱ {{ r.time_minutes }} min • {{ r.difficulty }} • {{ r.servings }} Portionen
                    </div>

                    <div class="row">
                        <div>
                            <strong>Verwendet:</strong>
                            <ul>
                                <li v-for="u in r.uses" :key="u">{{ u }}</li>
                            </ul>
                        </div>
                        <div v-if="r.missing_ok && r.missing_ok.length">
                            <strong>Optional einkaufen:</strong>
                            <ul>
                                <li v-for="m in r.missing_ok" :key="m">{{ m }}</li>
                            </ul>
                        </div>
                    </div>

                    <ol class="steps">
                        <li v-for="s in r.steps" :key="s">{{ s }}</li>
                    </ol>

                    <p v-if="r.notes" class="notes">{{ r.notes }}</p>
                </article>
            </div>

            <div v-if="error" class="error-note">
                <i class="fas fa-exclamation-triangle"></i> {{ error }}
            </div>
        </section>
        <small v-if="source==='local-quota' || source==='local-no-key' || source==='local-exception'">
            Hinweis: Vorschläge ohne KI (Fallback).
        </small>

    </div>
</template>

<script>
import axios from 'axios'
import PageHeader from './layout/PageHeader.vue'

export default {
    name: 'Recipes',
    components: { PageHeader },
    data() {
        return {
            diet: '',
            count: 5,
            loading: false,
            error: '',
            recipes: [],
            fridges: [],
            fridgeId: null,
            source: ''
        }
    },
    async mounted() {
        try {
            const {data} = await axios.get('/api/fridges')
            this.fridges = Array.isArray(data) ? data : []
            this.fridgeId = this.fridges[0]?.id ?? null
        } catch {
            this.fridges = []
        }
    },
    methods: {
        async fetchRecipes() {
            this.loading = true
            this.error = ''
            try {
                const {data} = await axios.post('/api/recipes/suggest', {
                    diet: this.diet || null,
                    count: this.count,
                    fridge_id: this.fridgeId,
                })
                this.recipes = Array.isArray(data?.recipes) ? data.recipes : []
                this.source = data?.source || 'unknown'
            } catch (e) {
                this.error =
                    e?.response?.data?.error || e.message || 'Konnte Rezepte nicht laden.'
                console.error('recipes/suggest failed', e?.response?.data || e)
            } finally {
                this.loading = false
            }
        },
    },
}
</script>

<style scoped>
.card {
    background: #fff;
    border: 1px solid #eaeef4;
    border-radius: 12px;
    padding: 16px;
    box-shadow: 0 2px 14px #b9e8fa14;
}

/* 4 columns: Fridge | Diet | Count | Button */
.controls {
    display: grid;
    grid-template-columns: 1.2fr 1fr 120px auto;
    gap: 10px;
    margin-bottom: 12px;
}

@media (max-width: 900px) {
    .controls {
        grid-template-columns: 1fr 1fr;
    }

    .btn {
        grid-column: 1 / -1;
        justify-self: start;
    }
}

.ui-input {
    height: 36px;
    border: 1px solid #cfe1ef;
    border-radius: 6px;
    padding: 0 10px;
    background: #f6fbff;
}

.qty {
    text-align: center;
}

.btn {
    height: 36px;
    padding: 0 14px;
    border-radius: 6px;
    border: 1px solid #b7e9fa;
    background: #f4fbff;
    color: #2568ad;
    font-weight: 600;
}

.empty-state {
    padding: 18px;
    text-align: center;
    color: #27598a;
    border: 1.5px dashed #cfe1ef;
    border-radius: 10px;
    background: #fff;
}

.recipes {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 12px;
}

.recipe {
    border: 1px solid #eef3f7;
    border-radius: 12px;
    padding: 12px;
    background: #fff;
}

.recipe h3 {
    margin: 0 0 4px;
    color: #25548a;
}

.meta {
    font-size: 0.9rem;
    color: #4b6a8a;
    margin-bottom: 6px;
    margin-bottom: 6px;
}

.row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}

.steps {
    margin: 8px 0 0;
    padding-left: 18px;
}

.notes {
    margin-top: 6px;
    color: #3a5;
}

.error-note {
    margin-top: 10px;
    padding: 10px 12px;
    border: 1px solid #ffd6d6;
    background: #fff4f4;
    color: #8b1c1c;
    border-radius: 8px;
}
</style>
