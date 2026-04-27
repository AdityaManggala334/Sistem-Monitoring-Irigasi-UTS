<?php

session_start();
require_once 'koneksi.php';

// ── Auth (semua user yang login boleh akses)
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$namaUser = htmlspecialchars(trim(
    ($_SESSION['nama_depan'] ?? '') . ' ' . ($_SESSION['nama_belakang'] ?? '')
) ?: ($_SESSION['username'] ?? ''));
$role = $_SESSION['role'] ?? 'petani';


//  Ambil data dari API BPS via cURL

function fetchBPS(string $url): ?array {
    if (!function_exists('curl_init')) return null;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_USERAGENT      => 'SMIrigasi/1.0',
    ]);
    $raw  = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if (!$raw || $code !== 200) return null;
    $data = json_decode($raw, true);
    return json_last_error() === JSON_ERROR_NONE ? $data : null;
}

//  Parser: ekstrak baris data dari struktur BPS
//  Struktur nyata: response['data'][1]['data'][n]
//  Kolom variabel:
//  qjt4tgvtld = Luas Panen (ha)
//  od6zj61thq = Produktivitas (ku/ha)
//  mtn492ybb1 = Produksi (ton)

function parseBPS(array $response): array {
    $rows  = $response['data'][1]['data']  ?? [];
    $judul = $response['data'][1]['judul_tabel'] ?? '';
    $result = [];

    foreach ($rows as $row) {
        if (($row['kode_wilayah'] ?? '') === '3300000') continue; // skip total Jateng

        // Nilai BPS format "125.882,00" → float
        $clean = fn($v) => floatval(str_replace(['.', ','], ['', '.'], $v));

        $result[] = [
            'wilayah'       => $row['label'] ?? '-',
            'kode'          => $row['kode_wilayah'] ?? '',
            'luas_panen'    => $clean($row['variables']['qjt4tgvtld']['value'] ?? '0'),
            'produktivitas' => $clean($row['variables']['od6zj61thq']['value'] ?? '0'),
            'produksi'      => $clean($row['variables']['mtn492ybb1']['value'] ?? '0'),
        ];
    }

    usort($result, fn($a, $b) => $b['luas_panen'] <=> $a['luas_panen']);
    return ['rows' => $result, 'judul' => $judul];
}

//  Eksekusi

$API_URL = 'https://webapi.bps.go.id/v1/api/interoperabilitas/datasource/simdasi/id/25/tahun/2025/id_tabel/ZjZ6MXlacGJNR0JaaHBPRSs0TzNUdz09/wilayah/3300000/key/cc819bdc45f65b22eebcb08f167d0e08';

$raw      = fetchBPS($API_URL);
$parsed   = $raw ? parseBPS($raw) : ['rows' => [], 'judul' => ''];
$listData = $parsed['rows'];
$judul    = $parsed['judul'];
$hasData  = count($listData) > 0;

// Statistik
$totalLuas     = array_sum(array_column($listData, 'luas_panen'));
$totalProduksi = array_sum(array_column($listData, 'produksi'));
$jumlah        = count($listData);
$terluas       = $listData[0] ?? null;
$rataProduktiv = $jumlah > 0
    ? array_sum(array_column($listData, 'produktivitas')) / $jumlah
    : 0;
?>


//  Ambil data dari API BPS via cURL

function fetchBPS(string $url): ?array {
    if (!function_exists('curl_init')) return null;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_USERAGENT      => 'SMIrigasi/1.0',
    ]);
    $raw  = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if (!$raw || $code !== 200) return null;
    $data = json_decode($raw, true);
    return json_last_error() === JSON_ERROR_NONE ? $data : null;
}

//  Parser: ekstrak baris data dari struktur BPS
//  Struktur nyata: response['data'][1]['data'][n]
//  Kolom variabel:
//  qjt4tgvtld = Luas Panen (ha)
//  od6zj61thq = Produktivitas (ku/ha)
//  mtn492ybb1 = Produksi (ton)

