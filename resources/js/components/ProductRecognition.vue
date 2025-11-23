<template>
    <div>

        <PageHeader title="Produk­terkennung" icon="fal fa-barcode">
            Nutze die Kamera zur automatischen Produkterkennung. Erkennte Artikel kannst du direkt einem Kühlschrank
            hinzufügen.
        </PageHeader>


        <div class="card toolbar">
            <div class="status-area">
                <div v-if="waitingForPi" class="status-badge blink">
                    <i class="fas fa-circle-notch fa-spin"></i> Warte auf Raspberry Pi...
                </div>
                <div v-else class="status-badge ready">
                    <i class="fas fa-check-circle"></i> Bereit
                </div>
            </div>

            <div class="actions">
                <!-- Botón para pedir foto a la Raspberry Pi -->
                <button class="btn" @click="triggerPiCamera" :disabled="waitingForPi">
                    <i class="fas fa-camera"></i> Foto aufnehmen (Pi)
                </button>
            </div>
        </div>

        <div class="content-grid">

            <div class="photo-card card">
                <h3>📸 Letztes Foto</h3>
                <div class="image-wrap">
                    <img v-if="latestImage" :src="latestImage" alt="Raspberry Pi Capture" class="pi-photo"/>
                    <div v-else class="placeholder">
                        <i class="fas fa-image"></i>
                        <p>Noch kein Foto aufgenommen.</p>
                    </div>
                </div>
            </div>


            <div class="results-card card">
                <h3>🛒 Erkannte Produkte</h3>
                
                <div v-if="detectedProducts.length > 0" class="product-list">
                    <div v-for="(item, index) in detectedProducts" :key="index" class="product-row">
                        <div class="prod-info">
                            <span class="prod-name">{{ item }}</span>
                        </div>
                      
                        <button class="btn small ghost" @click="openAddModal(item)">
                            <i class="fas fa-plus"></i> Hinzufügen
                        </button>
                    </div>
                </div>
                
                <div v-else class="empty-state">
                    <p>Keine Produkte erkannt.</p>
                </div>
            </div>
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
                        <div class="chip">{{ modal.label }}</div>

                        <label class="lbl">Zugeordnetes Produkt</label>
                        <div v-if="modal.product">
                            <div class="product-line">
                                <img v-if="modal.product.image_url" :src="modal.product.image_url" class="thumb"/>
                                <div class="pname">{{ modal.product.name }}</div>
                            </div>
                            <button class="btn ghost small" @click="modal.product=null">Neu zuordnen…</button>
                        </div>
                        <div v-else class="relink">
                            <input class="ui-input" v-model="modal.search" placeholder="Produkt suchen…"/>
                            <div class="relink-list">
                                <div v-for="p in searchProducts(modal.search)" :key="p.id" class="relink-row" @click="modal.product = p">
                                    <img v-if="p.image_url" :src="p.image_url" class="thumb"/>
                                    <div class="pname">{{ p.name }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <label class="lbl">Ziel-Kühlschrank</label>
                        <select class="ui-select" v-model="selectedFridgeId">
                            <option v-for="f in fridges" :key="f.id" :value="f.id">{{ f.name || 'Ohne Name' }}</option>
                        </select>

                        <div class="split">
                            <div>
                                <label class="lbl">Menge</label>
                                <input class="ui-input" type="number" min="1" v-model.number="modal.qty"/>
                            </div>
                            <div>
                                <label class="lbl">Ablaufdatum</label>
                                <input class="ui-input" type="date" v-model="modal.expiry"/>
                            </div>
                        </div>

                        <div class="actions">
                            <button class="btn" :disabled="!modal.product || !selectedFridgeId || busyAdd" @click="addToFridge()">
                                <i class="fas fa-plus"></i> Hinzufügen
                            </button>
                            <button class="btn ghost" @click="closeModal">Abbrechen</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
import axios from 'axios'
import PageHeader from './layout/PageHeader.vue'

// Normalización de texto simple
const norm = s => (s || '').toLowerCase().replace(/[^a-z0-9]+/g, ' ').trim();

export default {
    name: 'ProductRecognitionPi',
    components: {PageHeader},
    data() {
        return {
            // Datos del Sistema
            products: [],
            fridges: [],
            selectedFridgeId: null,
            
            // Datos de la Cámara Pi
            latestImage: null,
            detectedProducts: [],
            waitingForPi: false,
            pollingTimer: null,

            // Datos del Modal (Añadir al refri)
            modal: {
                open: false,
                label: '',     // El texto que vino de Google Vision (ej: 'Banana')
                product: null, // El producto real de la base de datos
                search: '',
                qty: 1,
                expiry: '',
            },
            busyAdd: false,
        }
    },
    methods: {
        async init() {
            // Cargar datos iniciales (Productos y Refris)
            await Promise.all([this.fetchProducts(), this.fetchFridges()])
            if (!this.selectedFridgeId && this.fridges.length) this.selectedFridgeId = this.fridges[0].id
            
            // Iniciar el "Polling" para ver si ya hay foto
            this.fetchLatestData();
            this.startPolling();
        },

        // --- 1. COMUNICACIÓN CON EL BACKEND (Pi) ---
        
        async triggerPiCamera() {
            this.waitingForPi = true;
            try {
                // Llamamos a la ruta que definimos en web.php
                await axios.post('/camera/trigger');
                // No esperamos la respuesta aquí, la recibiremos via polling
            } catch (e) {
                console.error(e);
                alert("Error al enviar la orden a la Raspberry Pi");
                this.waitingForPi = false;
            }
        },

        async fetchLatestData() {
            try {

                const { data } = await axios.get('/api/camera/latest');
                
                if (data.image_url && data.image_url !== this.latestImage) {
                    this.latestImage = data.image_url;
                    this.detectedProducts = data.products || [];
                    this.waitingForPi = false; // ¡Llegó la foto!
                }
            } catch (e) {
                console.log("Polling...", e);
            }
        },

        startPolling() {

            this.pollingTimer = setInterval(this.fetchLatestData, 2000);
        },



        async fetchProducts() {
            try {
                const {data} = await axios.get('/api/products');
                this.products = Array.isArray(data) ? data : [];
            } catch { this.products = []; }
        },
        async fetchFridges() {
            try {
                const {data} = await axios.get('/api/fridges');
                this.fridges = Array.isArray(data) ? data : [];
            } catch { this.fridges = []; }
        },


        openAddModal(detectedLabel) {
            this.modal.open = true;
            this.modal.label = detectedLabel;
            this.modal.search = detectedLabel;
            this.modal.qty = 1;
            this.modal.expiry = '';
            
            // Intentar encontrar el producto automáticamente en la BD
            this.modal.product = this.matchProduct(detectedLabel);
        },

        matchProduct(label) {
            const L = norm(label);
            // Búsqueda exacta
            let p = this.products.find(x => norm(x.name) === L);
            // Búsqueda parcial
            if (!p) p = this.products.find(x => norm(x.name).includes(L));
            return p || null;
        },

        searchProducts(q) {
            const Q = norm(q);
            if (!Q) return this.products.slice(0, 30);
            return this.products.filter(p => norm(p.name).includes(Q)).slice(0, 30);
        },

        closeModal() {
            this.modal.open = false;
            this.modal.product = null;
        },

        async addToFridge() {
            if (!this.modal.product || !this.selectedFridgeId) return;
            this.busyAdd = true;
            try {
                const payload = {
                    product_id: this.modal.product.id,
                    quantity: this.modal.qty || 1,
                    expiry_date: this.modal.expiry || null,
                };
                await axios.post(`/api/fridges/${this.selectedFridgeId}/items`, payload);
                this.closeModal();
                alert("Produkt hinzugefügt! ✅");
            } catch (e) {
                alert("Fehler beim Hinzufügen ❌");
            } finally {
                this.busyAdd = false;
            }
        }
    },
    mounted() {
        this.init();
    },
    beforeUnmount() {
        if (this.pollingTimer) clearInterval(this.pollingTimer);
    }
}
</script>

<style scoped>

.card {
    background: #fff;
    border: 1px solid #eaeef4;
    border-radius: 12px;
    padding: 16px;
    box-shadow: 0 2px 14px #b9e8fa14;
    margin-bottom: 20px;
}

.toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.status-badge {
    padding: 8px 12px;
    border-radius: 20px;
    font-weight: bold;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.status-badge.ready { background: #c6f6d5; color: #2f855a; }
.status-badge.blink { background: #feebc8; color: #dd6b20; animation: pulse 1.5s infinite; }

.content-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}
@media (max-width: 768px) { .content-grid { grid-template-columns: 1fr; } }

.pi-photo {
    width: 100%;
    border-radius: 8px;
    border: 2px solid #333;
}

.placeholder {
    background: #edf2f7;
    height: 300px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    color: #a0aec0;
    border-radius: 8px;
    border: 2px dashed #cbd5e0;
}
.placeholder i { font-size: 40px; margin-bottom: 10px; }

.product-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.product-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f7fafc;
    padding: 10px;
    border-radius: 8px;
    border: 1px solid #edf2f7;
}

.prod-info { font-weight: bold; color: #2c3e50; font-size: 1.1em; }


.ui-select, .ui-input { height: 36px; border: 1px solid #cfe1ef; border-radius: 4px; padding: 0 10px; background: #f6fbff; width: 100%; margin-bottom: 10px; }
.btn { height: 36px; padding: 0 20px; border-radius: 4px; background: #3182ce; color: white; border: none; font-weight: bold; cursor: pointer; }
.btn:disabled { opacity: 0.6; cursor: not-allowed; }
.btn.ghost { background: #ebf8ff; color: #3182ce; }
.btn.small { height: 30px; font-size: 0.9rem; padding: 0 12px; }


.overlay { position: fixed; inset: 0; background: #0009; display: grid; place-items: center; z-index: 100; }
.modal { background: white; width: 90%; max-width: 800px; padding: 20px; border-radius: 12px; }
.modal-head { display: flex; justify-content: space-between; margin-bottom: 20px; }
.modal-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
@media (max-width: 600px) { .modal-grid { grid-template-columns: 1fr; } }
.chip { display: inline-block; background: #ebf8ff; color: #2b6cb0; padding: 4px 12px; border-radius: 16px; font-weight: bold; margin-bottom: 10px; }
.thumb { width: 50px; height: 50px; object-fit: cover; border-radius: 4px; }
.product-line { display: flex; gap: 10px; align-items: center; margin-bottom: 10px; }
.relink-list { max-height: 150px; overflow-y: auto; border: 1px solid #eee; margin-top: 5px; }
.relink-row { display: flex; align-items: center; padding: 5px; cursor: pointer; gap: 10px; }
.relink-row:hover { background: #f0f0f0; }

@keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.5; } 100% { opacity: 1; } }
</style>
```
