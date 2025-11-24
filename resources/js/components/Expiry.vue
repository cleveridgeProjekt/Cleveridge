<template>
    <div>
        <PageHeader :title="$t('expiry.title')" icon="fal fa-exclamation-triangle">
            {{ $t('expiry.intro') }}
        </PageHeader>

        <section class="card">
            <div class="controls">
                <label for="days" class="label">{{ $t('expiry.controls.label_days') }}</label>
                <input id="days" type="range" min="1" max="14" v-model.number="days" @input="onDaysInput" />
                <span class="hint">
          {{ days }} {{ days === 1 ? $t('expiry.controls.day_singular') : $t('expiry.controls.day_plural') }}
        </span>
                <button class="btn" @click="fetchData" :disabled="loading">
                    <i class="fas fa-sync-alt" :class="{ spin: loading }"></i> {{ $t('expiry.controls.refresh') }}
                </button>
            </div>

            <h3 class="section-title">{{ $t('expiry.soon.title') }}</h3>
            <div v-if="loading" class="empty-state">
                <i class="fas fa-spinner empty-icon fa-spin"></i>
                <h4>{{ $t('expiry.loading') }}</h4>
            </div>
            <div v-else-if="soonSorted.length === 0" class="empty-state">
                <i class="fas fa-check-circle empty-icon"></i>
                <h4>{{ $t('expiry.soon.ok_title') }}</h4>
                <p>
                    {{
                        days === 1
                            ? $t('expiry.soon.ok_desc_one', { days })
                            : $t('expiry.soon.ok_desc_other', { days })
                    }}
                </p>
            </div>
            <div v-else class="table-wrap">
                <table class="items-table">
                    <thead>
                    <tr>
                        <th>{{ $t('expiry.table.headers.image') }}</th>
                        <th>{{ $t('expiry.table.headers.product') }}</th>
                        <th>{{ $t('expiry.table.headers.fridge') }}</th>
                        <th>{{ $t('expiry.table.headers.quantity') }}</th>
                        <th>{{ $t('expiry.table.headers.expiry') }}</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="it in soonSorted" :key="it.id">
                        <td>
                            <img v-if="it.product?.image_url" :src="it.product.image_url" class="thumb" alt="" />
                        </td>
                        <td>{{ it.product?.name || '—' }}</td>
                        <td>{{ it.fridge?.name || $t('fridge.list.unnamed') }}</td>
                        <td>{{ it.quantity }}</td>
                        <td>
                            {{ formatDate(it.expiry_date) }}
                            <span class="badge soon">{{ $t('expiry.badge.soon') }}</span>
                        </td>
                        <td class="actions">
                            <button class="icon-btn danger" :title="$t('expiry.actions.consume')" @click="consume(it)">
                                <i class="fas fa-check"></i>
                            </button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <h3 class="section-title">{{ $t('expiry.expired.title') }}</h3>
            <div v-if="!loading && expiredSorted.length === 0" class="empty-state">
                <i class="fas fa-check-circle empty-icon"></i>
                <h4>{{ $t('expiry.expired.empty_title') }}</h4>
                <p>{{ $t('expiry.expired.empty_desc') }}</p>
            </div>
            <div v-else-if="loading" class="empty-state">
                <i class="fas fa-spinner empty-icon fa-spin"></i>
                <h4>{{ $t('expiry.loading') }}</h4>
            </div>
            <div v-else class="table-wrap">
                <table class="items-table">
                    <thead>
                    <tr>
                        <th>{{ $t('expiry.table.headers.image') }}</th>
                        <th>{{ $t('expiry.table.headers.product') }}</th>
                        <th>{{ $t('expiry.table.headers.fridge') }}</th>
                        <th>{{ $t('expiry.table.headers.quantity') }}</th>
                        <th>{{ $t('expiry.table.headers.expiry') }}</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="it in expiredSorted" :key="it.id">
                        <td>
                            <img v-if="it.product?.image_url" :src="it.product.image_url" class="thumb" alt="" />
                        </td>
                        <td>{{ it.product?.name || '—' }}</td>
                        <td>{{ it.fridge?.name || $t('fridge.list.unnamed') }}</td>
                        <td>{{ it.quantity }}</td>
                        <td>
                            {{ formatDate(it.expiry_date) }}
                            <span class="badge expired">{{ $t('expiry.badge.expired') }}</span>
                        </td>
                        <td class="actions">
                            <button class="icon-btn danger" :title="$t('expiry.actions.remove')" @click="consume(it)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="errorMsg" class="error-note">
                <i class="fas fa-exclamation-triangle"></i> {{ errorMsg }}
            </div>
        </section>
    </div>
</template>


<script>
import axios from 'axios'
import PageHeader from './layout/PageHeader.vue'

