<template>
    <div>
        <PageHeader title="Produk­terkennung" icon="fal fa-barcode">
            Nutze die Kamera zur automatischen Produkterkennung. Erkennte Artikel kannst du direkt einem Kühlschrank
            hinzufügen.
        </PageHeader>

        <div class="toolbar card">
            <div class="left">
                <button class="btn" @click="toggleCamera">
                    <i :class="isCameraOn ? 'fas fa-video-slash' : 'fas fa-video'"></i>
                    {{ isCameraOn ? 'Kamera ausschalten' : 'Kamera einschalten' }}
                </button>

                <span class="sep"></span>

                <label class="lbl">Ziel-Kühlschrank:</label>
                <select class="ui-select" v-model="selectedFridgeId">
                    <option v-for="f in fridges" :value="f.id" :key="f.id">{{ f.name || 'Ohne Name' }}</option>
                </select>
                <button class="btn ghost" @click="createDefaultFridge" v-if="!fridges.length">
                    <i class="fas fa-plus"></i> Kühlschrank anlegen
                </button>
            </div>

            <div class="right">
                <label class="lbl">Erkennungs-Schwelle:</label>
                <input class="ui-input small" type="number" min="0" max="1" step="0.05" v-model.number="minScore"/>
            </div>
        </div>

        <div class="cam-area card">
            <div class="cam-wrap">
                <video ref="video" autoplay playsinline muted></video>
                <canvas ref="canvas"></canvas>
            </div>
            <div class="cam-hint" v-if="!isCameraOn">
                <i class="fas fa-video-slash"></i> Kamera ist aus. Klicke auf „Kamera einschalten“.
            </div>
        </div>

        <div class="carousel-wrap">
            <button class="carousel-arrow" @click="scrollCarousel(-1)" aria-label="Zurück">
                <i class="fas fa-chevron-left"></i>
            </button>

            <div class="carousel" ref="carousel">
                <div v-for="c in cards" :key="c.id" class="card-item" @click="openCard(c)">
                    <img :src="c.image_url || '/media/products/default.png'" alt=""/>
                    <div class="name">{{ c.displayName }}</div>
                    <div class="conf">🔍 {{ (c.score * 100).toFixed(0) }}%</div>
                    <div v-if="!c.product" class="nomatch">Kein Produkt gefunden</div>
                    <button class="btn small" @click.stop="openCard(c)">
                        <i class="fas fa-plus"></i> Hinzufügen
                    </button>
                </div>
            </div>

            <button class="carousel-arrow" @click="scrollCarousel(1)" aria-label="Vor">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>

        <div v-if="modal.open" class="overlay" @click.self="closeModal">
            <div class="modal">
                <div class="modal-head">
                    <h3>Produkt hinzufügen</h3>
                    <button class="icon-btn" @click="closeModal"><i class="fas fa-times"></i></button>
                </div>

                <div class="modal-grid">
                    <div class="col">
                        <label class="lbl">Erkanntes Label</label>
                        <div class="chip">{{ modal.det?.label }} ({{ (modal.det?.score * 100).toFixed(0) }}%)</div>

                        <label class="lbl">Zugeordnetes Produkt</label>
                        <div v-if="modal.product">
                            <div class="product-line">
                                <img v-if="modal.product.image_url" :src="modal.product.image_url" class="thumb"/>
                                <div class="pname">{{ modal.product.name }}</div>
                            </div>
                            <div class="small-actions">
                                <button class="btn ghost small" @click="modal.product=null">Neu zuordnen…</button>
                                <button class="btn ghost small" @click="showNutrition(modal.product)">Nährwerte</button>
                            </div>
                        </div>
                        <div v-else class="relink">
                            <input class="ui-input" v-model="modal.search" placeholder="Produkt suchen…"/>
                            <div class="relink-list">
                                <div v-for="p in searchProducts(modal.search)" :key="p.id" class="relink-row"
                                     @click="modal.product = p">
                                    <img v-if="p.image_url" :src="p.image_url" class="thumb"/>
                                    <div class="pname">{{ p.name }}</div>
                                </div>
                            </div>
                            <div class="hint">Kein Treffer? Erstelle das Produkt auf der Produkte-Seite.</div>
                        </div>
                    </div>

                    <div class="col">
                        <label class="lbl">Ziel-Kühlschrank</label>
                        <select class="ui-select" v-model="selectedFridgeId">
                            <option v-for="f in fridges" :key="f.id" :value="f.id">{{ f.name || 'Ohne Name' }}</option>
                        </select>
                        <button class="btn ghost small" v-if="!fridges.length" @click="createDefaultFridge">Kühlschrank
                            anlegen
                        </button>

                        <div class="split">
                            <div>
                                <label class="lbl">Menge</label>
                                <input class="ui-input" type="number" min="1" v-model.number="modal.qty"/>
                            </div>
                            <div>
                                <label class="lbl">Ablaufdatum (optional)</label>
                                <input class="ui-input" type="date" v-model="modal.expiry"/>
                            </div>
                        </div>

                        <div class="actions">
                            <button class="btn" :disabled="!modal.product || !selectedFridgeId || busyAdd"
                                    @click="addToFridge()">
                                <i class="fas fa-plus"></i> Hinzufügen
                            </button>
                            <button class="btn ghost" @click="closeModal">Abbrechen</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <NutritionEtiquette v-if="nutrition.show" :productId="nutrition.product?.id" :productName="nutrition.product?.name" :show="nutrition.show" @close="nutrition.show=false"/>
    </div>
