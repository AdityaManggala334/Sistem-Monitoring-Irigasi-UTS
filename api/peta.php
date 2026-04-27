<?php
require_once 'koneksi.php';

// Ambil parameter dari URL path
$path = $_SERVER['PATH_INFO'] ?? '';
$segments = explode('/', trim($path, '/'));

if (count($segments) >= 2) {
    $user_id = $segments[0];
    $username = urldecode($segments[1]);
    $nama_depan = urldecode($segments[2] ?? '');
    $nama_belakang = urldecode($segments[3] ?? '');
    $role = $segments[4] ?? 'petani';
    $namaDepan = htmlspecialchars($nama_depan ?: $username);
} else {
    header("Location: login.php");
    exit();
}
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Peta Sensor — SM Irigasi</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    :root {
        --green-900: #064E3B;
        --green-500: #10B981;
        --green-400: #34D399;
        --border:    rgba(6,78,59,0.08);
        --shadow:    0 1px 3px rgba(6,78,59,0.06), 0 8px 24px rgba(6,78,59,0.07);
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background: #F0FDF4; color: #0A2218;
        min-height: 100vh; display: flex; flex-direction: column;
    }

    /* ── Navbar */
    nav {
        background: rgba(6,78,59,0.97);
        backdrop-filter: blur(16px);
        border-bottom: 1px solid rgba(255,255,255,0.07);
        position: sticky; top: 0; z-index: 50;
    }
    .nav-inner {
        max-width: 1200px; margin: 0 auto; padding: 0 1.5rem;
        height: 64px; display: flex; align-items: center; justify-content: space-between;
    }
    .nav-brand { display: flex; align-items: center; gap: 10px; text-decoration: none; }
    .nav-brand-icon {
        width: 36px; height: 36px; border-radius: 10px;
        background: rgba(16,185,129,0.18); border: 1px solid rgba(52,211,153,0.25);
        display: flex; align-items: center; justify-content: center;
    }
    .nav-brand-text { font-size: .95rem; font-weight: 800; color: white; letter-spacing: -.02em; }
    .nav-brand-sub  { font-size: .65rem; font-weight: 600; color: rgba(255,255,255,.35); text-transform: uppercase; letter-spacing: .08em; }
    .nav-links { display: flex; align-items: center; gap: 4px; }
    .nav-link {
        display: flex; align-items: center; gap: 6px;
        padding: 6px 13px; border-radius: 9px;
        font-size: .82rem; font-weight: 500;
        color: rgba(255,255,255,.65); text-decoration: none; transition: all .18s;
    }
    .nav-link:hover  { background: rgba(255,255,255,.10); color: white; }
    .nav-link.active { background: rgba(16,185,129,.20); color: #34D399; font-weight: 600; }
    .nav-link.danger { background: rgba(239,68,68,.12); color: rgba(255,180,180,.9); }
    .nav-link.danger:hover { background: rgba(239,68,68,.22); }

    /* ─ Main */
    main { flex: 1; max-width: 1200px; margin: 0 auto; width: 100%; padding: 2rem 1.5rem; }

    /* ─ KPI row */
    .kpi-row { display: grid; grid-template-columns: repeat(4,1fr); gap: 1rem; margin-bottom: 1.5rem; }
    @media(max-width:640px){ .kpi-row{ grid-template-columns:repeat(2,1fr); } }
    .kpi-chip {
        background: white; border: 1px solid var(--border);
        border-radius: 14px; padding: 1rem 1.1rem;
        box-shadow: var(--shadow);
        display: flex; align-items: center; gap: 10px;
    }
    .kpi-dot  { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
    .kpi-num  { font-size: 1.6rem; font-weight: 800; color: #022C22; line-height: 1; }
    .kpi-lbl  { font-size: .72rem; font-weight: 600; color: #94A3B8; margin-top: 2px; }

    /* ─ Layout */
    .map-layout { display: grid; grid-template-columns: 1fr 300px; gap: 1.25rem; align-items: start; }
    @media(max-width:900px){ .map-layout{ grid-template-columns:1fr; } }

    /* ─ Panel */
    .panel {
        background: white; border: 1px solid var(--border);
        border-radius: 16px; overflow: hidden; box-shadow: var(--shadow);
    }
    .panel-head {
        padding: .9rem 1.2rem; border-bottom: 1px solid var(--border);
        display: flex; align-items: center; justify-content: space-between;
        font-size: .85rem; font-weight: 700; color: #022C22;
    }
    .panel-sub { font-size: .72rem; font-weight: 500; color: #94A3B8; }

    /* ─ Map wrapper */
    .map-wrap {
        position: relative; overflow: hidden;
        /* Foto aerial sebagai background */
        background: #1a2e1a;
    }
    .map-wrap img.aerial {
        width: 100%; display: block;
        max-height: 500px; object-fit: cover; object-position: center;
        opacity: 0.88;
    }
    /* Overlay gelap tipis agar sensor terlihat */
    .map-overlay {
        position: absolute; inset: 0;
        background: rgba(0,0,0,.18);
        pointer-events: none;
    }
    /* SVG sensor layer di atas foto */
    .sensor-svg {
        position: absolute; inset: 0;
        width: 100%; height: 100%;
    }

    /* ─ Sensor dot animation */
    @keyframes sensorPulse {
        0%   { r: 10; opacity: .7; }
        50%  { r: 18; opacity: .3; }
        100% { r: 10; opacity: .7; }
    }

    .sensor-ring { animation: sensorPulse 2.5s ease-in-out infinite; }

    /* ─ Sidebar: detail */
    .detail-empty {
        padding: 2.5rem 1.2rem; text-align: center; color: #CBD5E1;
    }
    .detail-empty svg { margin: 0 auto 10px; display: block; opacity: .35; }
    .detail-empty p   { font-size: .78rem; }

    .detail-card { padding: 1.1rem 1.2rem; }
    .detail-id   { font-size: .68rem; font-weight: 700; letter-spacing: .09em; text-transform: uppercase; color: #94A3B8; }
    .detail-name { font-size: 1rem; font-weight: 700; color: #022C22; margin: 4px 0 8px; }
    .detail-pill {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 10px; border-radius: 20px;
        font-size: .7rem; font-weight: 700; margin-bottom: 12px;
    }
    .detail-stats { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
    .detail-stat { background: #F8FAFC; border-radius: 10px; padding: 9px 10px; }
    .detail-stat-val { font-size: 1rem; font-weight: 700; color: #022C22; }
    .detail-stat-lbl { font-size: .65rem; color: #94A3B8; font-weight: 500; margin-top: 2px; }

    /* ─ Sensor list */
    .sensor-list-item {
        display: flex; align-items: center; gap: 9px;
        padding: 9px 1.1rem; border-bottom: 1px solid rgba(6,78,59,.04);
        cursor: pointer; transition: background .15s;
    }
    .sensor-list-item:hover { background: #F0FDF4; }
    .sensor-list-dot { width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0; }
    .sensor-list-id  { font-size: .68rem; font-weight: 700; color: #94A3B8; }
    .sensor-list-loc { font-size: .8rem; font-weight: 500; color: #022C22; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .sensor-pill {
        margin-left: auto; flex-shrink: 0;
        padding: 2px 8px; border-radius: 10px;
        font-size: .65rem; font-weight: 700;
    }

    /* Status colors */
    .sp-normal { background:#F0FDF4; color:#15803D; border:1px solid #BBF7D0; }
    .sp-rendah { background:#FFF7ED; color:#C2410C; border:1px solid #FED7AA; }
    .sp-tinggi { background:#EFF6FF; color:#1D4ED8; border:1px solid #BFDBFE; }
    .sp-kritis { background:#FEF2F2; color:#B91C1C; border:1px solid #FCA5A5; }

    @keyframes livePulse { 0%,100%{opacity:1} 50%{opacity:0.3} }
    .live-dot { display:inline-block; width:7px; height:7px; background:#10B981; border-radius:50%; animation:livePulse 2s infinite; }

    .sensor-click { cursor: pointer; transition: transform .2s; }
    .sensor-click:hover { transform: scale(1.12); }
</style>
</head>
<body>

<!-- ── Navbar -->
<nav>
    <div class="nav-inner">
        <a href="index.php" class="nav-brand">
            <div class="nav-brand-icon">
                <svg width="18" height="18" viewBox="0 0 44 44" fill="none">
                    <path d="M22 7C22 7 13 18 13 24C13 29.52 17.03 34 22 34C26.97 34 31 29.52 31 24C31 18 22 7 22 7Z" fill="#10B981"/>
                    <line x1="18" y1="24" x2="26" y2="24" stroke="white" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
            </div>
            <div>
                <div class="nav-brand-text">SM Irigasi</div>
                <div class="nav-brand-sub">Monitoring</div>
            </div>
        </a>
        <div class="nav-links">
            <a href="index.php" class="nav-link">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                Monitor
            </a>
            <a href="peta.php" class="nav-link active">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/></svg>
                Peta Sensor
            </a>
            <a href="bps.php" class="nav-link">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                Data BPS
            </a>
            <a href="riwayat.php" class="nav-link">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                Riwayat
            </a>
            <?php if ($role === 'administrator'): ?>
            <a href="dashboard.php" class="nav-link">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                Admin
            </a>
            <?php endif; ?>
            <a href="logout.php" class="nav-link danger">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Keluar
            </a>
        </div>
    </div>
</nav>

<main>
    <!-- Page header -->
    <div style="margin-bottom:1.4rem;">
        <h1 style="font-size:1.5rem;font-weight:800;color:#022C22;letter-spacing:-.03em;">Peta Sensor Interaktif</h1>
        <p style="font-size:.82rem;color:#4B7563;margin-top:4px;">
            <span class="live-dot"></span>
            Visualisasi posisi 8 sensor aktif pada jaringan irigasi sawah &middot; update setiap 4 detik
        </p>
    </div>

    <!-- KPI Row -->
    <div class="kpi-row">
        <div class="kpi-chip">
            <div class="kpi-dot" style="background:#10B981;"></div>
            <div><div class="kpi-num" id="cnt-normal">0</div><div class="kpi-lbl">Normal</div></div>
        </div>
        <div class="kpi-chip">
            <div class="kpi-dot" style="background:#F97316;"></div>
            <div><div class="kpi-num" id="cnt-rendah">0</div><div class="kpi-lbl">Debit Rendah</div></div>
        </div>
        <div class="kpi-chip">
            <div class="kpi-dot" style="background:#3B82F6;"></div>
            <div><div class="kpi-num" id="cnt-tinggi">0</div><div class="kpi-lbl">Debit Tinggi</div></div>
        </div>
        <div class="kpi-chip">
            <div class="kpi-dot" style="background:#EF4444;"></div>
            <div><div class="kpi-num" id="cnt-kritis">0</div><div class="kpi-lbl">Status Kritis</div></div>
        </div>
    </div>

    <!-- Map + Sidebar -->
    <div class="map-layout">

        <!-- Map Card -->
        <div class="panel">
            <div class="panel-head">
                <span>Denah Jaringan Irigasi Sawah</span>
                <span class="panel-sub" id="waktu-peta">--:--:--</span>
            </div>
            <div class="map-wrap">
                <!-- Foto aerial -->
                <img class="aerial" src="aerial-irigasi.png" alt="Foto Udara Jaringan Irigasi">
                <!-- Overlay -->
                <div class="map-overlay"></div>

                <!--
                    SVG sensor overlay — posisi disesuaikan dengan foto aerial:
                    Foto menampilkan 6 petak sawah berpintu air tersusun 2 baris × 3 kolom,
                    saluran utama di atas (horizontal), embung kecil di pojok kanan bawah.

                    Koordinat dalam % (viewBox 100×100) agar responsif:
                    SNS-01: Saluran induk atas-kiri (pintu air baris atas kiri)
                    SNS-02: Percabangan blok A (pintu air atas tengah-kiri)
                    SNS-03: Saluran blok B  (pintu air atas tengah-kanan)
                    SNS-04: Bak penampungan C1 (pintu air atas kanan)
                    SNS-05: Saluran Ngalor D (pintu air baris bawah kiri)
                    SNS-06: Saluran Ngetan E (pintu air baris bawah tengah)
                    SNS-07: Saluran Petak 12 (pintu air baris bawah kanan)
                    SNS-08: Embung Ngulon (kolam pojok kanan bawah)
                -->
                <svg class="sensor-svg" viewBox="0 0 100 100" preserveAspectRatio="none">
                    <!-- Glow filter -->
                    <defs>
                        <filter id="glow" x="-50%" y="-50%" width="200%" height="200%">
                            <feGaussianBlur stdDeviation="1.5" result="blur"/>
                            <feMerge><feMergeNode in="blur"/><feMergeNode in="SourceGraphic"/></feMerge>
                        </filter>
                    </defs>

                    <!-- Sensor SNS-01: pintu air saluran induk atas kiri -->
                    <g class="sensor-click" onclick="pilihSensor('SNS-01')">
                        <circle id="ring-SNS-01" cx="14" cy="10" r="4" fill="none" stroke="#10B981" stroke-width="1" opacity=".5" class="sensor-ring"/>
                        <circle id="dot-SNS-01" cx="14" cy="10" r="3.5" fill="#10B981" stroke="white" stroke-width="1.2" filter="url(#glow)"/>
                        <text x="14" y="10.8" text-anchor="middle" dominant-baseline="middle" font-size="2.2" fill="white" font-weight="700" font-family="Plus Jakarta Sans,sans-serif">S1</text>
                    </g>

                    <!-- Sensor SNS-02: pintu air petak atas kiri-tengah -->
                    <g class="sensor-click" onclick="pilihSensor('SNS-02')">
                        <circle id="ring-SNS-02" cx="26" cy="10" r="4" fill="none" stroke="#10B981" stroke-width="1" opacity=".5" class="sensor-ring"/>
                        <circle id="dot-SNS-02" cx="26" cy="10" r="3.5" fill="#10B981" stroke="white" stroke-width="1.2" filter="url(#glow)"/>
                        <text x="26" y="10.8" text-anchor="middle" dominant-baseline="middle" font-size="2.2" fill="white" font-weight="700" font-family="Plus Jakarta Sans,sans-serif">S2</text>
                    </g>

                    <!-- Sensor SNS-03: pintu air petak atas tengah-kanan -->
                    <g class="sensor-click" onclick="pilihSensor('SNS-03')">
                        <circle id="ring-SNS-03" cx="47" cy="10" r="4" fill="none" stroke="#F97316" stroke-width="1" opacity=".5" class="sensor-ring"/>
                        <circle id="dot-SNS-03" cx="47" cy="10" r="3.5" fill="#F97316" stroke="white" stroke-width="1.2" filter="url(#glow)"/>
                        <text x="47" y="10.8" text-anchor="middle" dominant-baseline="middle" font-size="2.2" fill="white" font-weight="700" font-family="Plus Jakarta Sans,sans-serif">S3</text>
                    </g>

                    <!-- Sensor SNS-04: pintu air petak atas kanan -->
                    <g class="sensor-click" onclick="pilihSensor('SNS-04')">
                        <circle id="ring-SNS-04" cx="69" cy="10" r="4" fill="none" stroke="#3B82F6" stroke-width="1" opacity=".5" class="sensor-ring"/>
                        <circle id="dot-SNS-04" cx="69" cy="10" r="3.5" fill="#3B82F6" stroke="white" stroke-width="1.2" filter="url(#glow)"/>
                        <text x="69" y="10.8" text-anchor="middle" dominant-baseline="middle" font-size="2.2" fill="white" font-weight="700" font-family="Plus Jakarta Sans,sans-serif">S4</text>
                    </g>

                    <!-- Sensor SNS-05: pintu air petak bawah kiri -->
                    <g class="sensor-click" onclick="pilihSensor('SNS-05')">
                        <circle id="ring-SNS-05" cx="15" cy="55" r="4" fill="none" stroke="#10B981" stroke-width="1" opacity=".5" class="sensor-ring"/>
                        <circle id="dot-SNS-05" cx="15" cy="55" r="3.5" fill="#10B981" stroke="white" stroke-width="1.2" filter="url(#glow)"/>
                        <text x="15" y="55.8" text-anchor="middle" dominant-baseline="middle" font-size="2.2" fill="white" font-weight="700" font-family="Plus Jakarta Sans,sans-serif">S5</text>
                    </g>

                    <!-- Sensor SNS-06: pintu air petak bawah tengah -->
                    <g class="sensor-click" onclick="pilihSensor('SNS-06')">
                        <circle id="ring-SNS-06" cx="46" cy="50" r="4" fill="none" stroke="#EF4444" stroke-width="1" opacity=".5" class="sensor-ring"/>
                        <circle id="dot-SNS-06" cx="46" cy="50" r="3.5" fill="#EF4444" stroke="white" stroke-width="1.2" filter="url(#glow)"/>
                        <text x="46" y="50.8" text-anchor="middle" dominant-baseline="middle" font-size="2.2" fill="white" font-weight="700" font-family="Plus Jakarta Sans,sans-serif">S6</text>
                    </g>

                    <!-- Sensor SNS-07: pintu air petak bawah kanan -->
                    <g class="sensor-click" onclick="pilihSensor('SNS-07')">
                        <circle id="ring-SNS-07" cx="68" cy="50" r="4" fill="none" stroke="#10B981" stroke-width="1" opacity=".5" class="sensor-ring"/>
                        <circle id="dot-SNS-07" cx="68" cy="50" r="3.5" fill="#10B981" stroke="white" stroke-width="1.2" filter="url(#glow)"/>
                        <text x="68" y="50.8" text-anchor="middle" dominant-baseline="middle" font-size="2.2" fill="white" font-weight="700" font-family="Plus Jakarta Sans,sans-serif">S7</text>
                    </g>

                    <!-- Sensor SNS-08: embung pojok kanan bawah -->
                    <g class="sensor-click" onclick="pilihSensor('SNS-08')">
                        <circle id="ring-SNS-08" cx="85" cy="80" r="4" fill="none" stroke="#10B981" stroke-width="1" opacity=".5" class="sensor-ring"/>
                        <circle id="dot-SNS-08" cx="85" cy="80" r="3.5" fill="#10B981" stroke="white" stroke-width="1.2" filter="url(#glow)"/>
                        <text x="85" y="80.8" text-anchor="middle" dominant-baseline="middle" font-size="2.2" fill="white" font-weight="700" font-family="Plus Jakarta Sans,sans-serif">S8</text>
                    </g>
                </svg>
            </div>

            <!-- Legend -->
            <div style="display:flex;gap:1.25rem;padding:.8rem 1.2rem;border-top:1px solid var(--border);background:#FAFAFA;flex-wrap:wrap;">
                <?php foreach([
                    ['#10B981','Normal'],
                    ['#F97316','Rendah'],
                    ['#3B82F6','Tinggi'],
                    ['#EF4444','Kritis'],
                ] as [$c,$l]): ?>
                <div style="display:flex;align-items:center;gap:5px;font-size:.72rem;font-weight:600;color:#4B7563;">
                    <div style="width:8px;height:8px;border-radius:50%;background:<?= $c ?>;"></div>
                    <?= $l ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Sidebar -->
        <div style="display:flex;flex-direction:column;gap:1rem;">

            <!-- Detail Sensor -->
            <div class="panel">
                <div class="panel-head">Detail Sensor</div>
                <div id="detail-sensor">
                    <div class="detail-empty">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/><line x1="9" y1="3" x2="9" y2="18"/><line x1="15" y1="6" x2="15" y2="21"/></svg>
                        <p>Klik titik sensor pada peta untuk melihat detail data</p>
                    </div>
                </div>
            </div>

            <!-- Daftar Sensor -->
            <div class="panel">
                <div class="panel-head">Semua Sensor Aktif</div>
                <div id="daftar-sensor" style="overflow-y:auto;max-height:280px;"></div>
            </div>

        </div>
    </div>
</main>

<footer style="padding:1rem 1.5rem;background:#064E3B;text-align:center;font-size:.73rem;color:rgba(255,255,255,.35);margin-top:2rem;">
    &copy; 2026 SM Irigasi &mdash; Universitas Sebelas Maret &middot; Sistem Monitoring Irigasi Sawah
</footer>

<script>
// ─ Data sensor
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

var WARNA = { normal:"#10B981", rendah:"#F97316", tinggi:"#3B82F6", kritis:"#EF4444" };
var LABEL = { normal:"Normal", rendah:"Rendah", tinggi:"Tinggi", kritis:"Kritis" };

function pillStyle(status) {
    var s = {
        normal: "background:#F0FDF4;color:#15803D;border:1px solid #BBF7D0;",
        rendah: "background:#FFF7ED;color:#C2410C;border:1px solid #FED7AA;",
        tinggi: "background:#EFF6FF;color:#1D4ED8;border:1px solid #BFDBFE;",
        kritis: "background:#FEF2F2;color:#B91C1C;border:1px solid #FCA5A5;"
    };
    return s[status] || s.normal;
}

// ─ Waktu
function updateWaktu() {
    var n = new Date();
    var pad = function(x){ return String(x).padStart(2,'0'); };
    document.getElementById('waktu-peta').textContent = pad(n.getHours())+':'+pad(n.getMinutes())+':'+pad(n.getSeconds());
}

// ─ Render daftar sensor
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
    document.getElementById('daftar-sensor').innerHTML = html;
    ['normal','rendah','tinggi','kritis'].forEach(function(k) {
        document.getElementById('cnt-'+k).textContent = cnt[k] || 0;
    });
}

// Pilih sensor
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

    document.getElementById('detail-sensor').innerHTML = html;

    // Highlight dot yang dipilih
    dataSensor.forEach(function(x) {
        var el = document.getElementById('dot-'+x.id);
        if (el) el.setAttribute('stroke-width', x.id === id ? '2' : '1.2');
    });
}

// ─ Simulasi update sensor
function simulasiUpdate() {
    dataSensor.forEach(function(s) {
        s.debit  = Math.max(0.5, s.debit + (Math.random() - 0.5));
        s.tma    = Math.max(5, s.tma + Math.round((Math.random() - 0.5) * 3));
        s.lembap = Math.min(100, Math.max(10, s.lembap + Math.round((Math.random() - 0.5) * 2)));
        if      (s.tma < 15) s.status = 'kritis';
        else if (s.tma < 25) s.status = 'rendah';
        else if (s.tma > 65) s.status = 'tinggi';
        else                  s.status = 'normal';

        var dot = document.getElementById('dot-'+s.id);
        if (dot) dot.setAttribute('fill', WARNA[s.status]);
        var ring = document.getElementById('ring-'+s.id);
        if (ring) ring.setAttribute('stroke', WARNA[s.status]);
    });
    renderDaftar();
}

// ─ Init
renderDaftar();
setInterval(simulasiUpdate, 4000);
setInterval(updateWaktu, 1000);
updateWaktu();
</script>
</body>
</html>
