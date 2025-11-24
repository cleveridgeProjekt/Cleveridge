<template>
    <div>
        <PageHeader title="Rezepte" icon="fas fa-receipt">
            <p>Hier findest du Rezepte aus deinen gespeicherten Zutaten.</p>
        </PageHeader>

        <section class="card">
            <div class="controls">
                <select class="ui-input" v-model.number="fridgeId">
                    <option :value="null">Alle Kühlschränke</option>
                    <option v-for="f in fridges" :key="f.id" :value="f.id">{{ f.name || 'Ohne Name' }}</option>
                </select>

                <input class="ui-input" v-model="diet" placeholder="Ernährung (z. B. vegetarisch)"/>
                <select class="ui-input" v-model.number="count">
                    <option v-for="n in [3,4,5,6,7,8]" :key="n" :value="n">{{ n }}</option>
                </select>

                <label class="chk">
                    <input type="checkbox" v-model="withImages"/>
                    <span>Bilder generieren</span>
                </label>

                <button class="btn" :disabled="loading" @click="fetchRecipes">
                    <i class="fas fa-magic"></i> Vorschläge holen
                </button>
            </div>

            <div v-if="loading" class="empty-state"><i class="fas fa-spinner fa-spin"></i> Laden…</div>
            <div v-else-if="recipes.length === 0" class="empty-state">Noch keine Vorschläge.</div>

            <div v-else class="recipes">
                <article class="recipe" v-for="raw in recipes" :key="raw.title">
                    <img v-if="raw.image_url" class="cover" :src="raw.image_url" alt=""/>
                    <h3>{{ raw.title }}</h3>
                    <div class="meta">
                        <span title="Gesamtzeit"><i class="far fa-clock"></i> {{ raw.total_minutes ?? raw.time_minutes }} min</span>
                        <span>• {{ (raw.difficulty || 'leicht') }}</span>
                        <span>• {{ raw.servings }} Portionen</span>
                    </div>
                    <p v-if="raw.summary" class="summary">{{ raw.summary }}</p>

                    <!-- Zutaten -->
                    <div class="block">
                        <h4>Zutaten (für {{ raw.servings }}):</h4>
                        <ul class="ingredients">
                            <li v-for="i in normalizedIngredients(raw)" :key="i._k">
                                <span class="qty" v-if="i.amount != null">{{ i.amount }} {{ i.unit }}</span>
                                <span class="ing-name">{{ i.name }}</span>
                                <span class="opt" v-if="i.optional">optional</span>
                            </li>
                        </ul>

                        <div v-if="shoppingList(raw)?.length" class="shopping">
                            <div class="lbl">Optional einkaufen:</div>
                            <div class="chips">
                                <a v-for="m in shoppingList(raw)" :key="m" class="chip" :href="searchLink(m)" target="_blank" rel="noopener">
                                    {{ m }}
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Schritte -->
                    <div class="block">
                        <h4>Schritte:</h4>
                        <ol class="steps">
                            <li v-for="(s, idx) in normalizedSteps(raw)" :key="idx">
                                <div class="step-head">
                                    <span class="badge">Schritt {{ idx + 1 }}</span>
                                    <span class="step-time" v-if="s.minutes"><i class="far fa-clock"></i> {{ s.minutes }} min</span>
                                </div>
                                <div class="step-text">{{ s.text || s }}</div>
                            </li>
                        </ol>
                        <p v-if="raw.tips" class="tips">{{ raw.tips }}</p>
                    </div>

                    <!-- Referenzen -->
                    <div v-if="raw.references?.length" class="refs">
                        <div class="lbl">Ähnliche Rezepte (Suche):</div>
                        <div class="chips">
                            <a v-for="ref in raw.references" :key="ref.url" :href="ref.url" class="chip" target="_blank" rel="noopener">
                                {{ ref.site }}
                            </a>
                        </div>
                    </div>
                </article>
            </div>

            <div v-if="error" class="error-note">
                <i class="fas fa-exclamation-triangle"></i> {{ error }}
            </div>
        </section>

        <small v-if="['local-quota','local-no-key','local-exception','local-demo','local-openai-error','local-parse-fallback'].includes(source)">
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
            count: 3,
            withImages: false,
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
                    with_images: this.withImages
                })
                this.recipes = Array.isArray(data?.recipes) ? data.recipes : []
                this.source = data?.source || 'unknown'
            } catch (e) {
                this.error = e?.response?.data?.error || e.message || 'Konnte Rezepte nicht laden.'
                console.error('recipes/suggest failed', e?.response?.data || e)
            } finally {
                this.loading = false
            }
        },

        normalizedIngredients(r) {
            if (Array.isArray(r.ingredients) && r.ingredients.length) {
                return r.ingredients.map((i, idx) => ({...i, _k: idx + ':' + (i.name || '')}))
            }
            const uses = Array.isArray(r.uses) ? r.uses : []
            return uses.map((name, idx) => ({
                name, amount: 1, unit: 'Stück', optional: false, _k: idx + ':' + name
            }))
        },

        normalizedSteps(r) {
            if (Array.isArray(r.steps) && typeof r.steps[0] === 'object') return r.steps
            if (Array.isArray(r.steps) && typeof r.steps[0] === 'string') {
                const per = Math.max(2, Math.round((r.total_minutes || r.time_minutes || 15) / r.steps.length))
                return r.steps.map(t => ({text: t, minutes: per}))
            }
            return []
        },

        shoppingList(r) {
            if (Array.isArray(r.shopping) && r.shopping.length) return r.shopping
            if (Array.isArray(r.missing_ok) && r.missing_ok.length) return r.missing_ok
            const opt = (Array.isArray(r.ingredients) ? r.ingredients : []).filter(i => i.optional).map(i => i.name)
            return opt
        },

        searchLink(q) {
            const term = encodeURIComponent(q || '')
            return `https://www.chefkoch.de/suche.php?suche=${term}`
        }
    }
}
</script>

