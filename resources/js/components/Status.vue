<script setup>
import { ref, onMounted } from 'vue'

const sensorData = ref({ status: 'loading...', temperature: null, humidity: null })

onMounted(async () => {
  const res = await fetch('/api/sensor')
  sensorData.value = await res.json()
})

</script>

<template>
  <div>
    <h2>Status: {{ sensorData.status }}</h2>
    <p>Temperature: {{ sensorData.temperature }}°C</p>
    <p>Humidity: {{ sensorData.humidity }}%</p>
  </div>
</template>

