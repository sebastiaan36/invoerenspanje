<script setup lang="ts">
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
let mapInstance: import('leaflet').Map | null = null;

onMounted(async () => {
    if (!mapEl.value) {
        return;
    }

    const [{ default: L }] = await Promise.all([
        import('leaflet'),
        import('leaflet/dist/leaflet.css'),
    ]);

    const cfg = props.data;

    const map = L.map(mapEl.value, { scrollWheelZoom: false, zoomControl: true });
    mapInstance = map;

    map.on('focus', () => map.scrollWheelZoom.enable());
    map.on('blur', () => map.scrollWheelZoom.disable());

    L.tileLayer(cfg.tiles, { attribution: cfg.attribution, maxZoom: 19 }).addTo(map);

    const stijl = {
        color: cfg.accent,
        weight: 2,
        fillColor: cfg.accent,
        fillOpacity: 0.2,
    };
    L.polygon(cfg.gebied, stijl).addTo(map);

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
    <div
        class="wg-wrap"
        :style="{ '--wg-accent': data.accent, '--wg-height': height }"
    >
        <div ref="mapEl" class="wg-map" />
        <div class="wg-legend">
            <strong>{{ titel }}</strong>
            <span></span>Costa del Sol &amp; binnenland
        </div>
    </div>
</template>

<style>
.wg-wrap {
    position: relative;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
    border: 1px solid rgba(0, 0, 0, 0.06);
}

.wg-map {
    height: var(--wg-height, 520px);
    width: 100%;
}

@media (max-width: 600px) {
    .wg-map {
        height: 420px;
    }
}

.wg-legend {
    position: absolute;
    z-index: 1000;
    left: 14px;
    bottom: 14px;
    background: #fbf7f1;
    color: #2b2b2b;
    padding: 12px 14px;
    border-radius: 10px;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.18);
    font-size: 13px;
    line-height: 1.5;
    max-width: 230px;
}

.wg-legend strong {
    display: block;
    font-size: 14px;
    margin-bottom: 6px;
}

.wg-legend span {
    display: inline-block;
    width: 12px;
    height: 12px;
    border-radius: 3px;
    background: var(--wg-accent);
    opacity: 0.55;
    margin-right: 7px;
    vertical-align: -1px;
}

.wg-label {
    background: transparent;
    border: none;
    box-shadow: none;
    font-weight: 600;
    font-size: 12px;
    text-shadow: 0 0 4px #fff, 0 0 4px #fff, 0 0 6px #fff;
}
</style>
