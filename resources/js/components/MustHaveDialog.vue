<template>
    <div class="dialog-overlay" @click.self="close">
        <div class="dialog">
            <h3>Wunschliste bearbeiten</h3>
            <table>
                <thead>
                <tr>
                    <th>Bild</th>
                    <th>Produkt</th>
                    <th>Menge</th>
                    <th>Einheit</th>
                    <th>Löschen</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="item in mustHaveItems" :key="item.id">
                    <td>
                        <img :src="item.product.image_url" :alt="item.product.name"
                             style="width:42px;height:42px;border-radius:5px;background:#f7f7f7;">
                    </td>
                    <td>{{ item.product.name }}</td>
                    <td>
                        <input type="number" min="1" v-model.number="item.quantity" @change="updateItem(item)"
                               style="width:50px"/>
                    </td>
                    <td>
                        <select v-model="item.unit" @change="updateItem(item)">
                            <option v-for="unit in units" :value="unit">{{ unit }}</option>
                        </select>
                    </td>
                    <td>
                        <button @click="removeItem(item)" style="color:#b90000">🗑️</button>
                    </td>
                </tr>
                </tbody>
            </table>
            <div class="dialog-actions">
                <button class="btn" @click="close">Schließen</button>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";

export default {
    props: ["units"],
    emits: ["close"],
    data() {
        return {
            mustHaveItems: [],
        };
    },
    async mounted() {
        const {data} = await axios.get('/api/user/must-have');
        this.mustHaveItems = data;
    },
    methods: {
        close() {
            this.$emit("close");
        },
        async updateItem(item) {
            await axios.put(`/api/shopping-list/items/${item.id}`, {
                quantity: item.quantity,
                unit: item.unit,
            });
        },
        async removeItem(item) {
            await axios.delete(`/api/user/must-have/${item.product.id}`);
            this.mustHaveItems = this.mustHaveItems.filter(i => i.id !== item.id);
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
    max-width: 540px;
    padding: 28px 30px 18px 30px;
    box-shadow: 0 5px 60px 0 #3360a911;
    min-width: 330px;
}

.dialog h3 {
    font-size: 1.25em;
    margin-bottom: 17px;
}

table {
    width: 100%;
    margin-bottom: 10px;
}

th, td {
    text-align: center;
    padding: 9px 6px;
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
