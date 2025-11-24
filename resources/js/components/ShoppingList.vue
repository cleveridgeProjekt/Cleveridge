<template>
    <PageHeader :title="$t('shopping.title')" icon="fal fa-shopping-cart">
        <span v-html="$t('shopping.intro_html')"></span>
    </PageHeader>

    <ShoppingCarousel :visibleCarousel="visibleCarousel" @prev="prevCarousel" @next="nextCarousel"
                      @detail="showProductDetail"/>
    <NutritionEtiquette v-if="nutritionEtiquetteVisible" :productId="nutritionEtiquetteProductId"
                        :productName="nutritionEtiquetteProductName" :show="nutritionEtiquetteVisible"
                        @close="nutritionEtiquetteVisible = false"/>


    <MustHaveDialog v-if="showMustHaveDialog" :units="units" :products="products" @added="onItemAdded"
                    @close="onCloseMustHave"/>

    <AllergyDialog v-if="showAllergyDialog" :products="products" @saved="onAllergySaved" @close="closeDialogs"/>


    <div class="shoppinglist-controls">
        <button class="btn-main" @click="openMustHaveDialog">
            <i class="fas fa-heart"></i> {{ $t('shopping.controls.add_wishlist') }}
        </button>
        <button class="btn-alt" @click="openAllergyDialog">
            <i class="fas fa-allergies"></i> {{ $t('shopping.controls.set_allergies') }}
        </button>
    </div>

    <div v-if="isEmptyList" class="empty-state">
        <i class="fas fa-clipboard-list empty-icon"></i>
        <h4>{{ $t('shopping.empty.title') }}</h4>
        <p>{{ $t('shopping.empty.desc') }}</p>
    </div>

    <table v-else class="shopping-list-table">
        <thead>
        <tr>
            <th class="th-icon"><i class="fas fa-check"></i></th>
            <th class="th-icon"><i class="fas fa-image"></i> {{ $t('shopping.table.headers.image') }}</th>
            <th class="th-icon"><i class="fas fa-pizza-slice"></i> {{ $t('shopping.table.headers.product') }}</th>
            <th class="th-icon"><i class="fas fa-sort-numeric-up"></i> {{ $t('shopping.table.headers.quantity') }}</th>
            <th class="th-icon"><i class="fas fa-ruler-combined"></i> {{ $t('shopping.table.headers.unit') }}</th>
            <th class="th-icon"><i class="fas fa-trash"></i> {{ $t('shopping.table.headers.delete') }}</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="item in shoppingList.items" :key="item.id">
            <td><input type="checkbox" v-model="item.checked_off" @change="toggleCheck(item)" /></td>
            <td>
                <img v-if="item.product && item.product.image_url" :src="item.product.image_url" alt="" class="produkt-img" />
            </td>
            <td>{{ item.product ? item.product.name : item.name }}</td>
            <td>
                <input class="ui-input qty" type="number" min="1" v-model.number="item.quantity" @change="updateItem(item)" />
            </td>
            <td>
                <select class="ui-select unit" v-model="item.unit" @change="updateItem(item)">
                    <option v-for="unit in units" :key="unit" :value="unit">
                        {{ $t('common.unit.' + unit) }}
                    </option>
                </select>
            </td>
            <td>
                <button class="icon-btn danger" @click="deleteItem(item)" :title="$t('shopping.table.actions.delete')">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
        </tbody>
    </table>

    <div class="allergy-summary">
        <template v-if="allergyNames.length">
            <div class="allergy-title">{{ $t('shopping.allergy.title_has') }}</div>
            <ul class="allergy-list">
                <li v-for="n in allergyNames" :key="n">{{ n }}</li>
            </ul>
        </template>
        <template v-else>
            <div class="allergy-title">{{ $t('shopping.allergy.title_none') }}</div>
        </template>
    </div>

</template>

<script>
import PageHeader from "./layout/PageHeader.vue";
import ShoppingCarousel from "./layout/ShoppingCarousel.vue";
import NutritionEtiquette from "./NutritionEtiquette.vue";
import axios from "axios";
import MustHaveDialog from "./MustHaveDialog.vue";
import AllergyDialog from "./AllergyDialog.vue";