<style scoped>
.card {
    background: #fff;
    border: 1px solid #eaeef4;
    border-radius: 14px;
    padding: 16px;
    box-shadow: 0 2px 14px #b9e8fa14;
}

.controls {
    display: grid;
    grid-template-columns: 1.4fr 1fr 100px 160px auto;
    gap: 10px;
    margin-bottom: 12px;
    align-items: center;
}

.chk {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: .98rem;
    color: #274b7a;
}

.ui-input {
    height: 36px;
    border: 1px solid #cfe1ef;
    border-radius: 8px;
    padding: 0 10px;
    background: #f6fbff;
    color: #164b76;
}

.btn {
    height: 36px;
    padding: 0 14px;
    border-radius: 8px;
    border: 1px solid #b7e9fa;
    background: #f4fbff;
    color: #2568ad;
    font-weight: 600;
}

.recipes {
    display: grid;
    grid-template-columns: repeat(3, minmax(340px, 1fr)); /* max 3 per row */
    gap: 16px;
}
@media (max-width: 1400px) {
    .recipes { grid-template-columns: repeat(2, minmax(320px, 1fr)); }
}
@media (max-width: 840px) {
    .recipes { grid-template-columns: 1fr; }
}

.recipe {
    border: 1px solid #eef3f7;
    border-radius: 14px;
    padding: 16px 16px 18px;
    background: #fff;
}

.cover {
    width: 100%;
    height: 190px;
    object-fit: cover;
    border-radius: 10px;
    margin-bottom: 12px;
}

.recipe h3 {
    margin: 0 0 6px;
    color: #143b6a;
    font-size: 1.28rem;
    font-weight: 800;
}

.meta {
    color: #4a6f96;
    margin-bottom: 8px;
    display: flex;
    gap: 10px;
    font-size: 1rem;
}

.summary {
    color: #2c5886;
    margin: 8px 0 12px;
    font-size: 1rem;
    line-height: 1.45;
}

.block { margin-top: 10px; }

h4 {
    margin: 8px 0 6px;
    color: #244a7a;
    font-size: 1.06rem;
}

.ingredients {
    list-style: none;
    margin: 0;
    padding: 0;
    display: grid;
    gap: 8px;
    font-size: 1rem;
}

.ingredients li {
    display: grid;
    grid-template-columns: 110px auto auto;
    align-items: baseline;
    gap: 10px;
}

.qty {
    width: 110px;
    min-width: 110px;
    text-align: right;
    color: #186eb1;          /* no neon green */
    font-weight: 700;
}

.ing-name { color: #0f2f53; }

.opt {
    margin-left: auto;
    font-size: .9rem;
    color: #7a8ea6;
}

.shopping { margin-top: 10px; }
.lbl { color: #244a7a; margin-bottom: 6px; }

.chips {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.chip {
    padding: 5px 10px;
    border: 1px solid #e0edf7;
    border-radius: 999px;
    background: #f6fbff;
    font-size: .95rem;
    color: #1a4f84;
    text-decoration: none;
}

.steps {
    margin: 8px 0 0;
    padding-left: 18px;
    display: grid;
    gap: 10px;
    font-size: 1rem;
}

.step-head {
    display: flex;
    gap: 8px;
    align-items: center;
    margin-bottom: 4px;
}

.badge {
    background: #e9f6ff;
    color: #25548a;
    border: 1px solid #cfe1ef;
    border-radius: 6px;
    padding: 2px 6px;
    font-size: .9rem;
}

.step-time {
    color: #176e5d;  /* muted teal */
    font-weight: 700;
}

.step-text {
    line-height: 1.6;
    color: #163a5f;
}

.tips {
    margin-top: 10px;
    color: #297a57;
    font-style: italic;
}

.refs { margin-top: 10px; }

.empty-state {
    padding: 18px;
    text-align: center;
    color: #27598a;
    border: 1.5px dashed #cfe1ef;
    border-radius: 10px;
    background: #fff;
}

.error-note {
    margin-top: 10px;
    padding: 10px 12px;
    border: 1px solid #ffd6d6;
    background: #fff4f4;
    color: #8b1c1c;
    border-radius: 8px;
}

@media (max-width: 1000px) {
    .controls {
        grid-template-columns: 1fr 1fr 100px 1fr auto;
    }
}
@media (max-width: 720px) {
    .controls {
        grid-template-columns: 1fr 1fr;
    }
    .btn {
        grid-column: 1 / -1;
        justify-self: start;
    }
}
</style>
