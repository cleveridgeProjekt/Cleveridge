
<template>
    <div>
        <PageHeader title="Fridge" icon="fal fa-snowflake">
            Hier siehst du alle Produkte, die aktuell in deinem Kühlschrank sind.
            <br>Du kannst Produkte hinzufügen oder entfernen, Ablaufdaten verwalten, nach bestimmten Lebensmitteln suchen.
            <br>Behalte den Überblick, damit du weniger Lebensmittel verschwendest!
        </PageHeader>
    </div>
    <div>
        <h1>Deine Kühlschränke</h1>
        <ul>
            <li v-for="fridge in fridges" :key="fridge.id">
                {{ fridge.name || 'Ohne Name' }}
                <ul>
                    <li v-for="item in fridge.items" :key="item.id">
                        {{ item.product?.name }} - Menge: {{ item.quantity }}
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</template>

<script>
import PageHeader from "./layout/PageHeader.vue";
import axios from 'axios';

export default {
    name: 'Fridge',
    components: { PageHeader },
    data() {
        return {
            fridges: []
        }
    },
    methods: {
        async fetchFridges() {
            try {
                const { data } = await axios.get('/api/fridges')
                this.fridges = data
            } catch (e) {
                this.fridges = []
            }
        }
    },
    mounted() {
        this.fetchFridges()
    }
}
</script>