</template>

<script>
import axios from 'axios'
import PageHeader from './layout/PageHeader.vue'
import NutritionEtiquette from './NutritionEtiquette.vue'

let coco;
let tfLoaded = false;

const norm = s => (s || '').toLowerCase().replace(/[^a-z0-9]+/g, ' ').trim();

export default {
    name: 'ProductRecognition',
    components: {PageHeader, NutritionEtiquette},
    data() {
        return {
            products: [],
            fridges: [],
            selectedFridgeId: null,

            isCameraOn: false,
            model: null,
            timer: null,
            minScore: 0.6,
            detectionsMap: new Map(),
            eliminated: new Set(),

            modal: {
                open: false,
                det: null,
                product: null,
                search: '',
                qty: 1,
                expiry: '',
            },
            busyAdd: false,
            nutrition: {show: false, product: null},
        }
    },
    computed: {
        cards() {
            const arr = []
            for (const [label, entry] of this.detectionsMap.entries()) {
                if (this.eliminated.has(label)) continue
                if (entry.hits >= 3) arr.push(entry)
            }
            return arr.sort((a, b) => b.lastSeen - a.lastSeen)
        }
    },
    methods: {
        async init() {
            await Promise.all([this.fetchProducts(), this.fetchFridges()])
            if (!this.selectedFridgeId && this.fridges.length) this.selectedFridgeId = this.fridges[0].id
            await this.loadModel()
            await this.startCamera()
        },
        async fetchProducts() {
            try {
                const {data} = await axios.get('/api/products')
                this.products = Array.isArray(data) ? data : []
            } catch {
                this.products = []
            }
        },
        async fetchFridges() {
            try {
                const {data} = await axios.get('/api/fridges')
                this.fridges = Array.isArray(data) ? data : []
            } catch {
                this.fridges = []
            }
        },

        async startCamera() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({video: {facingMode: 'environment'}})
                const video = this.$refs.video
                video.srcObject = stream
                await video.play()
                this.isCameraOn = true
                this.startLoop()
            } catch (e) {
                console.error('Camera error', e)
                this.isCameraOn = false
                alert('Kamera konnte nicht gestartet werden (Browser-Berechtigungen?)')
            }
        },
        stopCamera() {
            const video = this.$refs.video
            const stream = video?.srcObject
            if (stream) {
                stream.getTracks().forEach(t => t.stop())
                video.srcObject = null
            }
            this.isCameraOn = false
            this.stopLoop()
            this.clearOverlay()
        },
        toggleCamera() {
            this.isCameraOn ? this.stopCamera() : this.startCamera()
        },

        async loadModel() {
            if (!tfLoaded) {
                await import('@tensorflow/tfjs')
                tfLoaded = true
            }
            if (!coco) {
                const mod = await import('@tensorflow-models/coco-ssd')
                coco = mod
            }
            if (!this.model) {
                this.model = await coco.load({base: 'lite_mobilenet_v2'})
            }
        },

        startLoop() {
            if (this.timer) return
            this.timer = setInterval(this.detectOnce, 800)
        },
        stopLoop() {
            if (this.timer) {
                clearInterval(this.timer);
                this.timer = null
            }
        },
        async detectOnce() {
            if (!this.model || !this.isCameraOn) return
            const video = this.$refs.video
            if (!video || video.readyState < 2) return

            let preds = []
            try {
                preds = await this.model.detect(video)
            } catch (e) {
                console.warn('Detect error', e);
                return
            }

            this.drawOverlay(preds)

            const now = Date.now()

            for (const [label, entry] of this.detectionsMap.entries()) {
                if (now - entry.lastSeen > 4000) this.detectionsMap.delete(label)
            }

            preds
                .filter(p => p.score >= this.minScore)
                .forEach(p => {
                    const label = norm(p.class)
                    if (!label || this.eliminated.has(label)) return

                    const entry = this.detectionsMap.get(label) || {
                        id: `${label}-${now}`,
                        label,
                        score: 0,
                        hits: 0,
                        lastSeen: 0,
                        product: null,
                        image_url: ''
                    }
                    entry.score = Math.max(entry.score, p.score)
                    entry.hits += 1
                    entry.lastSeen = now

                    if (!entry.product) {
                        entry.product = this.matchProduct(label)
                        entry.displayName = entry.product?.name || p.class
                        entry.image_url = entry.product?.image_url || this.placeholderImage(label)
                    }

                    this.detectionsMap.set(label, entry)
                })
        },

        matchProduct(label) {
            const L = norm(label)
            let p = this.products.find(x => norm(x.name) === L)
            if (p) return p
            p = this.products.find(x => norm(x.name).startsWith(L))
            if (p) return p
            p = this.products.find(x => norm(x.name).includes(L))
            return p || null
        },
        placeholderImage(label) {
            const map = {
                apple: '/media/products/red-apple.png',
                banana: '/media/products/banana.png',
                orange: '/media/products/orange.png',
                bottle: '/media/products/cow-milk.png',
                carrot: '/media/products/carrots.png',
                pizza: '/media/products/pizza.jpg',
            }
            return map[label] || '/media/products/default.png'
        },

        clearOverlay() {
            const ctx = this.$refs.canvas?.getContext('2d')
            if (!ctx) return
            ctx.clearRect(0, 0, this.$refs.canvas.width, this.$refs.canvas.height)
        },
        drawOverlay(preds) {
            const video = this.$refs.video
            const canvas = this.$refs.canvas
            if (!video || !canvas) return

            const w = video.videoWidth || 640
            const h = video.videoHeight || 480
            canvas.width = w
            canvas.height = h

            const ctx = canvas.getContext('2d')
            ctx.clearRect(0, 0, w, h)

            preds.forEach(p => {
                if (p.score < this.minScore) return
                const [x, y, width, height] = p.bbox
                ctx.lineWidth = 2
                ctx.strokeStyle = '#00A3FF'
                ctx.fillStyle = '#00A3FF'
                ctx.globalAlpha = 0.9
                ctx.strokeRect(x, y, width, height)
                const label = `${p.class} ${(p.score * 100).toFixed(0)}%`
                ctx.fillRect(x, y - 18, ctx.measureText(label).width + 10, 18)
                ctx.globalAlpha = 1
                ctx.fillStyle = '#fff'
                ctx.fillText(label, x + 4, y - 5)
            })
        },

        scrollCarousel(dir) {
            const el = this.$refs.carousel
            if (!el) return
            el.scrollBy({left: dir * 220, behavior: 'smooth'})
        },
        openCard(c) {
            this.modal.open = true
            this.modal.det = c
            this.modal.product = c.product || null
            this.modal.search = c.displayName || ''
            this.modal.qty = 1
            this.modal.expiry = ''
        },
        closeModal() {
            this.modal.open = false
            this.modal.det = null
            this.modal.product = null
            this.modal.search = ''
            this.modal.qty = 1
            this.modal.expiry = ''
        },
        searchProducts(q) {
            const Q = norm(q)
            if (!Q) return this.products.slice(0, 30)
            return this.products.filter(p => norm(p.name).includes(Q)).slice(0, 30)
        },

        async createDefaultFridge() {
            const {data} = await axios.post('/api/fridges', {name: 'Mein Kühlschrank'})
            this.fridges.push({...data, items: []})
            this.selectedFridgeId = data.id
        },
        async addToFridge() {
            if (!this.modal.product || !this.selectedFridgeId) return
            this.busyAdd = true
            try {
                const payload = {
                    product_id: this.modal.product.id,
                    quantity: this.modal.qty || 1,
                    expiry_date: this.modal.expiry || null,
                }
                await axios.post(`/api/fridges/${this.selectedFridgeId}/items`, payload)
                this.closeModal()
                const label = this.modal.det?.label
                if (label) this.eliminated.add(label)
            } finally {
                this.busyAdd = false
            }
        },
        showNutrition(p) {
            this.nutrition.product = p
            this.nutrition.show = true
        },
    },
    async mounted() {
        await this.init()
    },
    beforeUnmount() {
        this.stopCamera()
    }
}
</script>

