<template>
    <div>
        <PageHeader :title="$t('status.title')" icon="fal fa-snowflake">
            {{ $t('status.intro') }}
        </PageHeader>
    </div>

    <div class="p-4">
        <h1 class="text-xl font-bold mb-4">{{ $t('status.live') }}</h1>

        <div v-if="sensorData">
            <p>{{ $t('status.temperature') }}: {{ sensorData.temperature }} °C</p>
            <p>{{ $t('status.humidity') }}: {{ sensorData.humidity }} %</p>
            <p>{{ $t('status.last_updated') }}: {{ sensorData.timestamp }}</p>
        </div>
        <div v-else>
            <p>{{ $t('status.loading') }}</p>
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
