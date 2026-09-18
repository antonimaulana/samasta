<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover, user-scalable=no">
    <meta name="theme-color" content="#059669">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>{{ $profile['title'] }} — WebAR {{ config('app.name') }}</title>
    @include('layouts.partials.favicon')
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            background: #022c22;
            color: #ecfdf5;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        #ar-app {
            position: fixed;
            inset: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }
        #ar-live-camera {
            position: fixed;
            inset: 0;
            z-index: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            background: #022c22;
        }
        #ar-camera-fallback {
            position: fixed;
            inset: 0;
            z-index: 1;
            background-color: #022c22;
            background-size: cover;
            background-position: center;
        }
        #ar-camera-fallback::after {
            content: "";
            position: absolute;
            inset: 0;
            background: rgba(2, 44, 34, 0.72);
        }
        #ar-overlay {
            position: fixed;
            inset: 0;
            z-index: 10;
            display: flex;
            flex-direction: column;
            padding: max(12px, env(safe-area-inset-top)) 16px max(16px, env(safe-area-inset-bottom));
            background: linear-gradient(
                180deg,
                rgba(2, 44, 34, 0.55) 0%,
                rgba(2, 44, 34, 0.08) 38%,
                rgba(2, 44, 34, 0.08) 62%,
                rgba(2, 44, 34, 0.72) 100%
            );
            pointer-events: none;
            overflow: hidden;
        }
        .ar-pointer { pointer-events: auto; }
        .ar-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 12px;
        }
        .ar-eyebrow {
            margin: 0;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #6ee7b7;
            text-shadow: 0 1px 3px rgba(0,0,0,0.45);
        }
        .ar-title {
            margin: 4px 0 0;
            font-size: 18px;
            font-weight: 800;
            line-height: 1.25;
            color: #fff;
            text-shadow: 0 2px 8px rgba(0,0,0,0.55);
        }
        .ar-subtitle {
            margin: 4px 0 0;
            font-size: 13px;
            color: #d1fae5;
            text-shadow: 0 1px 4px rgba(0,0,0,0.45);
        }
        .ar-live-badge {
            display: none;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border-radius: 999px;
            border: 1px solid rgba(110, 231, 183, 0.45);
            background: rgba(16, 185, 129, 0.28);
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            white-space: nowrap;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }
        .ar-live-badge.is-on { display: inline-flex; }
        .ar-live-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #4ade80;
            animation: ar-pulse 1.4s ease-in-out infinite;
        }
        @keyframes ar-pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.55; transform: scale(0.85); }
        }
        .ar-card-wrap {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 0;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }
        .ar-card {
            width: 100%;
            max-width: 420px;
            padding: 16px;
            border-radius: 24px;
            border: 1px solid rgba(110, 231, 183, 0.38);
            background: rgba(6, 78, 59, 0.52);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.35), inset 0 0 0 1px rgba(167, 243, 208, 0.12);
            animation: ar-glow 3s ease-in-out infinite;
        }
        @keyframes ar-glow {
            0%, 100% { box-shadow: 0 12px 40px rgba(0,0,0,0.35), 0 0 24px rgba(16,185,129,0.18); }
            50% { box-shadow: 0 12px 40px rgba(0,0,0,0.35), 0 0 36px rgba(16,185,129,0.32); }
        }
        .ar-card-head {
            padding-bottom: 12px;
            margin-bottom: 12px;
            border-bottom: 1px solid rgba(110, 231, 183, 0.22);
        }
        .ar-card-head p {
            margin: 0;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #a7f3d0;
        }
        .ar-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .ar-stat {
            padding: 12px;
            border-radius: 16px;
            background: rgba(2, 44, 34, 0.48);
            border: 1px solid rgba(16, 185, 129, 0.18);
        }
        .ar-stat-wide { grid-column: 1 / -1; }
        .ar-stat dt {
            margin: 0;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #6ee7b7;
        }
        .ar-stat dd {
            margin: 6px 0 0;
            font-size: 14px;
            font-weight: 700;
            color: #fff;
            line-height: 1.35;
        }
        .ar-stat dd.ar-normal { font-weight: 500; color: #ecfdf5; }
        .ar-fasilitas {
            margin: 0;
            padding: 0;
            list-style: none;
        }
        .ar-fasilitas li {
            padding: 4px 0;
        }
        .ar-kondisi {
            flex-shrink: 0;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 700;
            border: 1px solid transparent;
        }
        .ar-kondisi-baik { background: rgba(34,197,94,0.22); color: #dcfce7; border-color: rgba(74,222,128,0.35); }
        .ar-kondisi-ringan { background: rgba(245,158,11,0.22); color: #ffedd5; border-color: rgba(251,191,36,0.35); }
        .ar-kondisi-berat { background: rgba(239,68,68,0.22); color: #fee2e2; border-color: rgba(248,113,113,0.35); }
        .ar-footer {
            margin-top: 12px;
            width: 100%;
            max-width: 420px;
            margin-left: auto;
            margin-right: auto;
        }
        .ar-btn {
            display: none;
            width: 100%;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 16px;
            border: none;
            border-radius: 16px;
            background: #059669;
            color: #fff;
            font-size: 16px;
            font-weight: 800;
            box-shadow: 0 8px 24px rgba(0,0,0,0.28);
        }
        .ar-btn.is-visible { display: inline-flex; }
        .ar-link {
            display: flex;
            width: 100%;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 10px;
            padding: 14px 16px;
            border-radius: 16px;
            border: 1px solid rgba(110, 231, 183, 0.42);
            background: rgba(2, 44, 34, 0.62);
            color: #ecfdf5;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }
        .ar-status {
            margin: 0 0 8px;
            text-align: center;
            font-size: 11px;
            line-height: 1.45;
            color: rgba(236, 253, 245, 0.88);
            text-shadow: 0 1px 3px rgba(0,0,0,0.45);
        }
        #ar-tap-start {
            position: fixed;
            inset: 0;
            z-index: 20;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: rgba(2, 44, 34, 0.82);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
        }
        #ar-tap-start.is-visible { display: flex; }
        #ar-tap-start button {
            width: 100%;
            max-width: 320px;
            padding: 18px 20px;
            border: 2px solid rgba(110, 231, 183, 0.5);
            border-radius: 18px;
            background: #059669;
            color: #fff;
            font-size: 17px;
            font-weight: 800;
        }
        .is-hidden { display: none !important; }
    </style>
</head>
<body>
@php
    $kondisi = $profile['kondisi'];
    $maintenance = $profile['maintenance'];
    $fasilitas = $profile['fasilitas'];
@endphp

<div id="ar-app">
    <video id="ar-live-camera" autoplay playsinline muted></video>

    <div id="ar-camera-fallback"
         @if ($profile['poster_url']) style="background-image:url('{{ $profile['poster_url'] }}')" @endif></div>

    <div id="ar-tap-start" class="ar-pointer">
        <button type="button" id="ar-tap-start-btn">📷 Ketuk untuk Mulai Live AR</button>
    </div>

    <div id="ar-overlay">
        <header class="ar-header ar-pointer">
            <div>
                <p class="ar-eyebrow">{{ config('app.name') }} Live AR</p>
                <h1 class="ar-title">{{ $profile['title'] }}</h1>
                <p class="ar-subtitle">{{ $profile['subtitle'] }}</p>
            </div>
            <span id="ar-live-badge" class="ar-live-badge">
                <span class="ar-live-dot"></span> Live
            </span>
        </header>

        <div class="ar-card-wrap ar-pointer">
            <div class="ar-card">
                <div class="ar-card-head">
                    <p>Kartu Hologram Profil</p>
                </div>

                <dl class="ar-grid">
                    <div class="ar-stat">
                        <dt>Luas</dt>
                        <dd>{{ $profile['luasan_formatted'] }} m²</dd>
                    </div>
                    <div class="ar-stat">
                        <dt>Kondisi</dt>
                        <dd>{{ $kondisi['emoji'] }} {{ $kondisi['label'] }}</dd>
                    </div>
                    <div class="ar-stat ar-stat-wide">
                        <dt>Lokasi</dt>
                        <dd class="ar-normal">{{ $profile['location_label'] }}</dd>
                    </div>
                    <div class="ar-stat ar-stat-wide">
                        <dt>Fasilitas Tersedia</dt>
                        <dd class="ar-normal">
                            @if ($fasilitas['count'] > 0)
                                <ul class="ar-fasilitas">
                                    @foreach ($fasilitas['items'] as $item)
                                        <li>{{ $item['nama'] }}</li>
                                    @endforeach
                                </ul>
                            @else
                                {{ $fasilitas['label'] }}
                            @endif
                        </dd>
                    </div>
                    <div class="ar-stat ar-stat-wide">
                        <dt>Pemeliharaan Terakhir</dt>
                        <dd class="ar-normal leading-relaxed">{{ $maintenance['summary_label'] }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="ar-footer ar-pointer">
            <button type="button" id="ar-camera-start-btn" class="ar-btn">📷 Aktifkan Kamera Live</button>
            <p id="ar-camera-status" class="ar-status">Membuka kamera…</p>
            <a href="{{ $profile['web_detail_url'] }}" class="ar-link">📄 Buka Web Detail {{ config('app.name') }}</a>
        </div>
    </div>
</div>

<script>
(function () {
    var video = document.getElementById('ar-live-camera');
    var fallback = document.getElementById('ar-camera-fallback');
    var startBtn = document.getElementById('ar-camera-start-btn');
    var tapLayer = document.getElementById('ar-tap-start');
    var tapBtn = document.getElementById('ar-tap-start-btn');
    var statusEl = document.getElementById('ar-camera-status');
    var liveBadge = document.getElementById('ar-live-badge');
    var stream = null;

    function setStatus(message) {
        if (statusEl) statusEl.textContent = message;
    }

    function showLiveState() {
        fallback.classList.add('is-hidden');
        video.classList.remove('is-hidden');
        startBtn.classList.remove('is-visible');
        tapLayer.classList.remove('is-visible');
        liveBadge.classList.add('is-on');
        setStatus('');
    }

    function showFallbackState(message, showButton) {
        fallback.classList.remove('is-hidden');
        video.classList.add('is-hidden');
        liveBadge.classList.remove('is-on');
        setStatus(message);
        if (showButton) {
            startBtn.classList.add('is-visible');
        } else {
            startBtn.classList.remove('is-visible');
        }
    }

    function showTapToStart() {
        tapLayer.classList.add('is-visible');
    }

    async function startLiveCamera() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            showFallbackState('Browser tidak mendukung kamera live.', false);
            return;
        }

        setStatus('Meminta izin kamera…');

        try {
            if (stream) {
                stream.getTracks().forEach(function (track) { track.stop(); });
            }

            stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: { ideal: 'environment' }, width: { ideal: 1920 }, height: { ideal: 1080 } },
                audio: false
            });

            video.srcObject = stream;
            video.setAttribute('playsinline', 'true');
            video.muted = true;

            try {
                await video.play();
                showLiveState();
            } catch (playError) {
                showTapToStart();
                setStatus('Ketuk tombol untuk memulai kamera live.');
            }
        } catch (error) {
            showFallbackState('Izinkan akses kamera untuk tampilan live AR.', true);
        }
    }

    function bindStart(el) {
        if (!el) return;
        el.addEventListener('click', function () {
            startLiveCamera();
        });
    }

    bindStart(startBtn);
    bindStart(tapBtn);

    document.addEventListener('visibilitychange', function () {
        if (document.hidden && stream) {
            stream.getTracks().forEach(function (track) { track.stop(); });
        } else if (!document.hidden) {
            startLiveCamera();
        }
    });

    startLiveCamera();
})();
</script>
</body>
</html>
