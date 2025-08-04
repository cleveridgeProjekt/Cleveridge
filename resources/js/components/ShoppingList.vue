<template>
  <PageHeader title="Einkaufsliste" icon="fal fa-shopping-cart">
      <span>
        Füge Produkte deiner Einkaufsliste hinzu, bearbeite Mengen und hake ab, was du schon hast.
      </span>
  </PageHeader>

  <div class="add-product-section">
    <select v-model="selectedProductId">
      <option disabled value="">Produkt auswählen...</option>
      <option v-for="p in products" :key="p.id" :value="p.id">
        {{ p.name }}
      </option>
    </select>
    <input v-model="manualProduct" placeholder="Oder neuen Artikel eingeben..."/>
    <input v-model.number="addQuantity" type="number" min="1" max="999" style="width:70px"/>
    <select v-model="addUnit">
      <option v-for="unit in units" :key="unit" :value="unit">{{ unit }}</option>
    </select>
    <button @click="addItem">Hinzufügen</button>
  </div>

  <table class="shopping-list-table">
    <thead>
    <tr>
      <th>✓</th>
      <th>Bild</th>
      <th>Produkt</th>
      <th>Menge</th>
      <th>Einheit</th>
      <th>Löschen</th>
    </tr>
    </thead>
    <tbody>
    <tr v-for="item in shoppingList.items" :key="item.id">
      <td>
        <input type="checkbox" v-model="item.checked_off" @change="toggleCheck(item)"/>
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
import axios from "axios";

export default {
    name: "ShoppingList",
    components: { PageHeader },
    data() {
        return {
            shoppingList: { items: [] },
            products: [],
            selectedProductId: "",
            manualProduct: "",
            addQuantity: 1,
            addUnit: "Stück",
            units: ["Stück", "kg", "g", "l", "ml", "Packung"]
        }
    },
    async mounted() {
        await this.fetchProducts();
        await this.fetchShoppingList();
    },
    methods: {
        async fetchProducts() {
            const {data} = await axios.get('/api/products');
            this.products = data;
        },
        async fetchShoppingList() {
            const {data} = await axios.get('/api/shopping-list');
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
                unit: this.addUnit
            };

            const {data} = await axios.post('/api/shopping-list/items', payload);
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
        }
    }
};
</script>

<style scoped>
.add-product-section {
    display: flex;
    gap: 10px;
    margin-bottom: 16px;
    align-items: center;
}

.shopping-list-table {
    width: 100%;
    margin-top: 10px;
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
