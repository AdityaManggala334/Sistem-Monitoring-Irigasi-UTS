<?php
// Memuat file koneksi database dan helper autentikasi (session, role, dll)
require_once 'koneksi.php';
require_once 'auth_helper.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<!-- META DATA DAN KONFIGURASI HALAMAN -->
<meta charset="UTF-8">  <!-- Set karakter UTF-8 untuk mendukung huruf Indonesia -->
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">  <!-- Responsif untuk mobile -->
<title>Peta Sensor — SM Irigasi</title>

<!-- CSS Framework Tailwind CDN -->
<script src="https://cdn.tailwindcss.com"></script>

<!-- Google Font: Plus Jakarta Sans -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    /* ROOT VARIABLES (Warna dan bayangan utama) */
    :root {
        --green-900: #064E3B;
        --green-500: #10B981;
        --green-400: #34D399;
        --border:    rgba(6,78,59,0.08);
        --shadow:    0 1px 3px rgba(6,78,59,0.06), 0 8px 24px rgba(6,78,59,0.07);
    }
    /* RESET CSS */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background: #F0FDF4; color: #0A2218;
        min-height: 100vh; display: flex; flex-direction: column;
    }

    /* STYLE NAVBAR */
    nav {
        background: rgba(6,78,59,0.97);
        backdrop-filter: blur(16px);
        border-bottom: 1px solid rgba(255,255,255,0.07);
        position: sticky; top: 0; z-index: 50;
    }
    .nav-inner {
        max-width: 1200px; margin: 0 auto; padding: 0 1rem;
        height: 56px; display: flex; align-items: center; justify-content: space-between;
    }
    .nav-brand { display: flex; align-items: center; gap: 8px; text-decoration: none; }
    .nav-brand-icon {
        width: 32px; height: 32px; border-radius: 10px;
        background: rgba(16,185,129,0.18); border: 1px solid rgba(52,211,153,0.25);
        display: flex; align-items: center; justify-content: center;
    }
    .nav-brand-text { font-size: .85rem; font-weight: 800; color: white; letter-spacing: -.02em; }
    .nav-brand-sub  { font-size: .6rem; font-weight: 600; color: rgba(255,255,255,.35); text-transform: uppercase; letter-spacing: .08em; }
    .nav-links { display: flex; align-items: center; gap: 2px; }
    .nav-link {
        display: flex; align-items: center; gap: 4px;
        padding: 5px 8px; border-radius: 8px;
        font-size: .75rem; font-weight: 500;
        color: rgba(255,255,255,.65); text-decoration: none; transition: all .18s;
    }
    .nav-link:hover  { background: rgba(255,255,255,.10); color: white; }
    .nav-link.active { background: rgba(16,185,129,.20); color: #34D399; font-weight: 600; }
    .nav-link.danger { background: rgba(239,68,68,.12); color: rgba(255,180,180,.9); }
    .nav-link.danger:hover { background: rgba(239,68,68,.22); }
    
    /* RESPONSIVE NAVBAR: Sembunyikan teks menu di mobile, tampilkan di tablet/desktop */
    @media (max-width: 640px) {
        .nav-link span { display: none; }  /* Sembunyikan teks menu di HP */
        .nav-link { padding: 6px 8px; }
        .nav-brand-text { display: none; }
        .nav-brand-sub { display: none; }
        .nav-inner { padding: 0 12px; }
    }
    @media (min-width: 641px) {
        .nav-link span { display: inline; }  /* Tampilkan teks menu di desktop */
        .nav-brand-text { display: block; }
        .nav-brand-sub { display: block; }
        .nav-inner { height: 64px; }
        .nav-brand-icon { width: 36px; height: 36px; }
    }

    /* KONTEN UTAMA */
    main { flex: 1; max-width: 1200px; margin: 0 auto; width: 100%; padding: 1rem; }
    @media (min-width: 640px) { main { padding: 2rem 1.5rem; } }

    /* KPI ROW (Card statistik) */
    .kpi-row { display: grid; grid-template-columns: repeat(2,1fr); gap: 0.75rem; margin-bottom: 1rem; }
    @media (min-width: 640px) { .kpi-row { grid-template-columns: repeat(4,1fr); gap: 1rem; margin-bottom: 1.5rem; } }
    
    .kpi-chip {
        background: white; border: 1px solid var(--border);
        border-radius: 14px; padding: 0.75rem;
        box-shadow: var(--shadow);
        display: flex; align-items: center; gap: 8px;
    }
    @media (min-width: 640px) { .kpi-chip { padding: 1rem 1.1rem; gap: 10px; } }
    
    .kpi-dot  { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
    @media (min-width: 640px) { .kpi-dot { width: 10px; height: 10px; } }
    
    .kpi-num  { font-size: 1.2rem; font-weight: 800; color: #022C22; line-height: 1; }
    @media (min-width: 640px) { .kpi-num { font-size: 1.6rem; } }
    
    .kpi-lbl  { font-size: 0.6rem; font-weight: 600; color: #94A3B8; margin-top: 2px; }
    @media (min-width: 640px) { .kpi-lbl { font-size: .72rem; } }

    /* LAYOUT MAP + SIDEBAR */
    .map-layout { display: grid; grid-template-columns: 1fr; gap: 1rem; align-items: start; }
    @media(min-width:900px){ .map-layout{ grid-template-columns: 1fr 300px; gap: 1.25rem; } }

    /* PANEL CARD */
    .panel {
        background: white; border: 1px solid var(--border);
        border-radius: 16px; overflow: hidden; box-shadow: var(--shadow);
    }
    .panel-head {
        padding: 0.75rem 1rem; border-bottom: 1px solid var(--border);
        display: flex; align-items: center; justify-content: space-between;
        font-size: 0.8rem; font-weight: 700; color: #022C22;
    }
    @media (min-width: 640px) { .panel-head { padding: .9rem 1.2rem; font-size: .85rem; } }
    
    .panel-sub { font-size: 0.65rem; font-weight: 500; color: #94A3B8; }
    @media (min-width: 640px) { .panel-sub { font-size: .72rem; } }

    /* MAP WRAPPER (tempat peta dan overlay sensor) */
    .map-wrap {
        position: relative; overflow: hidden;
        background: #1a2e1a;
    }
    .map-wrap img.aerial {
        width: 100%; display: block;
        max-height: 350px; object-fit: cover; object-position: center;
        opacity: 0.88;
    }
    /* Tinggi peta responsif */
    @media (min-width: 640px) { .map-wrap img.aerial { max-height: 450px; } }
    @media (min-width: 1024px) { .map-wrap img.aerial { max-height: 500px; } }
    
    .map-overlay {
        position: absolute; inset: 0;
        background: rgba(0,0,0,.18);
        pointer-events: none;  /* Agar overlay tidak menghalangi klik ke sensor */
    }
    .sensor-svg {
        position: absolute; inset: 0;
        width: 100%; height: 100%;
    }

    /* ANIMASI SENSOR (berdenyut seperti heartbeat) */
    @keyframes sensorPulse {
        0%   { r: 10; opacity: .7; }
        50%  { r: 18; opacity: .3; }
        100% { r: 10; opacity: .7; }
    }
    .sensor-ring { animation: sensorPulse 2.5s ease-in-out infinite; }

    /* SIDEBAR DETAIL SENSOR */
    .detail-empty {
        padding: 1.5rem 1rem; text-align: center; color: #CBD5E1;
    }
    .detail-card { padding: 0.75rem 1rem; }
    @media (min-width: 640px) { .detail-card { padding: 1.1rem 1.2rem; } }
    
    .detail-id   { font-size: 0.6rem; font-weight: 700; letter-spacing: .09em; text-transform: uppercase; color: #94A3B8; }
    .detail-name { font-size: 0.85rem; font-weight: 700; color: #022C22; margin: 4px 0 6px; }
    @media (min-width: 640px) { .detail-name { font-size: 1rem; margin: 4px 0 8px; } }
    
    .detail-pill {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 8px; border-radius: 20px;
        font-size: 0.65rem; font-weight: 700; margin-bottom: 10px;
    }
    @media (min-width: 640px) { .detail-pill { padding: 3px 10px; font-size: .7rem; margin-bottom: 12px; } }
    
    .detail-stats { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; }
    @media (min-width: 640px) { .detail-stats { gap: 8px; } }
    
    .detail-stat { background: #F8FAFC; border-radius: 10px; padding: 6px 8px; }
    @media (min-width: 640px) { .detail-stat { padding: 9px 10px; } }
    
    .detail-stat-val { font-size: 0.85rem; font-weight: 700; color: #022C22; }
    @media (min-width: 640px) { .detail-stat-val { font-size: 1rem; } }
    
    .detail-stat-lbl { font-size: 0.55rem; color: #94A3B8; font-weight: 500; margin-top: 2px; }
    @media (min-width: 640px) { .detail-stat-lbl { font-size: .65rem; } }

    /* DAFTAR SENSOR (list di sidebar) */
    .sensor-list-item {
        display: flex; align-items: center; gap: 8px;
        padding: 8px 1rem; border-bottom: 1px solid rgba(6,78,59,.04);
        cursor: pointer; transition: background .15s;
    }
    @media (min-width: 640px) { .sensor-list-item { padding: 9px 1.1rem; gap: 9px; } }
    
    .sensor-list-item:hover { background: #F0FDF4; }
    .sensor-list-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
    .sensor-list-id  { font-size: 0.6rem; font-weight: 700; color: #94A3B8; }
    .sensor-list-loc { font-size: 0.7rem; font-weight: 500; color: #022C22; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    @media (min-width: 640px) { .sensor-list-loc { font-size: .8rem; } }
    
    .sensor-pill {
        margin-left: auto; flex-shrink: 0;
        padding: 2px 6px; border-radius: 10px;
        font-size: 0.55rem; font-weight: 700;
    }
    @media (min-width: 640px) { .sensor-pill { padding: 2px 8px; font-size: .65rem; } }

    /* BADGE STATUS SENSOR */
    .sp-normal { background:#F0FDF4; color:#15803D; border:1px solid #BBF7D0; }
    .sp-rendah { background:#FFF7ED; color:#C2410C; border:1px solid #FED7AA; }
    .sp-tinggi { background:#EFF6FF; color:#1D4ED8; border:1px solid #BFDBFE; }
    .sp-kritis { background:#FEF2F2; color:#B91C1C; border:1px solid #FCA5A5; }

    /* ANIMASI LIVE DOT */
    @keyframes livePulse { 0%,100%{opacity:1} 50%{opacity:0.3} }
    .live-dot { display:inline-block; width:7px; height:7px; background:#10B981; border-radius:50%; animation:livePulse 2s infinite; }

    /* EFEK HOVER PADA TITIK SENSOR */
    .sensor-click { cursor: pointer; transition: transform .2s; }
    .sensor-click:hover { transform: scale(1.12); }
    
    /* LEGEND (keterangan warna) responsif */
    .legend-wrap {
        display: flex; gap: 0.75rem; padding: 0.6rem 1rem;
        border-top: 1px solid var(--border); background:#FAFAFA;
        flex-wrap: wrap; justify-content: center;
    }
    @media (min-width: 640px) { .legend-wrap { gap: 1.25rem; padding: .8rem 1.2rem; justify-content: flex-start; } }
    
    .legend-item {
        display: flex; align-items: center; gap: 4px;
        font-size: 0.6rem; font-weight: 600; color: #4B7563;
    }
    @media (min-width: 640px) { .legend-item { gap: 5px; font-size: .72rem; } }
    
    .legend-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
    @media (min-width: 640px) { .legend-dot { width: 8px; height: 8px; } }
    
    /* FOOTER */
    footer {
        padding: 0.75rem 1rem; background:#064E3B; text-align:center;
        font-size: 0.6rem; color: rgba(255,255,255,.35); margin-top: 1.5rem;
    }
    @media (min-width: 640px) { footer { padding: 1rem 1.5rem; font-size: .73rem; margin-top: 2rem; } }
</style>
</head>
<body>

<!-- ── NAVBAR (Navigasi Utama) ── -->
<nav>
    <div class="nav-inner">
        <!-- Logo / Brand -->
        <a href="index.php" class="nav-brand">
            <div class="nav-brand-icon">
                <svg width="16" height="16" viewBox="0 0 44 44" fill="none">
                    <path d="M22 7C22 7 13 18 13 24C13 29.52 17.03 34 22 34C26.97 34 31 29.52 31 24C31 18 22 7 22 7Z" fill="#10B981"/>
                    <line x1="18" y1="24" x2="26" y2="24" stroke="white" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
            </div>
            <div>
                <div class="nav-brand-text">SM Irigasi</div>
                <div class="nav-brand-sub">Monitoring</div>
            </div>
        </a>
        
        <!-- Menu Navigasi -->
        <div class="nav-links">
            <a href="index.php" class="nav-link">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                <span>Monitor</span>
            </a>
            <a href="peta.php" class="nav-link active">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/></svg>
                <span>Peta</span>
            </a>
            <a href="bps.php" class="nav-link">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                <span>BPS</span>
            </a>
            <a href="riwayat.php" class="nav-link">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <span>Riwayat</span>
            </a>
            <?php if ($role === 'administrator'): ?>
            <a href="dashboard.php" class="nav-link">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                <span>Admin</span>
            </a>
            <?php endif; ?>
            <a href="logout.php" class="nav-link danger">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                <span>Keluar</span>
            </a>
        </div>
    </div>
</nav>

<main>
    <!-- HEADER HALAMAN -->
    <div style="margin-bottom:1rem;">
        <h1 style="font-size:1.25rem;font-weight:800;color:#022C22;letter-spacing:-.03em;">Peta Sensor Interaktif</h1>
        <p style="font-size:0.7rem;color:#4B7563;margin-top:4px;">
            <span class="live-dot"></span>
            Visualisasi posisi 8 sensor aktif · update setiap 4 detik
        </p>
    </div>

    <!-- KPI ROW (Card statistik jumlah sensor per status) -->
    <div class="kpi-row">
        <div class="kpi-chip">
            <div class="kpi-dot" style="background:#10B981;"></div>
            <div><div class="kpi-num" id="cnt-normal">0</div><div class="kpi-lbl">Normal</div></div>
        </div>
        <div class="kpi-chip">
            <div class="kpi-dot" style="background:#F97316;"></div>
            <div><div class="kpi-num" id="cnt-rendah">0</div><div class="kpi-lbl">Rendah</div></div>
        </div>
        <div class="kpi-chip">
            <div class="kpi-dot" style="background:#3B82F6;"></div>
            <div><div class="kpi-num" id="cnt-tinggi">0</div><div class="kpi-lbl">Tinggi</div></div>
        </div>
        <div class="kpi-chip">
            <div class="kpi-dot" style="background:#EF4444;"></div>
            <div><div class="kpi-num" id="cnt-kritis">0</div><div class="kpi-lbl">Kritis</div></div>
        </div>
    </div>

    <!-- MAP + SIDEBAR (Layout 2 kolom di desktop, 1 kolom di mobile) -->
    <div class="map-layout">

        <!-- MAP CARD (Peta dan titik sensor) -->
        <div class="panel">
            <div class="panel-head">
                <span> Denah Jaringan Irigasi</span>
                <span class="panel-sub" id="waktu-peta">--:--:--</span>
            </div>
            <div class="map-wrap">
                <!-- Gambar peta dasar (aerial view) -->
                <img class="aerial" src="https://imgur.com/dmDQGaw.png" alt="Peta Irigasi Sawah" onerror="this.src='data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%20400%20300%22%3E%3Crect%20width%3D%22400%22%20height%3D%22300%22%20fill%3D%22%231a2e1a%22%2F%3E%3Ctext%20x%3D%22200%22%20y%3D%22150%22%20text-anchor%3D%22middle%22%20fill%3D%22%2334D399%22%20font-size%3D%2214%22%3EPeta%20Irigasi%3C%2Ftext%3E%3C%2Fsvg%3E'">
                <div class="map-overlay"></div>

                <!-- SVG OVERLAY SENSOR (Titik-titik sensor di atas peta) -->
                <svg class="sensor-svg" viewBox="0 0 100 100" preserveAspectRatio="none">
                    <defs>
                        <!-- Efek glow untuk titik sensor -->
                        <filter id="glow" x="-50%" y="-50%" width="200%" height="200%">
                            <feGaussianBlur stdDeviation="1.5" result="blur"/>
                            <feMerge><feMergeNode in="blur"/><feMergeNode in="SourceGraphic"/></feMerge>
                        </filter>
                    </defs>

                    <!-- Sensor SNS-01 (pintu air saluran induk atas kiri) -->
                    <g class="sensor-click" onclick="pilihSensor('SNS-01')">
                        <circle id="ring-SNS-01" cx="14" cy="10" r="4" fill="none" stroke="#10B981" stroke-width="1" opacity=".5" class="sensor-ring"/>
                        <circle id="dot-SNS-01" cx="14" cy="10" r="3.5" fill="#10B981" stroke="white" stroke-width="1.2" filter="url(#glow)"/>
                        <text x="14" y="10.8" text-anchor="middle" dominant-baseline="middle" font-size="2.2" fill="white" font-weight="700">S1</text>
                    </g>

                    <!-- Sensor SNS-02 (pintu air petak atas kiri-tengah) -->
                    <g class="sensor-click" onclick="pilihSensor('SNS-02')">
                        <circle id="ring-SNS-02" cx="26" cy="10" r="4" fill="none" stroke="#10B981" stroke-width="1" opacity=".5" class="sensor-ring"/>
                        <circle id="dot-SNS-02" cx="26" cy="10" r="3.5" fill="#10B981" stroke="white" stroke-width="1.2" filter="url(#glow)"/>
                        <text x="26" y="10.8" text-anchor="middle" dominant-baseline="middle" font-size="2.2" fill="white" font-weight="700">S2</text>
                    </g>

                    <!-- Sensor SNS-03 (pintu air petak atas tengah-kanan) status rendah -->
                    <g class="sensor-click" onclick="pilihSensor('SNS-03')">
                        <circle id="ring-SNS-03" cx="47" cy="10" r="4" fill="none" stroke="#F97316" stroke-width="1" opacity=".5" class="sensor-ring"/>
                        <circle id="dot-SNS-03" cx="47" cy="10" r="3.5" fill="#F97316" stroke="white" stroke-width="1.2" filter="url(#glow)"/>
                        <text x="47" y="10.8" text-anchor="middle" dominant-baseline="middle" font-size="2.2" fill="white" font-weight="700">S3</text>
                    </g>

                    <!-- Sensor SNS-04 (pintu air petak atas kanan) status tinggi -->
                    <g class="sensor-click" onclick="pilihSensor('SNS-04')">
                        <circle id="ring-SNS-04" cx="69" cy="10" r="4" fill="none" stroke="#3B82F6" stroke-width="1" opacity=".5" class="sensor-ring"/>
                        <circle id="dot-SNS-04" cx="69" cy="10" r="3.5" fill="#3B82F6" stroke="white" stroke-width="1.2" filter="url(#glow)"/>
                        <text x="69" y="10.8" text-anchor="middle" dominant-baseline="middle" font-size="2.2" fill="white" font-weight="700">S4</text>
                    </g>

                    <!-- Sensor SNS-05 (pintu air petak bawah kiri) -->
                    <g class="sensor-click" onclick="pilihSensor('SNS-05')">
                        <circle id="ring-SNS-05" cx="15" cy="55" r="4" fill="none" stroke="#10B981" stroke-width="1" opacity=".5" class="sensor-ring"/>
                        <circle id="dot-SNS-05" cx="15" cy="55" r="3.5" fill="#10B981" stroke="white" stroke-width="1.2" filter="url(#glow)"/>
                        <text x="15" y="55.8" text-anchor="middle" dominant-baseline="middle" font-size="2.2" fill="white" font-weight="700">S5</text>
                    </g>

                    <!-- Sensor SNS-06 (pintu air petak bawah tengah) status kritis -->
                    <g class="sensor-click" onclick="pilihSensor('SNS-06')">
                        <circle id="ring-SNS-06" cx="46" cy="50" r="4" fill="none" stroke="#EF4444" stroke-width="1" opacity=".5" class="sensor-ring"/>
                        <circle id="dot-SNS-06" cx="46" cy="50" r="3.5" fill="#EF4444" stroke="white" stroke-width="1.2" filter="url(#glow)"/>
                        <text x="46" y="50.8" text-anchor="middle" dominant-baseline="middle" font-size="2.2" fill="white" font-weight="700">S6</text>
                    </g>

                    <!-- Sensor SNS-07 (pintu air petak bawah kanan) -->
                    <g class="sensor-click" onclick="pilihSensor('SNS-07')">
                        <circle id="ring-SNS-07" cx="68" cy="50" r="4" fill="none" stroke="#10B981" stroke-width="1" opacity=".5" class="sensor-ring"/>
                        <circle id="dot-SNS-07" cx="68" cy="50" r="3.5" fill="#10B981" stroke="white" stroke-width="1.2" filter="url(#glow)"/>
                        <text x="68" y="50.8" text-anchor="middle" dominant-baseline="middle" font-size="2.2" fill="white" font-weight="700">S7</text>
                    </g>

                    <!-- Sensor SNS-08 (embung pojok kanan bawah) -->
                    <g class="sensor-click" onclick="pilihSensor('SNS-08')">
                        <circle id="ring-SNS-08" cx="85" cy="80" r="4" fill="none" stroke="#10B981" stroke-width="1" opacity=".5" class="sensor-ring"/>
                        <circle id="dot-SNS-08" cx="85" cy="80" r="3.5" fill="#10B981" stroke="white" stroke-width="1.2" filter="url(#glow)"/>
                        <text x="85" y="80.8" text-anchor="middle" dominant-baseline="middle" font-size="2.2" fill="white" font-weight="700">S8</text>
                    </g>
                </svg>
            </div>

            <!-- LEGEND (keterangan warna status sensor) -->
            <div class="legend-wrap">
                <div class="legend-item"><div class="legend-dot" style="background:#10B981;"></div>Normal</div>
                <div class="legend-item"><div class="legend-dot" style="background:#F97316;"></div>Rendah</div>
                <div class="legend-item"><div class="legend-dot" style="background:#3B82F6;"></div>Tinggi</div>
                <div class="legend-item"><div class="legend-dot" style="background:#EF4444;"></div>Kritis</div>
            </div>
        </div>

        <!-- SIDEBAR (Detail dan Daftar Sensor) -->
        <div style="display:flex;flex-direction:column;gap:1rem;">

            <!-- DETAIL SENSOR (Panel yang berisi data sensor yang dipilih) -->
            <div class="panel">
                <div class="panel-head"> Detail Sensor</div>
                <div id="detail-sensor">
                    <div class="detail-empty">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/><line x1="9" y1="3" x2="9" y2="18"/><line x1="15" y1="6" x2="15" y2="21"/></svg>
                        <p style="font-size:0.7rem;">Klik titik sensor pada peta</p>
                    </div>
                </div>
            </div>

            <!-- DAFTAR SENSOR (Semua sensor dalam bentuk list) -->
            <div class="panel">
                <div class="panel-head"> Semua Sensor</div>
                <div id="daftar-sensor" style="overflow-y:auto;max-height:260px;"></div>
            </div>

        </div>
    </div>
</main>

<!-- FOOTER -->
<footer>
    &copy; 2026 SM Irigasi — Universitas Sebelas Maret · Sistem Monitoring Irigasi Sawah
</footer>

<!-- JAVASCRIPT (Data Sensor, Update Real-Time, Interaksi) -->
<script>
// DATA SENSOR (8 titik sensor dengan nilai default)
var dataSensor = [
    { id:"SNS-01", lokasi:"Saluran Induk Ngidul",  debit:12.4, tma:42, suhu:26.8, lembap:68, status:"normal" },
    { id:"SNS-02", lokasi:"Percabangan Blok A",    debit:8.7,  tma:35, suhu:27.1, lembap:72, status:"normal" },
    { id:"SNS-03", lokasi:"Saluran Blok B",        debit:3.2,  tma:18, suhu:28.3, lembap:45, status:"rendah" },
    { id:"SNS-04", lokasi:"Bak Penampungan C1",    debit:18.9, tma:71, suhu:26.2, lembap:80, status:"tinggi" },
    { id:"SNS-05", lokasi:"Saluran Ngalor D",      debit:6.5,  tma:28, suhu:27.8, lembap:63, status:"normal" },
    { id:"SNS-06", lokasi:"Saluran Ngetan E",      debit:1.1,  tma:10, suhu:29.0, lembap:31, status:"kritis" },
    { id:"SNS-07", lokasi:"Saluran Petak 12",      debit:9.3,  tma:38, suhu:26.5, lembap:70, status:"normal" },
    { id:"SNS-08", lokasi:"Embung Ngulon",         debit:7.8,  tma:32, suhu:27.4, lembap:66, status:"normal" },
];

// Mapping warna untuk setiap status sensor
var WARNA = { normal:"#10B981", rendah:"#F97316", tinggi:"#3B82F6", kritis:"#EF4444" };
var LABEL = { normal:"Normal", rendah:"Rendah", tinggi:"Tinggi", kritis:"Kritis" };

// Fungsi untuk mendapatkan style badge berdasarkan status
function pillStyle(status) {
    var s = {
        normal: "background:#F0FDF4;color:#15803D;border:1px solid #BBF7D0;",
        rendah: "background:#FFF7ED;color:#C2410C;border:1px solid #FED7AA;",
        tinggi: "background:#EFF6FF;color:#1D4ED8;border:1px solid #BFDBFE;",
        kritis: "background:#FEF2F2;color:#B91C1C;border:1px solid #FCA5A5;"
    };
    return s[status] || s.normal;
}

// Fungsi untuk memperbarui tampilan waktu di peta
function updateWaktu() {
    var n = new Date();
    var pad = function(x){ return String(x).padStart(2,'0'); };
    var waktuElem = document.getElementById('waktu-peta');
    if (waktuElem) waktuElem.textContent = pad(n.getHours())+':'+pad(n.getMinutes())+':'+pad(n.getSeconds());
}

// Fungsi untuk merender daftar sensor di sidebar
function renderDaftar() {
    var cnt = { normal:0, rendah:0, tinggi:0, kritis:0 };
    var html = '';
    dataSensor.forEach(function(s) {
        cnt[s.status] = (cnt[s.status] || 0) + 1;
        html += '<div class="sensor-list-item" onclick="pilihSensor(\''+s.id+'\')">';
        html += '<div class="sensor-list-dot" style="background:'+WARNA[s.status]+';"></div>';
        html += '<div style="flex:1;min-width:0;">';
        html += '<div class="sensor-list-id">'+s.id+'</div>';
        html += '<div class="sensor-list-loc">'+s.lokasi+'</div>';
        html += '</div>';
        html += '<span class="sensor-pill" style="'+pillStyle(s.status)+'">'+LABEL[s.status]+'</span>';
        html += '</div>';
    });
    var daftarElem = document.getElementById('daftar-sensor');
    if (daftarElem) daftarElem.innerHTML = html;
    
    // Update KPI cards (jumlah sensor per status)
    ['normal','rendah','tinggi','kritis'].forEach(function(k) {
        var elem = document.getElementById('cnt-'+k);
        if (elem) elem.textContent = cnt[k] || 0;
    });
}

// Fungsi untuk menampilkan detail sensor yang dipilih (klik dari peta atau list)
function pilihSensor(id) {
    var s = dataSensor.find(function(x){ return x.id === id; });
    if (!s) return;
    var w = WARNA[s.status];
    var dot = '<svg width="6" height="6" viewBox="0 0 6 6"><circle cx="3" cy="3" r="3" fill="'+w+'"/></svg>';

    var html = '<div class="detail-card">';
    html += '<div class="detail-id">'+s.id+'</div>';
    html += '<div class="detail-name">'+s.lokasi+'</div>';
    html += '<span class="detail-pill" style="'+pillStyle(s.status)+'">'+dot+LABEL[s.status]+'</span>';
    html += '<div class="detail-stats">';
    html += '<div class="detail-stat"><div class="detail-stat-val">'+s.debit.toFixed(1)+'</div><div class="detail-stat-lbl">Debit L/dtk</div></div>';
    html += '<div class="detail-stat"><div class="detail-stat-val">'+s.tma+'</div><div class="detail-stat-lbl">TMA cm</div></div>';
    html += '<div class="detail-stat"><div class="detail-stat-val">'+s.suhu.toFixed(1)+'°</div><div class="detail-stat-lbl">Suhu C</div></div>';
    html += '<div class="detail-stat"><div class="detail-stat-val">'+s.lembap+'%</div><div class="detail-stat-lbl">Kelembapan</div></div>';
    html += '</div></div>';

    var detailElem = document.getElementById('detail-sensor');
    if (detailElem) detailElem.innerHTML = html;

    // Highlight titik sensor yang dipilih (stroke lebih tebal)
    dataSensor.forEach(function(x) {
        var el = document.getElementById('dot-'+x.id);
        if (el) el.setAttribute('stroke-width', x.id === id ? '2.5' : '1.2');
    });
}

// Fungsi untuk mensimulasikan update data sensor (random perubahan nilai)
function simulasiUpdate() {
    dataSensor.forEach(function(s) {
        // Ubah nilai debit, TMA, dan kelembapan secara random
        s.debit  = Math.max(0.5, s.debit + (Math.random() - 0.5));
        s.tma    = Math.max(5, s.tma + Math.round((Math.random() - 0.5) * 3));
        s.lembap = Math.min(100, Math.max(10, s.lembap + Math.round((Math.random() - 0.5) * 2)));
        
        // Tentukan status baru berdasarkan nilai TMA
        if      (s.tma < 15) s.status = 'kritis';
        else if (s.tma < 25) s.status = 'rendah';
        else if (s.tma > 65) s.status = 'tinggi';
        else                  s.status = 'normal';

        // Update warna titik sensor di peta sesuai status baru
        var dot = document.getElementById('dot-'+s.id);
        if (dot) dot.setAttribute('fill', WARNA[s.status]);
        var ring = document.getElementById('ring-'+s.id);
        if (ring) ring.setAttribute('stroke', WARNA[s.status]);
    });
    renderDaftar();  // Refresh daftar sensor di sidebar
}

// Jalankan render pertama kali
renderDaftar();

// Set interval untuk update data setiap 4 detik (4000 ms)
setInterval(simulasiUpdate, 4000);
// Set interval untuk update waktu setiap 1 detik (1000 ms)
setInterval(updateWaktu, 1000);
// Jalankan update waktu pertama kali
updateWaktu();
</script>

</body>
</html>