export default {
    name: "ShoppingList",
    components: {PageHeader, ShoppingCarousel, NutritionEtiquette, MustHaveDialog, AllergyDialog},
    data() {
        return {
            shoppingList: {items: []},
            products: [],
            selectedProductId: "",
            manualProduct: "",
            addQuantity: 1,
            addUnit: "Stück",
            units: ["Stück", "kg", "g", "l", "ml", "Packung"],
            carouselIndex: 0,
            nutritionEtiquetteProductId: null,
            nutritionEtiquetteProductName: "",
            nutritionEtiquetteVisible: false,
            showMustHaveDialog: false,
            showAllergyDialog: false,
            allergies: [],
            allergyNames: [],
            favoriteIds: [],
        };
    },
    computed: {
        isEmptyList() {
            return !this.shoppingList?.items || this.shoppingList.items.length === 0
        },
        carouselProducts() {
            if (!this.products.length) return [];
            const favSet = new Set(this.favoriteIds || []);
            const favs = this.products.filter(p => favSet.has(p.id));
            const rest = this.products.filter(p => !favSet.has(p.id));
            // show favorites first; if none selected, this is just all products
            return favs.length ? [...favs, ...rest] : this.products;
        },
        visibleCarousel() {
            const src = this.carouselProducts;
            if (!src.length) return [];
            const len = src.length;
            const center = this.carouselIndex % len;
            const idx = [
                (center - 2 + len) % len,
                (center - 1 + len) % len,
                center,
                (center + 1) % len,
                (center + 2) % len,
            ];
            return idx.map(i => src[i]);
        }
    },

    async mounted() {
        await this.fetchProducts();
        await this.fetchShoppingList();
        await this.fetchAllergies();
        this.loadFavorites();
    },
    methods: {
        openMustHaveDialog() {
            this.showMustHaveDialog = true;
        },
        onItemAdded(newItem) {
            this.shoppingList.items.push(newItem);
        },
        onCloseMustHave() {
            this.showMustHaveDialog = false;
        },
        openAllergyDialog() {
            this.showAllergyDialog = true;
        },
        closeDialogs() {
            this.showMustHaveDialog = false;
            this.showAllergyDialog = false;
        },
        loadFavorites() {
            try {
                const raw = localStorage.getItem('favorites:v1');
                const arr = JSON.parse(raw || '[]');
                this.favoriteIds = Array.isArray(arr) ? arr : [];
            } catch {
                this.favoriteIds = [];
            }
        },
        prevCarousel() {
            const len = this.carouselProducts.length || 1;
            this.carouselIndex = (this.carouselIndex - 1 + len) % len;
        },
        nextCarousel() {
            const len = this.carouselProducts.length || 1;
            this.carouselIndex = (this.carouselIndex + 1) % len;
        },

        async fetchProducts() {
            const {data} = await axios.get('/api/products');
            this.products = data;
        },
        async fetchShoppingList() {
            const {data} = await axios.get('/api/shopping-list');
            this.shoppingList = data;
        },
        async fetchAllergies() {
            const {data} = await axios.get('/api/user/allergies');
            this.allergies = data.map(a => a.id ?? a.product_id).filter(Boolean);
            const fromApi = data.map(a => a.name).filter(Boolean);
            this.allergyNames = fromApi.length
                ? fromApi
                : this.products
                    .filter(p => this.allergies.includes(p.id))
                    .map(p => p.name)
                    .sort((a, b) => a.localeCompare(b));
        },
        async updateItem(item) {
            await axios.put(`/api/shopping-list/items/${item.id}`, {
                quantity: item.quantity,
                unit: item.unit
            });
        },
        async toggleCheck(item) {
            await axios.put(`/api/shopping-list/items/${item.id}`, {
                checked_off: item.checked_off,
            });
        },
        async deleteItem(item) {
            if (!confirm(this.$t('shopping.prompts.confirm_delete'))) return
            await axios.delete(`/api/shopping-list/items/${item.id}`)
            this.shoppingList.items = this.shoppingList.items.filter(i => i.id !== item.id)
        },
        prevCarousel() {
            this.carouselIndex = (this.carouselIndex - 1 + this.products.length) % this.products.length;
        },
        nextCarousel() {
            this.carouselIndex = (this.carouselIndex + 1) % this.products.length;
        },
        showProductDetail(product) {
            this.nutritionEtiquetteProductId = product.id;
            this.nutritionEtiquetteProductName = product.name;
            this.nutritionEtiquetteVisible = true;
        },
        onAllergySaved() {
            this.fetchAllergies();
            this.showAllergyDialog = false;
        },
    }
};
</script>

<style scoped>
.allergy-summary {
    margin-top: 28px;
    background: #fff;
    border: 1px solid #eaeef4;
    border-radius: 10px;
    padding: 16px 18px;
    box-shadow: 0 1px 8px rgba(0, 0, 0, .03);
}

.allergy-title {
    font-weight: 700;
    font-size: 1.2rem;
    color: #25548a;
}

.allergy-list {
    list-style: disc;
    margin: 0;
    padding-left: 22px;
}

.allergy-list li {
    color: #111;
    font-weight: 700;
    line-height: 1.5;
    margin: 2px 0;
}

.shoppinglist-controls {
    display: grid;
    grid-template-columns: repeat(2, auto);
    gap: 12px;
    margin-bottom: 22px;
}

.empty-state {
    margin-top: 12px;
    background: #fff;
    border-radius: 12px;
    padding: 28px 24px;
    text-align: center;
    color: #27598a;
    box-shadow: 0 2px 20px 0 #b9e8fa0d;
    border: 1.5px dashed #cfe1ef;
}

.empty-state h4 {
    margin: 10px 0 6px 0;
    font-size: 1.18rem;
    color: #144b78;
}

.empty-state p {
    margin: 0 0 14px 0;
    color: #557a9a;
    font-size: .98rem;
}

.empty-icon {
    font-size: 38px;
    color: #69aee3;
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

.th-icon i {
    margin-right: 6px;
}

.produkt-img {
    display: block;
    margin: 0 auto;
    width: 60px;
    height: 60px;
    border-radius: 2px;
    background: #f7f7f7;
    object-fit: cover;
}

.ui-input,
.ui-select {
    width: 84px;
    height: 34px;
    border-radius: 9px;
    border: 1px solid #cfe1ef;
    background: #f6fbff;
    padding: 0 10px;
    font-size: 14px;
    color: #164b76;
    outline: none;
    transition: box-shadow .12s, border-color .12s, background .12s;
}

.ui-input:focus,
.ui-select:focus {
    border-color: #9ed2f5;
    box-shadow: 0 0 0 3px #bfe7ff66;
    background: #ffffff;
}

.qty {
    text-align: center;
    width: 64px;
}

.unit {
    width: 120px;
}

.icon-btn {
    height: 34px;
    min-width: 34px;
    border: 1px solid #d9e7f2;
    background: #fff;
    border-radius: 9px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.icon-btn:hover {
    background: #f4f9ff;
}

.icon-btn.danger {
    color: #b90000;
    border-color: #f1d2d2;
}

.icon-btn.danger:hover {
    background: #fff5f5;
}
</style>
