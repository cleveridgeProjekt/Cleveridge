<template>
    <div class="dialog-overlay" @click.self="close">
        <div class="dialog">
            <h3>{{ $t('shopping.wishlist_dialog.title') }}</h3>

            <div class="add-row">
                <select class="ui-select" v-model="selectedProductId">
                    <option disabled value="">{{ $t('shopping.wishlist_dialog.select_placeholder') }}</option>
                    <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                </select>

                <input class="ui-input qty" type="number" min="1" v-model.number="addQuantity"/>

                <select class="ui-select" v-model="addUnit">
                    <option v-for="unit in units" :key="unit" :value="unit">{{ unit }}</option>
                </select>

                <button class="btn" @click="addItem" :disabled="!selectedProductId || busy">
                    <i class="fas fa-plus"></i> {{ $t('shopping.wishlist_dialog.add') }}
                </button>
            </div>

            <p class="hint">
                {{ $t('shopping.wishlist_dialog.hint') }}
            </p>

            <div class="dialog-actions">
                <button class="btn ghost" @click="close">{{ $t('shopping.wishlist_dialog.close') }}</button>
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
                const {data} = await axios.post("/api/shopping-list/items", payload);

                this.$emit("added", data);

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
    },
};
</script>

<style scoped>
.dialog-overlay {
    position: fixed;
    inset: 0;
    background: #0b20384d;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 99;
}

.dialog {
    background: #fff;
    border-radius: 5px;
    max-width: 720px;
    width: calc(100% - 32px);
    padding: 26px 26px 18px;
    box-shadow: 0 18px 60px #0b2a5214;
}

.dialog h3 {
    font-weight: bold;
    font-size: 20px;
    margin: 0 0 16px;
    color: #0c5288;
}

.add-row {
    display: grid;
    grid-template-columns: 1fr 96px 150px 150px;
    gap: 10px;
    align-items: center;
}

.ui-input,
.ui-select {
    width: 100%;
    height: 36px;
    border-radius: 10px;
    border: 1px solid #cfe1ef;
    background: #f6fbff;
    padding: 0 12px;
    font-size: 14.5px;
    color: #164b76;
    outline: none;
    transition: box-shadow .12s, border-color .12s, background .12s;
    appearance: none;
}

.ui-input:focus,
.ui-select:focus {
    border-color: #9ed2f5;
    box-shadow: 0 0 0 3px #bfe7ff66;
    background: #ffffff;
}

.ui-select:invalid,
.ui-select option[disabled] {
    color: #90a4b8;
}

.qty {
    text-align: center;
}

.btn {
    display: inline-flex;
    gap: 8px;
    align-items: center;
    justify-content: center;
    height: 36px;
    padding: 0 16px;
    border-radius: 10px;
    border: 1px solid #b7e9fa;
    background: #f4fbff;
    color: #2568ad;
    cursor: pointer;
    font-weight: 600;
}

.btn:hover {
    background: #eaf6ff;
    color: #134a81;
}

.btn:disabled {
    opacity: .6;
    cursor: not-allowed;
}

.btn.ghost {
    background: #fff;
    border-color: #d5e7f5;
    color: #2b5c8c;
}

.btn.ghost:hover {
    background: #f5fbff;
}

.hint {
    margin: 10px 0 18px;
    color: #6b7b8a;
    font-size: .95rem;
}

.dialog-actions {
    text-align: right;
}
</style>
