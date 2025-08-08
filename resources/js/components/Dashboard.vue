<template>
    <div>
        <PageHeader title="Dashboard" icon="fal fa-th-large">
            <p>
                <b>Cleveridge</b> ist ein innovatives <b>Smart-Fridge-System</b>, das alltägliche Haushaltsaufgaben intelligent automatisiert und für mehr Übersicht und Komfort in deiner Küche sorgt.<br>
                Das Projekt kombiniert moderne Webtechnologie, Sensorik und künstliche Intelligenz, um den Inhalt deines Kühlschranks vollständig digital zu erfassen und zu verwalten.<br><br>
                Im Zentrum des Projekts steht eine mit dem <b>Raspberry Pi 5 verbundene Kamera</b>, die regelmäßig Bilder vom Inneren des Kühlschranks aufnimmt. Eine integrierte Bilderkennungs-KI analysiert diese Aufnahmen und identifiziert automatisch vorhandene Lebensmittel. Zusätzlich überwachen verbaute Sensoren in Echtzeit die Temperatur und Luftfeuchtigkeit, um optimale Lagerbedingungen sicherzustellen. Alle erfassten Informationen werden benutzerfreundlich über eine Weboberfläche visualisiert, die sowohl auf dem Smartphone als auch am PC verfügbar ist.
            </p>
        </PageHeader>
        <AnimatedTileGrid :tiles="tiles" />
    </div>
</template>

<script>
import PageHeader from './layout/PageHeader.vue'
import AnimatedTileGrid from './AnimatedTileGrid.vue'

export default {
    name: "Dashboard",
    components: { PageHeader, AnimatedTileGrid },
    data() {
        return {
            tiles: [
                {
                    label: "WHAT'S IN YOUR FRIDGE",
                    path: "/fridge",
                    img: "/media/tiles/fridge_shelves.webp",
                    desc: "Visuelle Darstellung aller Kühlschrankinhalte"
                },
                {
                    label: "EINKAUFLISTE",
                    path: "/shopping-list",
                    img: "/media/tiles/shopping_list.webp",
                    desc: "Intelligente Einkaufsliste basierend auf fehlenden Artikeln"
                },
                {
                    label: "FRIDGE STATUS",
                    path: "/status",
                    img: "/media/tiles/fridge_status.webp",
                    desc: "Temperatur- & Feuchtigkeitsanzeige in Echtzeit"
                },
                {
                    label: "ABLAUFDATUM",
                    path: "/expiry",
                    img: "/media/tiles/expiry_warning.webp",
                    desc: "Intelligente Einkaufsliste basierend auf fehlenden Artikeln"
                },
                {
                    label: "PRODUKTERKENNUNG",
                    path: "/product-recognition",
                    img: "/media/tiles/product_recognition.webp",
                    desc: "Automatische Produkterkennung durch Kamera & KI"
                },
                {
                    label: "REZEPTE",
                    path: "/receipts",
                    img: "/media/tiles/blueberry_receipt.png",
                    desc: "Alle gespeicherten Rezepte & Ideen für deine Zutaten"
                }
            ]
        }
    }
}
</script>
