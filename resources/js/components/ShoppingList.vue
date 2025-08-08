<template>
    <PageHeader title="Einkaufsliste" icon="fal fa-shopping-cart">
    <span>
      Erstelle und verwalte deine intelligente Einkaufsliste.<br>
      Basierend auf fehlenden oder bald ablaufenden Artikeln schlägt das System vor, was du nachkaufen solltest, bearbeite Mengen und hake ab, was du schon hast.
    </span>
    </PageHeader>

    <ShoppingCarousel :visibleCarousel="visibleCarousel" @prev="prevCarousel" @next="nextCarousel" @detail="showProductDetail"/>

    <MustHaveDialog v-if="showMustHaveDialog" :units="units" @close="closeDialogs"/>
    <AllergyDialog v-if="showAllergyDialog" :products="products" @close="closeDialogs"/>

    <div class="shoppinglist-controls">
        <button class="btn-main" @click="openMustHaveDialog">
            <i class="fas fa-heart"></i> Zu meiner Wunschliste hinzufügen
        </button>
        <button class="btn-alt" @click="openAllergyDialog">
            <i class="fas fa-allergies"></i> Allergien festlegen
        </button>
    </div>

    <table class="shopping-list-table">
        <thead>
        <tr>
            <th><i class="fas fa-check"></i></th>
            <th><i class="fas fa-image"></i> Bild</th>
            <th><i class="fas fa-burger"></i> Produkt</th>
            <th>Menge</th>
            <th>Einheit</th>
            <th><i class="fas fa-trash-can"></i> Löschen</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="item in shoppingList.items" :key="item.id">
            <td>
                <input type="checkbox" v-model="item.checked_off" @change="toggleCheck(item)" />
            </td>
            <td>
                <img v-if="item.product && item.product.image_url"
                     :src="item.product.image_url"
                     alt=""
                     class="produkt-img"
                     style="width:60px;height:60px;border-radius:7px;background:#f7f7f7;"/>
            </td>
            <td>
                {{ item.product ? item.product.name : item.name }}
            </td>
            <td>
                <input type="number" min="1" v-model.number="item.quantity" @change="updateItem(item)" style="width:60px"/>
            </td>
            <td>
                <select v-model="item.unit" @change="updateItem(item)">
                    <option v-for="unit in units" :value="unit">{{ unit }}</option>
                </select>
            </td>
            <td>
                <button @click="deleteItem(item)" style="color:#b90000">🗑️</button>
            </td>
        </tr>
        </tbody>
    </table>
</template>

<script>
import PageHeader from "./layout/PageHeader.vue";
import ShoppingCarousel from "./layout/ShoppingCarousel.vue";
import axios from "axios";
import MustHaveDialog from "./MustHaveDialog.vue";
import AllergyDialog from "./AllergyDialog.vue";

export default {
    name: "ShoppingList",
    components: { PageHeader, ShoppingCarousel, MustHaveDialog, AllergyDialog },
    data() {
        return {
            shoppingList: { items: [] },
            products: [],
            selectedProductId: "",
            manualProduct: "",
            addQuantity: 1,
            addUnit: "Stück",
            units: ["Stück", "kg", "g", "l", "ml", "Packung"],
            carouselIndex: 0,
            showMustHaveDialog: false,
            showAllergyDialog: false,
        };
    },
    computed: {
        visibleCarousel() {
            if (!this.products.length) return [];
            const len = this.products.length;
            const center = this.carouselIndex;
            let idx = [
                (center - 2 + len) % len,
                (center - 1 + len) % len,
                center,
                (center + 1) % len,
                (center + 2) % len,
            ];
            return idx.map(i => this.products[i]);
        }
    },

    async mounted() {
        await this.fetchProducts();
        await this.fetchShoppingList();
    },
    methods: {
        openMustHaveDialog() { this.showMustHaveDialog = true; },
        openAllergyDialog() { this.showAllergyDialog = true; },
        closeDialogs() { this.showMustHaveDialog = false; this.showAllergyDialog = false; },

        async fetchProducts() {
            const { data } = await axios.get('/api/products');
            this.products = data;
        },
        async fetchShoppingList() {
            const { data } = await axios.get('/api/shopping-list');
            this.shoppingList = data;
        },
        async addItem() {
            let name = "";
            let product_id = null;
            if (this.manualProduct.trim() !== "") {
                name = this.manualProduct.trim();
            } else if (this.selectedProductId) {
                product_id = this.selectedProductId;
                name = this.products.find(p => p.id == product_id)?.name || "";
            } else {
                alert("Bitte Produkt wählen oder eingeben!");
                return;
            }
            const payload = {
                product_id,
                name,
                quantity: this.addQuantity,
                unit: this.addUnit,
            };
            const { data } = await axios.post('/api/shopping-list/items', payload);
            this.shoppingList.items.push(data);
            this.selectedProductId = "";
            this.manualProduct = "";
            this.addQuantity = 1;
            this.addUnit = "Stück";
        },
        async updateItem(item) {
            await axios.put(`/api/shopping-list/items/${item.id}`, {
                quantity: item.quantity,
                unit: item.unit
            });
        },
        async toggleCheck(item) {
            item.checked_off = !item.checked_off;
            await axios.put(`/api/shopping-list/items/${item.id}`, {
                checked_off: item.checked_off
            });
        },
        async deleteItem(item) {
            if (!confirm("Wirklich löschen?")) return;
            await axios.delete(`/api/shopping-list/items/${item.id}`);
            this.shoppingList.items = this.shoppingList.items.filter(i => i.id !== item.id);
        },
        prevCarousel() {
            this.carouselIndex = (this.carouselIndex - 1 + this.products.length) % this.products.length;
        },
        nextCarousel() {
            this.carouselIndex = (this.carouselIndex + 1) % this.products.length;
        },
        showProductDetail(product) {
            alert(`Bald: Details für ${product.name} (z.B. Nährwerte, Kalorien, Allergene)`);
        }
    }
};
</script>

<style scoped>
.shoppinglist-controls {
    display: grid;
    grid-template-columns: repeat(2, auto);
    gap: 12px;
    margin-bottom: 22px;
}
.btn-main,
.btn-alt {
    background: #f6fafe;
    color: #186eb1;
    border: 1.5px solid #bbe6fa;
    border-radius: 8px;
    padding: 9px 18px;
    font-size: 1rem;
    font-weight: 500;
    box-shadow: 0 1.5px 8px 0 #b4e3fa1a;
    cursor: pointer;
    transition: background .13s, color .13s;
}
.btn-main:hover,
.btn-alt:hover {
    background: #d7f1ff;
    color: #0a457a;
}

.add-product-section {
    display: grid;
    grid-template-columns: 200px 1fr 70px 110px 100px;
    gap: 10px;
    margin-bottom: 18px;
    align-items: center;
}
.shopping-list-table {
    width: 100%;
    margin-top: 12px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 20px 0 #b9e8fa0d;
    font-size: 1.05rem;
    border-collapse: separate;
    border-spacing: 0;
}
.shopping-list-table th,
.shopping-list-table td {
    padding: 13px 8px;
    text-align: center;
    border-bottom: 1px solid #f0f5fa;
}
.shopping-list-table th {
    background: #e9f6ff;
}
.shopping-list-table tr:last-child td {
    border-bottom: none;
}
</style>
