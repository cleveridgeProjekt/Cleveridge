<template>
    <div>
        <PageHeader title="Fridge status" icon="fal fa-snowflake">
            Hier kannst du die Echtzeitdaten deiner Kühlschrank-Sensoren überwachen, wie Temperatur, Luftfeuchtigkeit und Gerätestatus.
        </PageHeader>
    </div>
    <div class="p-4">
        <h1 class="text-xl font-bold mb-4">🌡️ Live Sensor Data</h1>
        <div v-if="sensorData">
            <p>Temperature: {{ sensorData.temperature }} °C</p>
            <p>Humidity: {{ sensorData.humidity }} %</p>
            <p>Last updated: {{ sensorData.timestamp }}</p>
        </div>
        <div v-else>
            <p>Loading...</p>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import PageHeader from "./layout/PageHeader.vue";

export default {
    name: 'Status',
    components: {PageHeader},
    data() {
        return {
            sensorData: null,
            intervalId: null
        }
    },
    methods: {
        async fetchSensorData() {
            try {
                const response = await axios.get('/api/status')
                this.sensorData = response.data
            } catch (err) {
                console.error('Failed to fetch sensor data:', err)
            }
        }
    },
    mounted() {
        this.fetchSensorData()
        this.intervalId = setInterval(this.fetchSensorData, 5000)
    },
    beforeUnmount() {
        clearInterval(this.intervalId)
    }
}
</script>