function parseBPS(array $response): array {
    $rows  = $response['data'][1]['data']  ?? [];
    $judul = $response['data'][1]['judul_tabel'] ?? '';
    $result = [];

    foreach ($rows as $row) {
        if (($row['kode_wilayah'] ?? '') === '3300000') continue; // skip total Jateng

        // Nilai BPS format "125.882,00" → float
        $clean = fn($v) => floatval(str_replace(['.', ','], ['', '.'], $v));

        $result[] = [
            'wilayah'       => $row['label'] ?? '-',
            'kode'          => $row['kode_wilayah'] ?? '',
            'luas_panen'    => $clean($row['variables']['qjt4tgvtld']['value'] ?? '0'),
            'produktivitas' => $clean($row['variables']['od6zj61thq']['value'] ?? '0'),
            'produksi'      => $clean($row['variables']['mtn492ybb1']['value'] ?? '0'),
        ];
    }

    usort($result, fn($a, $b) => $b['luas_panen'] <=> $a['luas_panen']);
    return ['rows' => $result, 'judul' => $judul];
}

//  Eksekusi

$API_URL = 'https://webapi.bps.go.id/v1/api/interoperabilitas/datasource/simdasi/id/25/tahun/2025/id_tabel/ZjZ6MXlacGJNR0JaaHBPRSs0TzNUdz09/wilayah/3300000/key/cc819bdc45f65b22eebcb08f167d0e08';

$raw      = fetchBPS($API_URL);
$parsed   = $raw ? parseBPS($raw) : ['rows' => [], 'judul' => ''];
$listData = $parsed['rows'];
$judul    = $parsed['judul'];
$hasData  = count($listData) > 0;

