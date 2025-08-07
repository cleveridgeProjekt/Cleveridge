<template>
  <PageHeader title="Einkaufsliste" icon="fal fa-shopping-cart">
      <span>
          Erstelle und verwalte deine intelligente Einkaufsliste.<br>
          Basierend auf fehlenden oder bald ablaufenden Artikeln schlägt das System vor, was du nachkaufen solltest, bearbeite Mengen und hake ab, was du schon hast.
      </span>
  </PageHeader>

    <ShoppingCarousel :visibleCarousel="visibleCarousel" @prev="prevCarousel" @next="nextCarousel" @detail="showProductDetail"/>

    <div class="shoppinglist-controls">
        <button class="btn-main" @click="openMustHaveDialog">
            <i class="fas fa-heart"></i> Wunschliste
        </button>
        <button class="btn-alt" @click="openAllergyDialog">
            <i class="fas fa-allergies"></i> Allergien
        </button>
    </div>
    <MustHaveDialog v-if="showMustHaveDialog" @close="closeDialogs"/>
    <AllergyDialog v-if="showAllergyDialog" @close="closeDialogs"/>


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
      <th><i class="fas fa-check"></i></th>
      <th><i class="fas fa-image"></i> Bild</th>
      <th><i class="fas fa-burger"></i> Produkt</th>
      <th> Menge</th>
      <th> Einheit</th>
      <th><i class="fas fa-trash-can"></i> Löschen</th>
        <th><i class="fas fa-pen"></i> Bearbeiten</th>
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
import ShoppingCarousel from "./layout/ShoppingCarousel.vue";

export default {
    name: "ShoppingList",
    components: { PageHeader, ShoppingCarousel },
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
        }
    },
    computed: {
        visibleCarousel() {
            if (!this.products.length) return [];
            let start = this.carouselIndex;
            let arr = [];
            for (let i = 0; i < 3; i++) {
                arr.push(this.products[(start + i) % this.products.length]);
            }
            return arr;
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
        },
        prevCarousel() {
            this.carouselIndex = (this.carouselIndex - 1 + this.products.length) % this.products.length;
        },
        nextCarousel() {
            this.carouselIndex = (this.carouselIndex + 1) % this.products.length;
        },
        showProductDetail(product) {
            alert(`Bald: Details für ${product.name} (z.B. Nährwerte, Kalorien, Allergene)`);
        },
    }
};
</script>

<style scoped>
.product-carousel {
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 20px auto 38px auto;
    max-width: 750px;
}
.carousel-track {
    display: flex;
    align-items: center;
    gap: 26px;
}
.carousel-item {
    width: 90px;
    height: 120px;
    background: #f4fbff;
    border-radius: 15px;
    box-shadow: 0 2px 12px 0 #d9e9fa33;
    display: flex;
    flex-direction: column;
    align-items: center;
    transition: transform .24s cubic-bezier(.44,.82,.34,.98);
    opacity: 0.65;
    cursor: pointer;
    padding: 6px 5px;
    border: 2px solid transparent;
}
.carousel-item img {
    width: 60px; height: 60px;
    object-fit: contain;
    margin-bottom: 7px;
}
.carousel-item.active {
    transform: scale(1.22);
    background: #fff;
    opacity: 1;
    z-index: 1;
    border-color: #4ac0fa;
}
.carousel-arrow {
    font-size: 34px;
    border: none;
    background: transparent;
    color: #1680b4;
    cursor: pointer;
    margin: 0 18px;
    padding: 4px 10px;
    user-select: none;
}
.prod-name {
    font-size: 0.98em;
    color: #25548a;
    text-align: center;
    font-weight: 600;
    line-height: 1.15;
}

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
