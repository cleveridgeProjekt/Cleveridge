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

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const data = ref(null)

const fetchSensorData = async () => {
  try {
    const response = await axios.get('/api/status')
    data.value = response.data
  } catch (err) {
    console.error('Failed to fetch sensor data:', err)
  }
}

onMounted(() => {
  fetchSensorData()
  setInterval(fetchSensorData, 5000) // refresh every 5 seconds
})
</script>