<style scoped>
.card {
    background: #fff;
    border: 1px solid #eaeef4;
    border-radius: 12px;
    padding: 12px 16px;
    box-shadow: 0 2px 14px #b9e8fa14;
    margin-bottom: 14px;
}

.toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.toolbar .left {
    display: flex;
    align-items: center;
    gap: 10px;
}

.toolbar .right {
    display: flex;
    align-items: center;
    gap: 8px;
}

.lbl {
    font-weight: 700;
    color: #25548a;
    margin-right: 6px;
}

.sep {
    width: 1px;
    height: 22px;
    background: #e6eef6;
    margin: 0 6px;
}

.ui-select, .ui-input {
    height: 36px;
    border: 1px solid #cfe1ef;
    border-radius: 2px;
    padding: 0 10px;
    background: #f6fbff;
}

.ui-input.small {
    width: 120px;
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
    cursor: pointer;
}

.btn.ghost {
    background: #fff;
    border-color: #d5e7f5;
    color: #2b5c8c;
}

.btn.small {
    height: 32px;
    font-size: .95rem;
}

.cam-area {
    display: grid;
    gap: 8px;
}

.cam-wrap {
    position: relative;
    width: min(720px, 95vw);
    margin: auto;
}

video, canvas {
    width: 100%;
    height: auto;
    display: block;
    border-radius: 10px;
}

