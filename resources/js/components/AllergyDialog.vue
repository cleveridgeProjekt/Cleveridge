<template>
    <div class="dialog-overlay" @click.self="close">
        <div class="dialog">
            <h3>Allergien auswählen</h3>
            <div style="max-height:250px;overflow-y:auto;margin-bottom:13px;">
                <div v-for="product in products" :key="product.id" class="allergy-row">
                    <label>
                        <input type="checkbox" v-model="selected" :value="product.id" :disabled="noAllergy"/>
                        {{ product.name }}
                    </label>
                </div>
                <div style="margin:15px 0;">
                    <label>
                        <input type="checkbox" v-model="noAllergy" @change="clearAll"/>
                        Ich habe keine Allergien auf diese Produkte
                    </label>
                </div>
            </div>
            <div class="dialog-actions">
                <button class="btn" @click="save">Speichern</button>
                <button class="btn" @click="close">Abbrechen</button>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";

export default {
    props: ["products"],
    emits: ["close"],
    data() {
        return {
            selected: [],
            noAllergy: false
        };
    },
    async mounted() {
        const {data} = await axios.get('/api/user/allergies');
        this.selected = data.map(a => a.product_id);
        this.noAllergy = data.length === 0;
    },
    methods: {
        clearAll() {
            if (this.noAllergy) this.selected = [];
        },
        async save() {
            if (this.noAllergy) {
                await axios.post('/api/user/allergies', {allergies: []});
            } else {
                await axios.post('/api/user/allergies', {allergies: this.selected});
            }
            this.close();
        },
        close() {
            this.$emit("close");
        }
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
    max-width: 430px;
    padding: 26px 24px 16px 24px;
    box-shadow: 0 5px 60px 0 #3360a911;
}

.dialog h3 {
    font-size: 1.21em;
    margin-bottom: 15px;
}

.allergy-row {
    padding: 5px 0;
}

.dialog-actions {
    text-align: right;
    margin-top: 6px;
}

.btn {
    background: #f4fbff;
    border-radius: 7px;
    border: 1px solid #b7e9fa;
    color: #2568ad;
    padding: 7px 18px;
    cursor: pointer;
    margin-left: 8px;
}

.btn:hover {
    background: #daf1ff;
    color: #054b7e;
}
</style>
