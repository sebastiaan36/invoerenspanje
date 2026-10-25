<script setup lang="ts">
import type { Map as LeafletMap } from 'leaflet';
import { onBeforeUnmount, onMounted, useTemplateRef } from 'vue';

interface Plaats {
    naam: string;
    lat: number;
    lng: number;
}

interface WerkgebiedData {
    accent: string;
    tiles: string;
    attribution: string;
    bounds: [[number, number], [number, number]];
    gebied: [number, number][];
    plaatsen: Plaats[];
}

const props = withDefaults(
    defineProps<{
        data: WerkgebiedData;
        height?: string;
        titel?: string;
    }>(),
    {
        height: '520px',
        titel: 'Ons werkgebied',
    },
);

const mapEl = useTemplateRef<HTMLDivElement>('mapEl');
let mapInstance: LeafletMap | null = null;

onMounted(async () => {
    if (!mapEl.value) {
        return;
    }

    const [{ default: L }] = await Promise.all([
        import('leaflet'),
        import('leaflet/dist/leaflet.css'),
    ]);

    const cfg = props.data;

    const map = L.map(mapEl.value, {
        scrollWheelZoom: false,
        zoomControl: true,
    });
    mapInstance = map;

    map.on('focus', () => map.scrollWheelZoom.enable());
    map.on('blur', () => map.scrollWheelZoom.disable());

    L.tileLayer(cfg.tiles, {
        attribution: cfg.attribution,
        maxZoom: 19,
    }).addTo(map);

    L.polygon(cfg.gebied, {
        color: cfg.accent,
        weight: 2,
        fillColor: cfg.accent,
        fillOpacity: 0.15,
    }).addTo(map);

    for (const plaats of cfg.plaatsen) {
        L.circleMarker([plaats.lat, plaats.lng], {
            radius: 5,
            color: '#ffffff',
            weight: 2,
            fillColor: cfg.accent,
            fillOpacity: 1,
        })
            .bindTooltip(plaats.naam, {
                permanent: true,
                direction: 'right',
                offset: [6, 0],
                className: 'wg-label',
            })
            .addTo(map);
    }

    map.fitBounds(cfg.bounds, { padding: [20, 20] });
});

onBeforeUnmount(() => {
    if (mapInstance) {
        mapInstance.remove();
        mapInstance = null;
    }
});
</script>

<template>
    <div>
        <div
            class="wg-wrap"
            :style="{ '--wg-accent': data.accent, '--wg-height': height }"
        >
            <div
                ref="mapEl"
                class="wg-map"
                role="region"
                :aria-label="`Kaart: ${titel}`"
            />
            <div class="wg-legend" aria-hidden="true">
                <strong>{{ titel }}</strong>
                <span></span>Costa del Sol &amp; binnenland
            </div>
        </div>

        <div v-if="data.plaatsen.length" class="mt-6">
            <h3 class="text-base font-semibold text-foreground">
                Plaatsen in ons werkgebied, onder meer:
            </h3>
            <ul
                class="mt-3 flex flex-wrap gap-x-6 gap-y-2 text-muted-foreground"
            >
                <li v-for="plaats in data.plaatsen" :key="plaats.naam">
                    {{ plaats.naam }}
                </li>
            </ul>
        </div>
    </div>
</template>

<style>
.wg-wrap {
    position: relative;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    border: 1px solid var(--border);
}

.wg-map {
    height: var(--wg-height, 520px);
    width: 100%;
}

@media (max-width: 600px) {
    .wg-map {
        height: 420px;
    }

    .wg-legend {
        display: none;
    }
}

.wg-wrap .leaflet-container {
    font-family: inherit;
}

.wg-wrap .leaflet-bar a {
    width: 44px;
    height: 44px;
    line-height: 44px;
    font-size: 22px;
}

.wg-wrap .leaflet-control-attribution {
    font-size: 14px;
}

.wg-legend {
    position: absolute;
    z-index: 1000;
    top: 16px;
    right: 16px;
    background: var(--card);
    color: var(--foreground);
    padding: 12px 16px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
    font-size: 14px;
    line-height: 1.5;
    max-width: 240px;
}

.wg-legend strong {
    display: block;
    font-size: 14px;
    margin-bottom: 4px;
}

.wg-legend span {
    display: inline-block;
    width: 12px;
    height: 12px;
    border-radius: 4px;
    background: var(--wg-accent);
    opacity: 0.55;
    margin-right: 8px;
    vertical-align: -1px;
}

.leaflet-tooltip.wg-label {
    background: transparent;
    border: none;
    box-shadow: none;
    padding: 0;
    color: var(--foreground);
    font-weight: 600;
    font-size: 14px;
    text-shadow:
        0 0 4px #fff,
        0 0 4px #fff,
        0 0 6px #fff;
}

.leaflet-tooltip.wg-label::before {
    display: none;
}
</style>