// Statistik
$totalLuas     = array_sum(array_column($listData, 'luas_panen'));
$totalProduksi = array_sum(array_column($listData, 'produksi'));
$jumlah        = count($listData);
$terluas       = $listData[0] ?? null;
$rataProduktiv = $jumlah > 0
    ? array_sum(array_column($listData, 'produktivitas')) / $jumlah
    : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data BPS — SM Irigasi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --green-900: #064E3B;
            --green-700: #047857;
            --green-500: #10B981;
            --green-400: #34D399;
            --green-50:  #F0FDF4;
            --border:    rgba(6,78,59,0.08);
            --shadow:    0 1px 3px rgba(6,78,59,0.06), 0 8px 24px rgba(6,78,59,0.07);
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #F0FDF4;
            color: #0A2218;
            min-height: 100vh;
        }

        /* Navbar */
        nav {
            background: rgba(6,78,59,0.97);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(255,255,255,0.07);
            position: sticky; top: 0; z-index: 50;
        }
        .nav-inner {
            max-width: 1200px; margin: 0 auto;
            padding: 0 1.5rem; height: 64px;
            display: flex; align-items: center; justify-content: space-between;
        }
        .nav-brand {
            display: flex; align-items: center; gap: 10px;
            text-decoration: none;
        }
        .nav-brand-icon {
            width: 36px; height: 36px; border-radius: 10px;
            background: rgba(16,185,129,0.18);
            border: 1px solid rgba(52,211,153,0.25);
            display: flex; align-items: center; justify-content: center;
        }
        .nav-brand-text { font-size: .95rem; font-weight: 800; color: white; letter-spacing: -.02em; }
        .nav-brand-sub  { font-size: .65rem; font-weight: 600; color: rgba(255,255,255,.35); text-transform: uppercase; letter-spacing: .08em; }
        .nav-links { display: flex; align-items: center; gap: 4px; }
        .nav-link {
            display: flex; align-items: center; gap: 6px;
            padding: 6px 13px; border-radius: 9px;
            font-size: .82rem; font-weight: 500;
            color: rgba(255,255,255,.65);
            text-decoration: none; transition: all .18s;
        }
        .nav-link:hover    { background: rgba(255,255,255,.10); color: white; }
        .nav-link.active   { background: rgba(16,185,129,.20); color: #34D399; font-weight: 600; }
        .nav-link.danger   { background: rgba(239,68,68,.12); color: rgba(255,180,180,.9); }
        .nav-link.danger:hover { background: rgba(239,68,68,.22); }

        /* Main */
        .page { max-width: 1200px; margin: 0 auto; padding: 2rem 1.5rem; }

        /* Page Header */
        .page-header { margin-bottom: 1.75rem; }
        .page-header h1 { font-size: 1.5rem; font-weight: 800; color: #022C22; letter-spacing: -.03em; }
        .page-header p  { font-size: .82rem; color: #4B7563; margin-top: 4px; }
        .status-chip {
            display: inline-flex; align-items: center; gap: 6px;
            margin-top: 8px; padding: 4px 12px;
            border-radius: 20px; font-size: .72rem; font-weight: 700;
        }
        .chip-ok  { background: #D1FAE5; color: #065F46; }
        .chip-err { background: #FEE2E2; color: #991B1B; }
        .pulse { width: 7px; height: 7px; border-radius: 50%; background: #10B981; animation: pulse 2s infinite; }
        @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.3} }

        /* KPI Grid */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem; margin-bottom: 1.5rem;
        }
        .kpi-card {
            background: white; border: 1px solid var(--border);
            border-radius: 16px; padding: 1.2rem 1.3rem;
            box-shadow: var(--shadow);
            position: relative; overflow: hidden;
            transition: transform .2s, box-shadow .2s;
        }
        .kpi-card:hover { transform: translateY(-2px); box-shadow: 0 4px 6px rgba(6,78,59,0.04), 0 20px 50px rgba(6,78,59,0.10); }
        .kpi-card::before {
            content: ''; position: absolute;
            top: 0; left: 0; width: 4px; height: 100%;
            background: var(--green-500);
        }
        .kpi-card.gold::before { background: #D97706; }
        .kpi-card.blue::before { background: #2563EB; }
        .kpi-card.rose::before { background: #E11D48; }
        .kpi-label { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #94A3B8; }
        .kpi-value { font-size: 1.6rem; font-weight: 800; color: #022C22; margin: 6px 0 2px; line-height: 1; letter-spacing: -.03em; }
        .kpi-sub   { font-size: .75rem; color: #4B7563; }

        /* Main Grid */
        .main-grid {
            display: grid;
            grid-template-columns: 1fr 320px;
            gap: 1.25rem; margin-bottom: 1.5rem;
        }
        @media (max-width: 900px) { .main-grid { grid-template-columns: 1fr; } }

        /* Panel */
        .panel {
            background: white; border: 1px solid var(--border);
            border-radius: 16px; overflow: hidden;
            box-shadow: var(--shadow);
        }
        .panel-head {
            padding: 1rem 1.25rem .75rem;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: flex-start; justify-content: space-between;
        }
        .panel-title { font-size: .9rem; font-weight: 700; color: #022C22; }
        .panel-sub   { font-size: .72rem; color: #94A3B8; margin-top: 2px; }
        .panel-body  { padding: 1.1rem 1.25rem; }

        /* Top 5 bars */
        .bar-item { margin-bottom: 1rem; }
        .bar-row  { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 5px; }
        .bar-name { font-size: .8rem; font-weight: 600; color: #022C22; }
        .bar-val  { font-size: .74rem; font-weight: 700; color: var(--green-500); font-variant-numeric: tabular-nums; }
        .bar-track{ height: 5px; background: #F1F5F9; border-radius: 4px; }
        .bar-fill { height: 5px; background: linear-gradient(90deg, #10B981, #34D399); border-radius: 4px; transition: width .7s cubic-bezier(.22,1,.36,1); }

        /* Full table */
        .tbl-wrap { background: white; border: 1px solid var(--border); border-radius: 16px; overflow: hidden; box-shadow: var(--shadow); }
        .tbl-head { padding: 1rem 1.4rem .75rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
        .scroll-body { max-height: 440px; overflow-y: auto; }
        .scroll-body::-webkit-scrollbar { width: 4px; }
        .scroll-body::-webkit-scrollbar-thumb { background: #E2E8F0; border-radius: 4px; }

        table { width: 100%; border-collapse: collapse; }
        thead th {
            padding: 9px 14px; text-align: left;
            font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .07em;
            color: #94A3B8; background: #FAFAFA;
            border-bottom: 1px solid var(--border);
        }
        thead th.r { text-align: right; }
        tbody tr { border-bottom: 1px solid rgba(6,78,59,.04); transition: background .15s; }
        tbody tr:hover { background: var(--green-50); }
        tbody td { padding: 9px 14px; font-size: .82rem; }
        .mono { font-variant-numeric: tabular-nums; font-weight: 600; color: var(--green-500); text-align: right; }
        .idx  { color: #CBD5E1; font-size: .75rem; }
        .rank-badge {
            display: inline-block; width: 20px; height: 20px;
            border-radius: 6px; text-align: center; line-height: 20px;
            font-size: .65rem; font-weight: 800;
        }
        .rank-1 { background: #FEF3C7; color: #92400E; }
        .rank-2 { background: #F1F5F9; color: #475569; }
        .rank-3 { background: #FFF7ED; color: #9A3412; }

        /* Error */
        .err-box {
            background: #FEF2F2; border: 1px solid #FECACA;
            border-radius: 16px; padding: 2rem; text-align: center;
        }
        .err-box h3 { color: #991B1B; font-size: .95rem; font-weight: 700; margin-bottom: 8px; }
        .err-box p  { font-size: .82rem; color: #B91C1C; }
    </style>
</head>
<body>

<!-- Navbar -->
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
            <a href="peta.php" class="nav-link">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/></svg>
                Peta
            </a>
            <a href="bps.php" class="nav-link active">
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

<!-- Page Content -->
<div class="page">

    <!-- Page Header -->
    <div class="page-header">
        <h1>Data Pertanian BPS</h1>
        <p><?= htmlspecialchars($judul ?: 'Luas Panen, Produktivitas, dan Produksi Padi — Jawa Tengah 2025'); ?></p>
        <?php if ($hasData): ?>
        <span class="status-chip chip-ok">
            <span class="pulse"></span>
            Terhubung ke API BPS &middot; <?= $jumlah; ?> wilayah
        </span>
        <?php else: ?>
        <span class="status-chip chip-err">Tidak dapat memuat data BPS</span>
        <?php endif; ?>
    </div>

    <?php if (!$hasData): ?>
    <div class="err-box">
        <h3>Gagal Memuat Data</h3>
        <p>Tidak dapat terhubung ke API BPS. Periksa koneksi internet server.</p>
    </div>
    <?php endif; ?>

    <?php if ($hasData): ?>

    <!-- KPI Cards -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-label">Total Luas Panen</div>
            <div class="kpi-value"><?= number_format($totalLuas, 0, ',', '.'); ?></div>
            <div class="kpi-sub">Hektar (Ha)</div>
        </div>
        <div class="kpi-card rose">
            <div class="kpi-label">Total Produksi Padi</div>
            <div class="kpi-value"><?= number_format($totalProduksi, 0, ',', '.'); ?></div>
            <div class="kpi-sub">Ton GKG</div>
        </div>
        <div class="kpi-card gold">
            <div class="kpi-label">Rata-rata Produktivitas</div>
            <div class="kpi-value"><?= number_format($rataProduktiv, 2, ',', '.'); ?></div>
            <div class="kpi-sub">Ku / Ha</div>
        </div>
        <div class="kpi-card blue">
            <div class="kpi-label">Luas Panen Terbesar</div>
            <div class="kpi-value" style="font-size:1.05rem;margin-top:8px;"><?= htmlspecialchars($terluas['wilayah']); ?></div>
            <div class="kpi-sub"><?= number_format($terluas['luas_panen'], 0, ',', '.'); ?> Ha</div>
        </div>
    </div>

    <!-- Chart + Top 5 -->
    <div class="main-grid">
        <div class="panel">
            <div class="panel-head">
                <div>
                    <div class="panel-title">Distribusi Luas Panen per Kabupaten / Kota</div>
                    <div class="panel-sub">20 wilayah tertinggi · Jawa Tengah 2025</div>
                </div>
            </div>
            <div class="panel-body">
                <canvas id="bpsChart" height="230"></canvas>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <div>
                    <div class="panel-title">Peringkat Luas Panen</div>
                    <div class="panel-sub">5 kabupaten / kota terluas</div>
                </div>
            </div>
            <div class="panel-body">
                <?php
                $top5   = array_slice($listData, 0, 5);
                $maxVal = $top5[0]['luas_panen'] ?? 1;
                foreach ($top5 as $i => $item):
                    $pct = $maxVal > 0 ? ($item['luas_panen'] / $maxVal * 100) : 0;
                ?>
                <div class="bar-item">
                    <div class="bar-row">
                        <span class="bar-name"><?= $i+1 ?>. <?= htmlspecialchars($item['wilayah']); ?></span>
                        <span class="bar-val"><?= number_format($item['luas_panen'], 0, ',', '.'); ?> Ha</span>
                    </div>
                    <div class="bar-track">
                        <div class="bar-fill" style="width:<?= round($pct) ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Full Data Table -->
    <div class="tbl-wrap">
        <div class="tbl-head">
            <div>
                <div class="panel-title">Data Lengkap Seluruh Kabupaten / Kota</div>
                <div class="panel-sub">Diurutkan berdasarkan luas panen terbesar &middot; Sumber: BPS Jawa Tengah 2025</div>
            </div>
            <span style="font-size:.72rem;color:#94A3B8;font-variant-numeric:tabular-nums;">Update: <?= date('d M Y'); ?></span>
        </div>
        <div class="scroll-body">
            <table>
                <thead>
                    <tr>
                        <th style="width:44px;">No</th>
                        <th>Kabupaten / Kota</th>
                        <th class="r">Luas Panen (Ha)</th>
                        <th class="r">Produktivitas (ku/ha)</th>
                        <th class="r">Produksi (Ton)</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($listData as $i => $item): ?>
                <tr>
                    <td>
                        <?php if ($i < 3): ?>
                        <span class="rank-badge rank-<?= $i+1 ?>"><?= $i+1 ?></span>
                        <?php else: ?>
                        <span class="idx"><?= $i+1 ?></span>
                        <?php endif; ?>
                    </td>
                    <td style="font-weight:600;"><?= htmlspecialchars($item['wilayah']); ?></td>
                    <td class="mono"><?= number_format($item['luas_panen'],    2, ',', '.'); ?></td>
                    <td class="mono"><?= number_format($item['produktivitas'], 2, ',', '.'); ?></td>
                    <td class="mono"><?= number_format($item['produksi'],      2, ',', '.'); ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php endif; ?>
</div>

<footer style="margin-top:2.5rem;padding:1rem 1.5rem;background:#064E3B;text-align:center;font-size:.73rem;color:rgba(255,255,255,.35);">
    &copy; 2026 SM Irigasi &mdash; Universitas Sebelas Maret &middot; Sumber Data: Badan Pusat Statistik
</footer>

<?php if ($hasData): ?>
<script>
    const chartData = <?= json_encode(array_slice($listData, 0, 20)); ?>;
    const labels    = chartData.map(d => d.wilayah);
    const values    = chartData.map(d => d.luas_panen);

    const ctx  = document.getElementById('bpsChart').getContext('2d');
    const grad = ctx.createLinearGradient(0, 0, 0, 280);
    grad.addColorStop(0, '#10B981');
    grad.addColorStop(1, '#D1FAE5');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Luas Panen (Ha)',
                data: values,
                backgroundColor: grad,
                borderRadius: 5,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ' ' + ctx.parsed.y.toLocaleString('id-ID') + ' Ha'
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(6,78,59,0.05)' },
                    ticks: {
                        callback: v => v >= 1000
                            ? (v/1000).toLocaleString('id-ID') + 'rb'
                            : v.toLocaleString('id-ID'),
                        font: { size: 10, family: 'Plus Jakarta Sans' }
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 9, family: 'Plus Jakarta Sans' }, maxRotation: 45 }
                }
            }
        }
    });
</script>
<?php endif; ?>

</body>
</html>
