<template>
    <div class="dialog-overlay" @click.self="close">
        <div class="dialog">
            <h3>Wunschliste bearbeiten</h3>

            <div class="add-row">
                <select v-model="selectedProductId">
                    <option disabled value="">Produkt wählen…</option>
                    <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                </select>
                <input type="number" min="1" v-model.number="addQuantity"/>
                <select v-model="addUnit">
                    <option v-for="unit in units" :key="unit" :value="unit">{{ unit }}</option>
                </select>
                <button class="btn" @click="addItem" :disabled="!selectedProductId">Hinzufügen</button>
            </div>

            <p style="margin:8px 0 20px 0;color:#6b7b8a;font-size:.95rem">
                Tipp: Du kannst mehrere Produkte nacheinander hinzufügen und das Fenster offen lassen.
            </p>

            <div class="dialog-actions">
                <button class="btn" @click="close">Schließen</button>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";

export default {
    props: ["units", "products"],
    emits: ["close", "added"],
    data() {
        return {
            selectedProductId: "",
            addQuantity: 1,
            addUnit: "Stück",
            busy: false,
        };
    },
    methods: {
        close() {
            this.$emit("close");
        },
        async addItem() {
            if (!this.selectedProductId) return;

            this.busy = true;
            try {
                const payload = {
                    product_id: this.selectedProductId,
                    quantity: this.addQuantity,
                    unit: this.addUnit,
                };
                const {data} = await axios.post('/api/shopping-list/items', payload);

                this.$emit('added', data);

                this.selectedProductId = "";
                this.addQuantity = 1;
                this.addUnit = "Stück";
            } catch (e) {
                console.error(e);
                alert("Konnte Produkt nicht hinzufügen.");
            } finally {
                this.busy = false;
            }
        },
    }
};
</script>

<style scoped>
.dialog-overlay {
    position: fixed;
    inset: 0;
    background: #1027484d;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 99;
}

.dialog {
    background: #fff;
    border-radius: 16px;
    max-width: 680px;
    padding: 28px 30px 18px 30px;
    box-shadow: 0 5px 60px 0 #3360a911;
    min-width: 330px;
}

.dialog h3 {
    font-size: 1.25em;
    margin-bottom: 17px;
}

.add-row {
    display: grid;
    grid-template-columns: 1fr 90px 120px 120px;
    gap: 10px;
    margin-bottom: 14px;
    align-items: center;
}

.dialog-actions {
    text-align: right;
}

.btn {
    background: #f4fbff;
    border-radius: 7px;
    border: 1px solid #b7e9fa;
    color: #2568ad;
    padding: 7px 18px;
    cursor: pointer;
}

.btn:hover {
    background: #daf1ff;
    color: #054b7e;
}
</style>
