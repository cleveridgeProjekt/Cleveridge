<template>
    <div>
        <PageHeader title="Receipts" icon="fas fa-receipt">
            <p>Hier findest du alle Rezepte und Ideen für deine gespeicherten Zutaten!</p>
        </PageHeader>
        <div style="padding:2em;">Coming soon: recipes will appear here.</div>
    </div>
</template>

<script>
import PageHeader from "./layout/PageHeader.vue";
export default {
    name: "Receipts",
    components: { PageHeader }
};
</script>
