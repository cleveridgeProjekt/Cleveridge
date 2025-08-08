<template>
    <div class="nutrition-overlay" @click.self="close">
        <div class="nutrition-modal">
            <button class="close-btn" @click="close">
                <i class="fas fa-xmark"></i>
            </button>
            <div v-if="nutrition">
                <div class="nutrition-label">
                    <div class="product-title">{{ props.productName }}</div>
                    <h2>Nutrition Facts</h2>
                    <div class="serving">
                        <b>Serving Size:</b> {{ nutrition.serving_size || '—' }}
                    </div>
                    <div class="calories">
                        <b>Calories</b> {{ nutrition.calories || 0 }}
                    </div>
                    <table class="nutri-table">
                        <tr>
                            <td>Fat</td>
                            <td>{{ nutrition.fat || 0 }} g</td>
                        </tr>
                        <tr>
                            <td>&emsp;Saturated</td>
                            <td>{{ nutrition.saturated_fat || 0 }} g</td>
                        </tr>
                        <tr>
                            <td>&emsp;Monounsaturated</td>
                            <td>{{ nutrition.monounsaturated_fat || 0 }} g</td>
                        </tr>
                        <tr>
                            <td>&emsp;Polyunsaturated</td>
                            <td>{{ nutrition.polyunsaturated_fat || 0 }} g</td>
                        </tr>
                        <tr>
                            <td>Carbohydrate</td>
                            <td>{{ nutrition.carbs || 0 }} g</td>
                        </tr>
                        <tr>
                            <td>&emsp;Sugar</td>
                            <td>{{ nutrition.sugar || 0 }} g</td>
                        </tr>
                        <tr>
                            <td>&emsp;Fiber</td>
                            <td>{{ nutrition.fiber || 0 }} g</td>
                        </tr>
                        <tr>
                            <td>Protein</td>
                            <td>{{ nutrition.protein || 0 }} g</td>
                        </tr>
                        <tr>
                            <td>Salt</td>
                            <td>{{ nutrition.salt || 0 }} mg</td>
                        </tr>
                    </table>
                    <div class="micro-nutrients">
                        <b>Vitamins/Minerals:</b> {{ nutrition.vitamins_minerals || '—' }}
                    </div>
                    <div class="allergens">
                        <b>Allergens:</b> {{ nutrition.allergens || '—' }}
                    </div>
                    <div class="common-uses">
                        <b>Common uses:</b> {{ nutrition.common_uses || '—' }}
                    </div>
                </div>
            </div>
            <div v-else>
                <em>No nutrition info available.</em>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from "vue";
import axios from "axios";

const props = defineProps({
    productId: Number,
    show: Boolean,
    productName: String,
});
const emit = defineEmits(['close']);

const nutrition = ref(null);

const close = () => emit('close');

const fetchNutrition = async () => {
    nutrition.value = null;
    if (!props.productId) return;
    try {
        const { data } = await axios.get(`/api/products/${props.productId}/nutrition`);
        nutrition.value = data;
    } catch (e) {
        nutrition.value = null;
    }
};

watch(() => props.productId, fetchNutrition, { immediate: true });
watch(() => props.show, (val) => { if(val) fetchNutrition(); });

onMounted(fetchNutrition);
</script>

<style scoped>
.nutrition-overlay {
    position: fixed;
    z-index: 99;
    inset: 0;
    background: rgba(0,0,0,0.88);
    display: flex;
    align-items: center;
    justify-content: center;
}

.nutrition-modal {
    background: #fff;
    border-radius: 5px;
    box-shadow: 0 5px 40px 0 #0004;
    padding: 30px 25px 30px 25px;
    min-width: 375px;
    max-width: 95vw;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
    font-family: 'Inter', 'Helvetica Neue', Arial, sans-serif;
}
.close-btn {
    position: absolute;
    top: 16px;
    right: 22px;
    background: #f3f3f3;
    border: none;
    border-radius: 50%;
    width: 38px;
    height: 38px;
    color: #222;
    cursor: pointer;
    z-index: 2;
    box-shadow: 0 2px 6px #0002;
    transition: background .13s;
    display: grid;
    place-items: center;
    padding: 0;
}
.close-btn:hover {
    background: #ffeaea;
}
.close-btn i {
    font-size: 1.3em;
    line-height: 1;
    display: block;
}
.nutrition-label {
    font-family: "Arial Narrow", Arial, sans-serif;
    width: 330px;
    border: 2.5px solid #111;
    border-radius: 5px;
    background: #fff;
    padding: 22px 22px 13px 22px;
    color: #121212;
    box-shadow: 0 2px 18px #7773;
}
.product-title {
    text-align: left;
    font-size: 1.8em;
    font-weight: 900;
    color: #111;
    letter-spacing: 0.01em;
    opacity: 1;
}
.nutrition-label h2 {
    text-align: left;
    font-size: 1.7em;
    margin: 0 0 8px 0;
    border-bottom: 2px solid #111;
    padding-bottom: 6px;
    letter-spacing: 0.03em;
}
.serving, .calories {
    margin: 7px 0 2px 0;
    font-size: 1.08em;
}
.calories {
    font-size: 1.45em;
    border-bottom: 2px solid #111;
    margin-bottom: 9px;
    padding-bottom: 5px;
}
.nutri-table {
    width: 100%;
    margin: 12px 0 0 0;
    font-size: 1.08em;
    border-collapse: collapse;
}
.nutri-table td {
    padding: 2px 0 2px 0;
    border-bottom: 1px solid #eaeaea;
}
.nutri-table tr:last-child td {
    border-bottom: none;
}
.micro-nutrients, .allergens, .common-uses {
    margin-top: 13px;
    font-size: 0.98em;
    color: #234;
}
</style>
