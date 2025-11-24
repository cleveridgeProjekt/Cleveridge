<template>
    <div class="dialog-overlay" @click.self="close">
        <div class="dialog">
            <h3>{{ $t('allergy_dialog.title') }}</h3>

            <div class="no-allergy-card">
                <label class="na-row">
                    <input type="checkbox" v-model="noAllergy" @change="onNoAllergyToggle"/>
                    <span>{{ $t('allergy_dialog.no_allergy_label') }}</span>
                </label>
            </div>

            <div class="list-wrap" :class="{ disabled: noAllergy }">
                <div v-for="p in orderedProducts" :key="p.id" class="row">
                    <label class="row-label" :class="{ selected: selected.includes(p.id) }">
                        <input
                            type="checkbox"
                            :value="p.id"
                            v-model="selected"
                            :disabled="noAllergy"
                            @change="onToggle()"
                        />
                        <span>{{ p.name }}</span>
                    </label>
                </div>
            </div>

            <div class="dialog-actions">
                <button class="btn" @click="save" :disabled="busy">
                    <i class="fas fa-save"></i> {{ $t('allergy_dialog.save') }}
                </button>
                <button class="btn ghost" @click="close">
                    <i class="fas fa-times"></i> {{ $t('allergy_dialog.close') }}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";

export default {
    props: ["products"],
    emits: ["close", "saved"],
    data() {
        return {
            list: [],
            selected: [],
            noAllergy: false,
            busy: false,
        };
    },
    computed: {
        orderedProducts() {
            const sel = [], rest = [];
            for (const p of this.list) {
                (this.selected.includes(p.id) ? sel : rest).push(p);
            }
            const byName = (a, b) => (a.name || "").localeCompare(b.name || "");
            return [...sel.sort(byName), ...rest.sort(byName)];
        },
    },
    async mounted() {
        this.list = (this.products || []).map(p => ({id: p.id, name: p.name}));
        await this.load();
    },
    methods: {
        async load() {
            const {data} = await axios.get("/api/user/allergies");
            const ids = data.map(a => a.id ?? a.product_id).filter(Boolean);
            this.selected = ids;
            this.noAllergy = ids.length === 0;
        },
        onToggle() {
            if (this.selected.length > 0) this.noAllergy = false;
        },
        onNoAllergyToggle() {
            if (this.noAllergy) this.selected = [];
        },
        async save() {
            this.busy = true;
            try {
                const payload = {allergies: this.noAllergy ? [] : this.selected};
                await axios.post("/api/user/allergies", payload);
                this.$emit("saved");
                this.close();
            } finally {
                this.busy = false;
            }
        },
        close() {
            this.$emit("close");
        },
    },
};
</script>

<style scoped>
.dialog-overlay {
    position: fixed;
    inset: 0;
    background: #0b20384d;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 99;
}

.dialog {
    background: #fff;
    border-radius: 5px;
    max-width: 450px;
    width: calc(100% - 32px);
    padding: 26px 26px 18px;
    box-shadow: 0 18px 60px #0b2a5214;
}

.dialog h3 {
    font-weight: 700;
    font-size: 20px;
    margin: 0 0 14px;
    color: #0c5288;
}

.no-allergy-card {
    border: 1px solid #e6f1f9;
    background: #f9fcff;
    border-radius: 10px;
    padding: 10px 12px;
    margin-bottom: 10px;
}

.na-row {
    display: flex;
    gap: 10px;
    align-items: center;
    color: #23527c;
}

.list-wrap {
    max-height: 280px;
    overflow-y: auto;
    border: 1px solid #eaf2f9;
    border-radius: 10px;
    background: #fff;
    padding: 6px 0;
    margin-bottom: 14px;
}

.row {
    padding: 6px 12px;
}

.row + .row {
    border-top: 1px dotted #eef3f8;
}

.row-label {
    display: flex;
    gap: 10px;
    align-items: center;
}

.row-label.selected span {
    font-weight: 600;
    color: #0b4e85;
}

.dialog-actions {
    text-align: right;
}

.btn {
    display: inline-flex;
    gap: 8px;
    align-items: center;
    justify-content: center;
    height: 36px;
    padding: 0 16px;
    border-radius: 10px;
    border: 1px solid #b7e9fa;
    background: #f4fbff;
    color: #2568ad;
    cursor: pointer;
    font-weight: 600;
    margin-left: 8px;
}

.btn:hover {
    background: #eaf6ff;
    color: #134a81;
}

.btn.ghost {
    background: #fff;
    border-color: #d5e7f5;
    color: #2b5c8c;
}

.btn.ghost:hover {
    background: #f5fbff;
}

.btn:disabled {
    opacity: .6;
    cursor: not-allowed;
}

.list-wrap.disabled {
    background: #fff7f7;
    border-color: #f3d3d3;
}

.list-wrap.disabled .row-label {
    color: #b24a4a;
    cursor: not-allowed;
    opacity: .9;
}

.list-wrap.disabled .row-label::after {
    content: "✖";
    color: #c63b3b;
    font-weight: 700;
    margin-left: auto;
}

.list-wrap.disabled input[type="checkbox"] {
    accent-color: #c9c9c9;
}
</style>
