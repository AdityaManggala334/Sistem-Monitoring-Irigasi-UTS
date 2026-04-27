<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', '0');
require_once 'koneksi.php';
require_once 'auth_helper.php';

$pesan_laporan = '';
$pesan_warna   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kirim_laporan'])) {
    $nama_pelapor  = trim($_POST['nama_pelapor']  ?? '');
    $lokasi        = trim($_POST['lokasi_kendala'] ?? '');
    $jenis_kendala = trim($_POST['jenis_kendala']  ?? '');

    if (empty($nama_pelapor) || empty($lokasi) || empty($jenis_kendala)) {
        $pesan_laporan = 'Mohon isi semua kolom sebelum mengirim laporan.';
        $pesan_warna   = 'error';
    } else {
        $id_user = (int)$user_id ?: null;
        $stmt = mysqli_prepare($conn, "INSERT INTO laporan_kendala (id_users, nama_pelapor, lokasi, jenis_kendala) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'isss', $id_user, $nama_pelapor, $lokasi, $jenis_kendala);
        if (mysqli_stmt_execute($stmt)) {
            $pesan_laporan = 'Laporan berhasil dikirim! Petugas akan segera menangani.';
            $pesan_warna   = 'sukses';
        } else {
            $pesan_laporan = 'Gagal menyimpan laporan. Coba lagi.';
            $pesan_warna   = 'error';
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SM Irigasi — Beranda</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  html { scroll-behavior: smooth; }
  body { font-family: 'Plus Jakarta Sans', sans-serif; }
  @keyframes livePulse { 0%,100%{opacity:1} 50%{opacity:0.3} }
  .live-dot { animation: livePulse 2s ease-in-out infinite; }
  .status-pill { display:inline-flex; align-items:center; gap:4px; padding:3px 10px; border-radius:20px; font-size:0.70rem; font-weight:700; white-space:nowrap; }
  .sp-normal { background:#F0FDF4; color:#15803D; border:1px solid #BBF7D0; }
  .sp-rendah { background:#FFF7ED; color:#C2410C; border:1px solid #FED7AA; }
  .sp-tinggi { background:#EFF6FF; color:#1D4ED8; border:1px solid #BFDBFE; }
  .sp-kritis { background:#FEF2F2; color:#B91C1C; border:1px solid #FCA5A5; }
  .profil-wrap:hover .profil-dropdown { display:block; }
  .profil-dropdown { display:none; position:absolute; right:0; top:100%; margin-top:8px; background:white; border-radius:16px; min-width:200px; box-shadow:0 8px 32px rgba(0,0,0,0.14); z-index:50; overflow:hidden; }
  /* Tabel: kolom lebar proporsional */
  #tabel-sensor th, #tabel-sensor td { padding: 10px 12px; white-space: nowrap; }
  #tabel-sensor th { font-size: 0.70rem; text-transform: uppercase; letter-spacing: 0.05em; color:#94a3b8; background:#f8fafc; border-bottom: 1px solid rgba(6,78,59,0.06); font-weight:700; }
  #tabel-sensor td { font-size: 0.82rem; border-bottom: 1px solid rgba(6,78,59,0.04); color:#475569; }
  #tabel-sensor tbody tr:hover { background: rgba(16,185,129,0.03); }
  #tabel-sensor td:nth-child(1) { color:#94a3b8; width:40px; text-align:center; }
  #tabel-sensor td:nth-child(2) { font-weight:700; color:#059669; }
</style>
</head>
<body class="bg-slate-50 text-slate-800">

<!-- ══════════ NAVBAR ══════════ -->
<nav class="sticky top-0 z-50 border-b" style="background:rgba(6,78,59,0.97);backdrop-filter:blur(16px);border-color:rgba(255,255,255,0.07);">
  <div class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between gap-4">

    <a href="index.php" class="flex items-center gap-2.5 no-underline flex-shrink-0">
      <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background:rgba(16,185,129,0.18);border:1px solid rgba(52,211,153,0.25);">
        <svg width="20" height="20" viewBox="0 0 44 44" fill="none">
          <path d="M22 7C22 7 13 18 13 24C13 29.52 17.03 34 22 34C26.97 34 31 29.52 31 24C31 18 22 7 22 7Z" fill="#10B981"/>
          <line x1="18" y1="24" x2="26" y2="24" stroke="white" stroke-width="1.8" stroke-linecap="round"/>
          <circle cx="18" cy="24" r="1.4" fill="white"/>
          <circle cx="26" cy="24" r="1.4" fill="white"/>
          <line x1="22" y1="20" x2="22" y2="28" stroke="white" stroke-width="1.8" stroke-linecap="round"/>
        </svg>
      </div>
      <span class="text-base font-extrabold text-white tracking-tight">SM Irigasi</span>
    </a>

    <div class="flex items-center gap-1">
      <a href="#tentang" class="px-3 py-1.5 rounded-lg text-sm font-medium transition-all hover:bg-white/10" style="color:rgba(255,255,255,0.65);">Tentang</a>
      <a href="#monitoring" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-all hover:bg-white/10" style="color:rgba(255,255,255,0.65);">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>Monitor
      </a>
      <a href="peta.php" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-all hover:bg-white/10" style="color:rgba(255,255,255,0.65);">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/></svg>Peta
      </a>
      <a href="bps.php" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-all hover:bg-white/10" style="color:rgba(255,255,255,0.65);">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>Data BPS
      </a>
      <a href="riwayat.php" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-all hover:bg-white/10" style="color:rgba(255,255,255,0.65);">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>Riwayat
      </a>
      <?php if ($role === 'administrator'): ?>
      <a href="dashboard.php" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-all hover:bg-white/10" style="color:rgba(255,255,255,0.65);">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>Admin
      </a>
      <?php endif; ?>

      <div class="profil-wrap relative ml-2">
        <button class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-medium hover:bg-white/10 bg-transparent border-none cursor-pointer" style="color:rgba(255,255,255,0.80);">
          <div class="w-7 h-7 rounded-lg flex items-center justify-center font-bold text-xs" style="background:rgba(16,185,129,0.25);color:#34D399;">
            <?= strtoupper(substr($namaDepan ?: 'U', 0, 1)) ?>
          </div>
          <?= $namaDepan ?>
          <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
        </button>
        <div class="profil-dropdown">
          <div class="px-4 py-3 border-b" style="background:linear-gradient(135deg,#F0FDF6,#E6F9F0);border-color:rgba(6,78,59,0.08);">
            <div class="font-bold text-emerald-900 text-sm"><?= $namaLengkap ?></div>
            <div class="text-xs text-slate-500 mt-0.5 capitalize"><?= str_replace('_', ' ', $role) ?></div>
          </div>
          <a href="logout.php" class="flex items-center gap-2 px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-colors no-underline">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Keluar
          </a>
        </div>
      </div>
    </div>
  </div>
</nav>

<!-- ══════════ KONTEN UTAMA ══════════ -->
<div class="max-w-6xl mx-auto px-6 py-8">

  <!-- HERO -->
  <div class="relative rounded-3xl overflow-hidden mb-8" style="min-height:320px;">
    <img src="https://i.imgur.com/elrEGQB.jpeg" alt="Irigasi Sawah" class="absolute inset-0 w-full h-full object-cover" style="object-position:center 40%;">
    <div class="absolute inset-0" style="background:linear-gradient(100deg,rgba(2,44,34,0.90) 0%,rgba(6,78,59,0.70) 55%,rgba(6,78,59,0.25) 100%);"></div>
    <div class="absolute inset-0 opacity-5" style="background-image:linear-gradient(rgba(255,255,255,1) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,1) 1px,transparent 1px);background-size:48px 48px;"></div>

    <div class="relative z-10 flex items-center justify-between gap-8 p-10 flex-wrap" style="min-height:320px;">
      <div class="max-w-lg">
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold mb-4" style="background:rgba(16,185,129,0.20);border:1px solid rgba(52,211,153,0.30);color:#34D399;">
          <span class="live-dot inline-block w-1.5 h-1.5 bg-emerald-400 rounded-full"></span>
          Sistem Aktif · Update Setiap 4 Detik
        </div>
        <h1 class="text-3xl sm:text-4xl font-extrabold text-white leading-tight tracking-tight mb-3">
          Sistem Monitoring<br>Irigasi Sawah <span class="text-emerald-400">Cerdas</span>
        </h1>
        <p class="text-sm leading-relaxed mb-6 max-w-md" style="color:rgba(255,255,255,0.60);">
          Pantau debit air, tinggi muka air, dan kelembapan tanah secara real-time dari sensor yang tersebar di seluruh jaringan irigasi sawah.
        </p>
        <div class="flex gap-3 flex-wrap">
          <a href="#monitoring" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-white transition-all hover:-translate-y-0.5"
             style="background:linear-gradient(135deg,#10B981,#059669);box-shadow:0 4px 16px rgba(16,185,129,0.40);">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            Lihat Data Sensor
          </a>
          <a href="peta.php" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold transition-all hover:bg-white/20"
             style="background:rgba(255,255,255,0.10);color:rgba(255,255,255,0.85);border:1px solid rgba(255,255,255,0.18);">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/></svg>
            Peta Sensor
          </a>
        </div>
      </div>
      <div class="grid grid-cols-2 gap-3 flex-shrink-0">
        <?php foreach ([['8','Sensor Aktif'],['240ha','Area Sawah'],['4 dtk','Update'],['99%','Uptime']] as [$n,$l]): ?>
        <div class="px-5 py-3 rounded-2xl text-center" style="background:rgba(255,255,255,0.07);border:1px solid rgba(255,255,255,0.12);backdrop-filter:blur(8px);">
          <div class="text-xl font-extrabold text-emerald-400 leading-none"><?= $n ?></div>
          <div class="text-xs font-medium mt-1" style="color:rgba(255,255,255,0.45);"><?= $l ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- AKSES CEPAT -->
  <div class="mb-3">
    <h2 class="text-sm font-bold text-slate-700">Akses Cepat</h2>
    <p class="text-xs text-slate-400 mt-0.5 mb-4">Navigasi langsung ke fitur utama sistem</p>
  </div>
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <?php foreach ([
      ['peta.php',    'Peta Sensor',     'Posisi & status 8 sensor di lapangan',     'Buka Peta',     '#15803D','#F0FDF4','<polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/>'],
      ['bps.php',     'Data BPS',        'Data luas panen & produktivitas padi',      'Lihat Data',    '#1D4ED8','#EFF6FF','<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/>'],
      ['riwayat.php', 'Riwayat Data',    'Historis pembacaan sensor & ekspor CSV',   'Lihat Riwayat', '#6D28D9','#F5F3FF','<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>'],
      ['#lapor',      'Laporan Kendala', 'Laporkan masalah irigasi ke petugas',       'Buat Laporan',  '#92400E','#FFFBEB','<path d="M14 2H6a2 2 0 0 0-2 2v16h16V8z"/><polyline points="14 2 14 8 20 8"/>'],
    ] as [$href,$title,$desc,$cta,$color,$bg,$ico]): ?>
    <a href="<?= $href ?>" class="bg-white rounded-2xl p-5 border flex flex-col gap-3 transition-all hover:-translate-y-1 hover:shadow-lg no-underline relative overflow-hidden"
       style="border-color:rgba(6,78,59,0.08);box-shadow:0 1px 3px rgba(6,78,59,0.05),0 4px 16px rgba(6,78,59,0.05);">
      <div class="absolute top-0 left-0 right-0 h-0.5" style="background:<?= $color ?>;opacity:0.6;"></div>
      <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background:<?= $bg ?>;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="<?= $color ?>" stroke-width="2"><?= $ico ?></svg>
      </div>
      <div>
        <div class="font-bold text-slate-800 text-sm"><?= $title ?></div>
        <div class="text-xs text-slate-400 mt-0.5 leading-relaxed"><?= $desc ?></div>
      </div>
      <div class="text-xs font-bold flex items-center gap-1 mt-auto" style="color:<?= $color ?>;">
        <?= $cta ?> <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
      </div>
    </a>
    <?php endforeach; ?>
  </div>

  <!-- TENTANG + INFO -->
  <div id="tentang" class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <!-- Tentang -->
    <div class="bg-white rounded-2xl p-6 border" style="border-color:rgba(6,78,59,0.08);box-shadow:0 1px 3px rgba(6,78,59,0.05),0 8px 24px rgba(6,78,59,0.06);">
      <h2 class="text-base font-bold text-emerald-900 mb-3 pb-2 inline-block border-b-2 border-emerald-500">Tentang Sistem</h2>
      <p class="text-sm text-slate-500 leading-relaxed mb-4">Platform berbasis web yang mengumpulkan data dari sensor jaringan irigasi sawah secara real-time untuk mendukung efisiensi pertanian.</p>
      <div class="grid grid-cols-2 gap-2">
        <?php foreach ([
          ['Monitor Debit','Sensor flow meter otomatis'],
          ['TMA Presisi','Sensor ultrasonik akurasi tinggi'],
          ['Notifikasi','Peringatan ambang batas aman'],
          ['Peta Visual','Posisi sensor di lapangan'],
        ] as [$t,$d]): ?>
        <div class="flex gap-2 p-2.5 rounded-xl" style="background:rgba(16,185,129,0.04);border:1px solid rgba(16,185,129,0.10);">
          <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 flex-shrink-0 mt-1.5"></div>
          <div class="text-xs text-slate-500"><strong class="text-slate-700"><?= $t ?></strong><br><?= $d ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Info Sistem -->
    <div class="bg-white rounded-2xl p-6 border" style="border-color:rgba(6,78,59,0.08);box-shadow:0 1px 3px rgba(6,78,59,0.05),0 8px 24px rgba(6,78,59,0.06);">
      <h2 class="text-base font-bold text-emerald-900 mb-4 pb-2 inline-block border-b-2 border-emerald-500">Informasi Sistem</h2>
      <table class="w-full text-sm border-collapse">
        <thead>
          <tr>
            <th class="py-2 px-3 text-left text-xs font-bold uppercase text-white rounded-l-lg" style="background:#064E3B;">#</th>
            <th class="py-2 px-3 text-left text-xs font-bold uppercase text-white" style="background:#064E3B;">Keterangan</th>
            <th class="py-2 px-3 text-left text-xs font-bold uppercase text-white rounded-r-lg" style="background:#064E3B;">Detail</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ([
            ['1','Nama Sistem','SM Irigasi'],
            ['2','Titik Sensor','8 Titik Aktif'],
            ['3','Jenis Sensor','Ultrasonik, DHT22, Flow Meter'],
            ['4','Komunikasi','LoRa / GSM / WiFi'],
            ['5','Update Interval','Setiap 4 detik'],
            ['6','Area Sawah','±240 Hektar'],
          ] as [$no,$k,$v]): ?>
          <tr class="hover:bg-emerald-50/40 transition-colors">
            <td class="py-2.5 px-3 text-emerald-600 font-bold border-b" style="border-color:rgba(6,78,59,0.06);"><?= $no ?></td>
            <td class="py-2.5 px-3 text-slate-500 border-b" style="border-color:rgba(6,78,59,0.06);"><?= $k ?></td>
            <td class="py-2.5 px-3 text-slate-700 font-medium border-b" style="border-color:rgba(6,78,59,0.06);"><?= $v ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ══════════ MONITORING TABLE ══════════ -->
  <div id="monitoring" class="bg-white rounded-2xl border overflow-hidden mb-8" style="border-color:rgba(6,78,59,0.08);box-shadow:0 1px 3px rgba(6,78,59,0.05),0 8px 24px rgba(6,78,59,0.06);">

    <!-- Header tabel -->
    <div class="flex items-center justify-between px-5 py-4 border-b" style="border-color:rgba(6,78,59,0.06);background:linear-gradient(135deg,#F0FDF4,#F8FAFC);">
      <div>
        <div class="font-bold text-slate-700 flex items-center gap-2">
          <span class="live-dot inline-block w-2 h-2 bg-emerald-500 rounded-full"></span>
          Data Monitoring Sensor Real-Time
        </div>
        <div class="text-xs text-slate-400 mt-0.5">Diperbarui setiap 4 detik · 8 titik sensor aktif</div>
      </div>
      <a href="peta.php" class="flex items-center gap-1 text-xs font-bold text-emerald-600 hover:text-emerald-800 transition-colors no-underline px-3 py-1.5 rounded-lg hover:bg-emerald-50">
        Lihat Peta <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
      </a>
    </div>

    <!-- Tabel scroll horizontal jika layar kecil -->
    <div class="overflow-x-auto">
      <table class="w-full border-collapse" id="tabel-sensor" style="min-width:640px;">
        <thead>
          <tr>
            <th style="width:44px;text-align:center;">No</th>
            <th style="width:90px;">ID</th>
            <th>Lokasi</th>
            <th style="width:110px;">Debit (L/dtk)</th>
            <th style="width:90px;">TMA (cm)</th>
            <th style="width:90px;">Suhu (°C)</th>
            <th style="width:90px;">Lembap (%)</th>
            <th style="width:100px;">Status</th>
            <th style="width:80px;">Waktu</th>
          </tr>
        </thead>
        <tbody id="isi-tabel"></tbody>
      </table>
    </div>

    <!-- Ringkasan bawah -->
    <div class="grid grid-cols-3 divide-x border-t" style="border-color:rgba(6,78,59,0.06);divide-color:rgba(6,78,59,0.06);">
      <div class="px-5 py-3">
        <div class="text-xs text-slate-400">Rata-rata Debit</div>
        <div class="text-base font-bold text-slate-700 mt-0.5"><span id="rata-debit">—</span> <span class="text-xs font-normal text-slate-400">L/dtk</span></div>
      </div>
      <div class="px-5 py-3">
        <div class="text-xs text-slate-400">Rata-rata TMA</div>
        <div class="text-base font-bold text-slate-700 mt-0.5"><span id="rata-tma">—</span> <span class="text-xs font-normal text-slate-400">cm</span></div>
      </div>
      <div class="px-5 py-3">
        <div class="text-xs text-slate-400">Status Normal</div>
        <div class="text-base font-bold text-emerald-600 mt-0.5"><span id="sensor-aman">—</span></div>
      </div>
    </div>
  </div>

  <!-- ══════════ FORM LAPORAN ══════════ -->
  <div id="lapor" class="bg-white rounded-2xl border overflow-hidden mb-8" style="border-color:rgba(6,78,59,0.08);box-shadow:0 1px 3px rgba(6,78,59,0.05),0 8px 24px rgba(6,78,59,0.06);">

    <!-- Header form -->
    <div class="px-6 py-4 border-b" style="border-color:rgba(6,78,59,0.06);background:linear-gradient(135deg,#F0FDF4,#F8FAFC);">
      <div class="font-bold text-slate-700 flex items-center gap-2">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        Laporan Kendala Irigasi
      </div>
      <div class="text-xs text-slate-400 mt-0.5">Petani atau petugas dapat melaporkan masalah irigasi melalui formulir berikut</div>
    </div>

    <div class="p-6">
      <form method="POST">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
          <div class="flex flex-col gap-1.5">
            <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Nama Pelapor</label>
            <input type="text" name="nama_pelapor" placeholder="Nama lengkap Anda"
                   value="<?= $namaLengkap ?>" required
                   class="px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 transition-all placeholder:text-slate-300">
          </div>
          <div class="flex flex-col gap-1.5">
            <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Lokasi Kendala</label>
            <input type="text" name="lokasi_kendala" placeholder="Contoh: Saluran Ngalor D" required
                   class="px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 transition-all placeholder:text-slate-300">
          </div>
        </div>
        <div class="flex flex-col gap-1.5 mb-5">
          <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Jenis Kendala</label>
          <select name="jenis_kendala" required
                  class="px-3 py-2.5 border border-slate-200 rounded-xl text-sm text-slate-700 outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 transition-all">
            <option value="">— Pilih Jenis Kendala —</option>
            <option>Debit air terlalu kecil</option>
            <option>Debit air terlalu besar / banjir</option>
            <option>Sensor tidak terbaca</option>
            <option>Saluran tersumbat</option>
            <option>Pintu air rusak</option>
            <option>Lainnya</option>
          </select>
        </div>
        <button type="submit" name="kirim_laporan"
          class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-bold text-white transition-all hover:-translate-y-0.5 active:translate-y-0"
          style="background:linear-gradient(135deg,#065F46,#064E3B);box-shadow:0 4px 16px rgba(6,78,59,0.25);">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
          Kirim Laporan
        </button>
      </form>

      <?php if ($pesan_laporan): ?>
      <div class="flex items-center gap-2 mt-4 px-4 py-3 rounded-xl text-sm font-medium"
           style="<?= $pesan_warna === 'sukses'
             ? 'background:#F0FDF4;border:1px solid #BBF7D0;color:#166534;'
             : 'background:#FEF2F2;border:1px solid #FECACA;color:#991B1B;' ?>">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <?= $pesan_warna === 'sukses'
            ? '<polyline points="20 6 9 17 4 12"/>'
            : '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>' ?>
        </svg>
        <?= htmlspecialchars($pesan_laporan) ?>
      </div>
      <?php endif; ?>
    </div>
  </div>

</div><!-- /max-w-6xl -->

<footer class="text-center py-5 text-xs" style="background:#064E3B;color:rgba(255,255,255,0.40);">
  © 2026 Sistem Monitoring Irigasi Sawah — Universitas Sebelas Maret
</footer>

<script>
var dataSensor = [
  {id:"SNS-01", lokasi:"Saluran Induk Ngidul", debit:12.4, tma:42, suhu:26.8, lembap:68, status:"normal"},
  {id:"SNS-02", lokasi:"Percabangan Blok A",   debit:8.7,  tma:35, suhu:27.1, lembap:72, status:"normal"},
  {id:"SNS-03", lokasi:"Saluran Blok B",       debit:3.2,  tma:18, suhu:28.3, lembap:45, status:"rendah"},
  {id:"SNS-04", lokasi:"Bak Penampungan C1",   debit:18.9, tma:71, suhu:26.2, lembap:80, status:"tinggi"},
  {id:"SNS-05", lokasi:"Saluran Ngalor D",     debit:6.5,  tma:28, suhu:27.8, lembap:63, status:"normal"},
  {id:"SNS-06", lokasi:"Saluran Ngetan E",     debit:1.1,  tma:10, suhu:29.0, lembap:31, status:"kritis"},
  {id:"SNS-07", lokasi:"Saluran Petak 12",     debit:9.3,  tma:38, suhu:26.5, lembap:70, status:"normal"},
  {id:"SNS-08", lokasi:"Embung Ngulon",        debit:7.8,  tma:32, suhu:27.4, lembap:66, status:"normal"}
];

var dotColor = {normal:"#10B981", rendah:"#F97316", tinggi:"#3B82F6", kritis:"#EF4444"};
var labelSt  = {normal:"Normal",  rendah:"Rendah",  tinggi:"Tinggi",  kritis:"Kritis!"};
var spClass  = {normal:"sp-normal", rendah:"sp-rendah", tinggi:"sp-tinggi", kritis:"sp-kritis"};

function waktu() {
  var n = new Date();
  return String(n.getHours()).padStart(2,'0') + ':' +
         String(n.getMinutes()).padStart(2,'0') + ':' +
         String(n.getSeconds()).padStart(2,'0');
}

function pill(s) {
  return '<span class="status-pill ' + spClass[s] + '">' +
         '<svg width="6" height="6" viewBox="0 0 6 6"><circle cx="3" cy="3" r="3" fill="' + dotColor[s] + '"/></svg>' +
         labelSt[s] + '</span>';
}

function renderTabel() {
  var html = '';
  dataSensor.forEach(function(s, i) {
    html += '<tr>';
    html += '<td style="text-align:center;color:#94a3b8;">' + (i+1) + '</td>';
    html += '<td style="font-weight:700;color:#059669;">' + s.id + '</td>';
    html += '<td>' + s.lokasi + '</td>';
    html += '<td style="font-variant-numeric:tabular-nums;">' + s.debit.toFixed(1) + '</td>';
    html += '<td style="font-variant-numeric:tabular-nums;">' + s.tma + '</td>';
    html += '<td style="font-variant-numeric:tabular-nums;">' + s.suhu.toFixed(1) + '</td>';
    html += '<td style="font-variant-numeric:tabular-nums;">' + s.lembap + '</td>';
    html += '<td>' + pill(s.status) + '</td>';
    html += '<td style="color:#94a3b8;font-size:0.75rem;">' + waktu() + '</td>';
    html += '</tr>';
  });
  document.getElementById('isi-tabel').innerHTML = html;
  hitungRingkasan();
}

function hitungRingkasan() {
  var td = 0, tt = 0, n = 0, c = dataSensor.length;
  dataSensor.forEach(function(s) { td += s.debit; tt += s.tma; if (s.status === 'normal') n++; });
  document.getElementById('rata-debit').textContent = (td/c).toFixed(1);
  document.getElementById('rata-tma').textContent   = Math.round(tt/c);
  document.getElementById('sensor-aman').textContent = n + ' dari ' + c + ' titik';
}

function perbaruiSensor() {
  dataSensor.forEach(function(s) {
    s.debit  = Math.max(0.5, s.debit + (Math.random() - 0.5));
    s.tma    = Math.max(5,   s.tma   + Math.round((Math.random() - 0.5) * 3));
    s.lembap = Math.min(100, Math.max(10, s.lembap + Math.round((Math.random() - 0.5) * 2)));
    if      (s.tma < 15) s.status = 'kritis';
    else if (s.tma < 25) s.status = 'rendah';
    else if (s.tma > 65) s.status = 'tinggi';
    else                  s.status = 'normal';
  });
  renderTabel();
}

renderTabel();
setInterval(perbaruiSensor, 4000);
</script>
</body>
</html>
