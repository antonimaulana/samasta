<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<style>
    .leaflet-container img,
    .leaflet-container svg,
    .leaflet-container canvas {
        max-width: none !important;
        max-height: none !important;
    }

    .taman-map-shell {
        position: relative;
        width: 100%;
        height: 480px;
        min-height: 480px;
        background: #e5e7eb;
    }

    @media (min-width: 640px) {
        .taman-map-shell {
            height: 560px;
            min-height: 560px;
        }
    }

    @media (min-width: 1024px) {
        .taman-map-shell {
            height: max(560px, calc(100vh - 12rem));
            min-height: 560px;
        }
    }

    .taman-map-shell .taman-map-canvas,
    .taman-map-shell .taman-map-canvas.leaflet-container {
        width: 100% !important;
        height: 100% !important;
        min-height: inherit;
        z-index: 0;
    }

    #map.taman-detail-map,
    #map.taman-detail-map.leaflet-container {
        width: 100% !important;
        height: 400px !important;
        min-height: 400px !important;
        z-index: 0;
    }
</style>
