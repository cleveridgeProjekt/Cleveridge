<template>
  <div class="p-6">
    <h2 class="text-xl font-bold mb-4">Live Sensor Status</h2>
    <div v-if="loading">Loading...</div>
    <div v-else>
      <p>🌡️ Temperature: {{ data.temperature }} °C</p>
      <p>💧 Humidity: {{ data.humidity }} %</p>
      <p>🕒 Last updated: {{ data.timestamp }}</p>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'Status',
  data() {
    return {
      data: {},
      loading: true,
    }
  },
  methods: {
    async fetchData() {
      try {
        const res = await axios.get('/api/status')
        this.data = res.data
        this.loading = false
      } catch (err) {
        console.error('Error fetching sensor data:', err)
      }
    }
  },
  mounted() {
    this.fetchData()
    setInterval(this.fetchData, 5000) // fetch every 5 seconds
  }
}
</script>