export default {
    name: 'Expiry',
    components: {PageHeader},

    data() {
        return {
            days: 3,
            loading: false,
            soon: [],
            expired: [],
            errorMsg: '',
            _debounce: null
        }
    },

    computed: {
        soonSorted() {
            const list = Array.isArray(this.soon) ? this.soon : []
            return [...list].sort((a, b) => +new Date(a?.expiry_date || 0) - +new Date(b?.expiry_date || 0))
        },
        expiredSorted() {
            const list = Array.isArray(this.expired) ? this.expired : []
            return [...list].sort((a, b) => +new Date(a?.expiry_date || 0) - +new Date(b?.expiry_date || 0))
        }
    },

    methods: {
        async fetchData() {
            this.loading = true
            this.errorMsg = ''
            try {
                const { data } = await axios.get('/api/expiry', { params: { days: this.days } })
                this.soon = Array.isArray(data?.soon) ? data.soon : []
                this.expired = Array.isArray(data?.expired) ? data.expired : []
            } catch (e) {
                this.errorMsg = this.$t('expiry.errors.fetch_failed')
            } finally {
                this.loading = false
            }
        },

        onDaysInput() {
            window.clearTimeout(this._debounce)
            this._debounce = window.setTimeout(this.fetchData, 250)
        },

        formatDate(val) {
            if (!val) return '–'
            const d = new Date(val)
            return d.toLocaleDateString()
        },

        async consume(item) {
            if (!confirm(this.$t('expiry.prompts.remove_confirm'))) return
            try {
                await axios.delete(`/api/fridge-items/${item.id}`)
                this.soon = this.soon.filter(i => i.id !== item.id)
                this.expired = this.expired.filter(i => i.id !== item.id)
            } catch (e) {
                this.errorMsg = this.$t('expiry.errors.remove_failed')
            }
        }
    },

    async mounted() {
        await this.fetchData()
    }
}
</script>

<style scoped>
.card {
    background: #fff;
    border: 1px solid #eaeef4;
    border-radius: 12px;
    padding: 16px;
    box-shadow: 0 2px 14px 0 #b9e8fa14;
}

.controls {
    display: grid;
    grid-template-columns: auto 1fr auto auto;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
}

@media (max-width: 700px) {
    .controls {
        grid-template-columns: 1fr 1fr;
    }

    .controls .btn {
        justify-self: start;
        grid-column: 1 / -1;
    }
}

.label {
    color: #25548a;
    font-weight: 600;
}

.hint {
    color: #25548a;
    opacity: .8;
}

.btn {
    height: 34px;
    padding: 0 12px;
    border-radius: 6px;
    border: 1px solid #b7e9fa;
    background: #f4fbff;
    color: #2568ad;
    font-weight: 600;
}

.spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.section-title {
    margin: 14px 0 8px;
    color: #25548a;
    font-size: 1.3rem;
    font-weight: 600;
}

.table-wrap {
    padding-inline: 18px;
}

.items-table {
    width: 100%;
    table-layout: fixed;
}

.items-table th:first-child,
.items-table td:first-child {
    padding-left: 12px;
}

.items-table th:last-child,
.items-table td:last-child {
    padding-right: 12px;
}

.section-title + .table-wrap {
    margin-top: 6px;
}
.items-table th, .items-table td {
    padding: 10px 8px;
    border-bottom: 1px solid #f0f5fa;
    text-align: left;
    white-space: nowrap;
}

.items-table th {
    background: #e9f6ff;
}

.thumb {
    width: 46px;
    height: 46px;
    object-fit: cover;
    border-radius: 4px;
    background: #f5f7fb;
}

.actions {
    text-align: right;
}

.icon-btn {
    height: 30px;
    min-width: 30px;
    border: 1px solid #d9e7f2;
    border-radius: 8px;
    background: #fff;
    cursor: pointer;
}

.icon-btn.danger {
    color: #b90000;
    border-color: #f1d2d2;
}

.badge {
    margin-left: 8px;
    padding: 3px 8px;
    border-radius: 10px;
    font-size: .85rem;
    border: 1px solid #e6eef6;
}

.badge.soon {
    color: #855b00;
    background: #fff9e6;
    border-color: #f3e2b8;
}

.badge.expired {
    color: #b90000;
    background: #fff5f5;
    border-color: #f4d1d1;
}

.empty-state {
    margin-top: 8px;
    background: #fff;
    border-radius: 12px;
    padding: 24px 18px;
    text-align: center;
    color: #27598a;
    box-shadow: 0 2px 20px 0 #b9e8fa0d;
    border: 1.5px dashed #cfe1ef;
}

.empty-state h4 {
    margin: 8px 0 4px;
    color: #144b78;
}

.empty-icon {
    font-size: 32px;
    color: #69aee3;
}

.error-note {
    margin-top: 10px;
    padding: 10px 12px;
    border: 1px solid #ffd6d6;
    background: #fff4f4;
    color: #8b1c1c;
    border-radius: 8px;
}
</style>