canvas {
    position: absolute;
    inset: 0;
    pointer-events: none;
}

.cam-hint {
    text-align: center;
    color: #6b7b8a;
}

.carousel-wrap {
    display: grid;
    grid-template-columns: 40px 1fr 40px;
    align-items: center;
    gap: 10px;
    margin: 16px 0;
}

.carousel-arrow {
    background: none;
    border: none;
    font-size: 24px;
    color: #1790d5;
    cursor: pointer;
}

.carousel {
    display: flex;
    gap: 14px;
    overflow: auto;
    scrollbar-width: none;
    scroll-behavior: smooth;
    padding: 4px;
}

.carousel::-webkit-scrollbar {
    display: none;
}

.card-item {
    min-width: 170px;
    max-width: 170px;
    background: #fff;
    border: 1px solid #eef3f7;
    border-radius: 12px;
    box-shadow: 0 2px 12px #b9e8fa14;
    padding: 10px;
    text-align: center;
}

.card-item img {
    width: 96px;
    height: 96px;
    object-fit: contain;
    display: block;
    margin: 0 auto 6px;
}

.card-item .name {
    font-weight: 700;
    color: #25548a;
}

.card-item .conf {
    color: #666;
    font-size: .9rem;
    margin-top: 2px;
}

.nomatch {
    color: #b15a00;
    font-weight: 700;
    margin: 6px 0;
}

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
    width: min(920px, 95vw);
    border-radius: 12px;
    padding: 14px;
    box-shadow: 0 10px 40px #0003;
}

.modal-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
}

.icon-btn {
    height: 32px;
    min-width: 32px;
    border: 1px solid #d9e7f2;
    border-radius: 8px;
    background: #fff;
    cursor: pointer;
}

.modal-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.lbl + .chip {
    display: inline-block;
    background: #f4fbff;
    border: 1px solid #bfe7ff;
    border-radius: 12px;
    padding: 4px 10px;
    color: #2568ad;
    font-weight: 700;
    margin-bottom: 8px;
}

.product-line {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 6px;
}

.thumb {
    width: 40px;
    height: 40px;
    object-fit: cover;
    border-radius: 4px;
    background: #f5f7fb;
}

.pname {
    font-weight: 700;
    color: #244;
}

.small-actions {
    display: flex;
    gap: 8px;
    margin-bottom: 8px;
}

.relink {
    display: grid;
    gap: 8px;
}

.relink-list {
    max-height: 200px;
    overflow: auto;
    border: 1px solid #eef3f7;
    border-radius: 8px;
}

.relink-row {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 8px;
    cursor: pointer;
}

.relink-row:hover {
    background: #f6fbff;
}

.hint {
    color: #6b7b8a;
    font-size: .9rem;
}

.split {
    display: grid;
    grid-template-columns: 120px 1fr;
    gap: 12px;
}

.actions {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
    margin-top: 10px;
}
</style>
