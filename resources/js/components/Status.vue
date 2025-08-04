<template>
  <div class="p-4">
    <h1 class="text-xl font-bold mb-4">🌡️ Live Sensor Data</h1>
    <div v-if="data">
      <p>Temperature: {{ data.temperature }} °C</p>
      <p>Humidity: {{ data.humidity }} %</p>
      <p>Last updated: {{ data.timestamp }}</p>
    </div>
    <div v-else>
      <p>Loading...</p>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'Status',
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
