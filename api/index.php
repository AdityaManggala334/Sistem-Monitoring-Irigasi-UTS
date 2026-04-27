<?php
require_once 'koneksi.php';

// Ambil parameter dari GET (simple)
if (isset($_GET['user_id']) && isset($_GET['username'])) {
    $user_id = $_GET['user_id'];
    $username = $_GET['username'];
    $nama_depan = $_GET['nama_depan'] ?? '';
    $nama_belakang = $_GET['nama_belakang'] ?? '';
    $role = $_GET['role'] ?? 'petani';
    
    $namaDepan = htmlspecialchars($nama_depan ?: $username);
    $namaBelakang = htmlspecialchars($nama_belakang);
    $namaLengkap = trim($namaDepan . ' ' . $namaBelakang) ?: htmlspecialchars($username);
} else {
    header("Location: login.php");
    exit();
}

$pesan_laporan = ''; 
$pesan_warna = '';

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
<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          emerald: {
            950: '#022C22', 900: '#064E3B', 800: '#065F46',
            700: '#047857', 600: '#059669', 500: '#10B981', 400: '#34D399',
          }
        },
        fontFamily: { sans: ["'Plus Jakarta Sans'", 'sans-serif'] }
      }
    }
  }
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  html { scroll-behavior: smooth; }
  body { font-family: 'Plus Jakarta Sans', sans-serif; }
  @keyframes livePulse { 0%,100%{opacity:1} 50%{opacity:0.3} }
  .live-dot { animation: livePulse 2s ease-in-out infinite; }
  @keyframes fadeUp { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:translateY(0)} }
  .fade-up { animation: fadeUp 0.5s cubic-bezier(0.22,1,0.36,1) both; }
  .sp-normal{background:#F0FDF4;color:#15803D;border:1px solid #BBF7D0;}
  .sp-rendah{background:#FFF7ED;color:#C2410C;border:1px solid #FED7AA;}
  .sp-tinggi{background:#EFF6FF;color:#1D4ED8;border:1px solid #BFDBFE;}
  .sp-kritis{background:#FEF2F2;color:#B91C1C;border:1px solid #FCA5A5;}
  .status-pill{display:inline-flex;align-items:center;gap:5px;padding:4px 11px;border-radius:20px;font-size:0.72rem;font-weight:700;}
  /* Dropdown */
  .profil-wrap:hover .profil-dropdown { display:block; }
  .profil-dropdown { display:none; }
</style>
</head>
<body class="bg-slate-50 text-slate-800">

<!--  NAVBAR  -->
<nav class="sticky top-0 z-50 border-b" style="background:rgba(6,78,59,0.97);backdrop-filter:blur(16px);border-color:rgba(255,255,255,0.07);">
  <div class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between gap-4">

    <!-- Logo - SUDAH DI PERBAIKI -->
    <a href="/api/index.php?user_id=<?= $user_id ?>&username=<?= urlencode($username) ?>&nama_depan=<?= urlencode($nama_depan) ?>&nama_belakang=<?= urlencode($nama_belakang) ?>&role=<?= $role ?>" class="flex items-center gap-2.5 no-underline flex-shrink-0">
    <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background:rgba(16,185,129,0.18);border:1px solid rgba(52,211,153,0.25);">
        <svg width="20" height="20" viewBox="0 0 44 44" fill="none">
            <path d="M22 7C22 7 13 18 13 24C13 29.52 17.03 34 22 34C26.97 34 31 29.52 31 24C31 18 22 7 22 7Z" fill="#10B981"/>
            <line x1="18" y1="24" x2="26" y2="24" stroke="white" stroke-width="1.8" stroke-linecap="round"/>
            <circle cx="18" cy="24" r="1.4" fill="white"/><circle cx="26" cy="24" r="1.4" fill="white"/>
            <line x1="22" y1="20" x2="22" y2="28" stroke="white" stroke-width="1.8" stroke-linecap="round"/>
        </svg>
    </div>
    <span class="text-base font-extrabold text-white tracking-tight">SM Irigasi</span>
    </a>

    <!-- Nav links - SUDAH DI PERBAIKI -->
<div class="flex items-center gap-1">
    <a href="#tentang" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-all hover:bg-white/10" style="color:rgba(255,255,255,0.65);">Tentang</a>
    
    <a href="#monitoring" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-all hover:bg-white/10" style="color:rgba(255,255,255,0.65);">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
        Monitor
    </a>
    
    <a href="/api/peta.php?user_id=<?= $user_id ?>&username=<?= urlencode($username) ?>&nama_depan=<?= urlencode($nama_depan) ?>&nama_belakang=<?= urlencode($nama_belakang) ?>&role=<?= $role ?>" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-all hover:bg-white/10" style="color:rgba(255,255,255,0.65);">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/></svg>
        Peta
    </a>
    
    <a href="/api/bps.php?user_id=<?= $user_id ?>&username=<?= urlencode($username) ?>&nama_depan=<?= urlencode($nama_depan) ?>&nama_belakang=<?= urlencode($nama_belakang) ?>&role=<?= $role ?>" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-all hover:bg-white/10" style="color:rgba(255,255,255,0.65);">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
        Data BPS
    </a>
    
    <a href="/api/riwayat.php?user_id=<?= $user_id ?>&username=<?= urlencode($username) ?>&nama_depan=<?= urlencode($nama_depan) ?>&nama_belakang=<?= urlencode($nama_belakang) ?>&role=<?= $role ?>" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-all hover:bg-white/10" style="color:rgba(255,255,255,0.65);">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        Riwayat
    </a>
    
    <?php if ($role === 'administrator'): ?>
    <a href="/api/dashboard.php?user_id=<?= $user_id ?>&username=<?= urlencode($username) ?>&nama_depan=<?= urlencode($nama_depan) ?>&nama_belakang=<?= urlencode($nama_belakang) ?>&role=<?= $role ?>" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-all hover:bg-white/10" style="color:rgba(255,255,255,0.65);">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        Admin
    </a>
    <?php endif; ?>

      <!-- Profile dropdown -->
      <div class="profil-wrap relative ml-1">
        <button class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-medium transition-all hover:bg-white/10 bg-transparent border-none cursor-pointer font-sans" style="color:rgba(255,255,255,0.75);">
          <div class="w-7 h-7 rounded-lg flex items-center justify-center font-bold text-xs" style="background:rgba(16,185,129,0.25);color:#34D399;">
            <?= strtoupper(substr($namaDepan ?: 'U', 0, 1)) ?>
          </div>
          <?= $namaDepan ?>
          <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
        </button>
        <div class="profil-dropdown absolute right-0 top-full mt-2 bg-white rounded-2xl min-w-[200px] z-50 overflow-hidden" style="box-shadow:0 8px 32px rgba(0,0,0,0.14);">
          <div class="px-4 py-3 border-b" style="background:linear-gradient(135deg,#F0FDF6,#E6F9F0);border-color:rgba(6,78,59,0.08);">
            <div class="font-bold text-emerald-900 text-sm"><?= $namaLengkap ?></div>
            <div class="text-xs text-slate-500 mt-0.5 capitalize"><?= str_replace('_', ' ', $role) ?></div>
          </div>
          <a href="/api/logout.php" class="flex items-center gap-2 px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-colors no-underline">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Keluar
          </a>
        </div>
      </div>
    </div>
  </div>
</nav>

<!--  PAGE CONTENT  -->
<div class="max-w-6xl mx-auto px-6 py-8">

  <!--  HERO with Image  -->
  <div class="relative rounded-3xl overflow-hidden mb-8 fade-up" style="min-height:340px;">
    <!-- Background image -->
    <img src="/api/hero-irigasi.png" alt="Irigasi Sawah"
         class="absolute inset-0 w-full h-full object-cover object-center">
    <!-- Gradient overlay -->
    <div class="absolute inset-0" style="background:linear-gradient(100deg,rgba(2,44,34,0.88) 0%,rgba(6,78,59,0.65) 55%,rgba(6,78,59,0.20) 100%);"></div>
    <!-- Grid overlay -->
    <div class="absolute inset-0 opacity-5" style="background-image:linear-gradient(rgba(255,255,255,1) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,1) 1px,transparent 1px);background-size:48px 48px;"></div>

    <!-- Content -->
    <div class="relative z-10 flex items-center justify-between gap-8 p-10 flex-wrap" style="min-height:340px;">
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
          <a href="/api/peta.php?user_id=<?= $user_id ?>&username=<?= urlencode($username) ?>&nama_depan=<?= urlencode($nama_depan) ?>&nama_belakang=<?= urlencode($nama_belakang) ?>&role=<?= $role ?>" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold transition-all hover:bg-white/20" style="background:rgba(255,255,255,0.10);color:rgba(255,255,255,0.85);border:1px solid rgba(255,255,255,0.18);">
             <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/></svg>
             Peta Sensor
          </a>
        </div>
      </div>

      <!-- Hero stats -->
      <div class="grid grid-cols-2 gap-2.5 flex-shrink-0">
        <?php foreach ([
          ['8', 'Sensor Aktif'],
          ['240ha', 'Area Sawah'],
          ['4 dtk', 'Update'],
          ['99%', 'Uptime'],
        ] as [$n, $l]): ?>
        <div class="px-4 py-3 rounded-2xl text-center" style="background:rgba(255,255,255,0.07);border:1px solid rgba(255,255,255,0.12);backdrop-filter:blur(8px);">
          <div class="text-xl font-extrabold text-emerald-400 leading-none"><?= $n ?></div>
          <div class="text-xs font-medium mt-0.5" style="color:rgba(255,255,255,0.40);"><?= $l ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!--  Quick Access - SUDAH DI PERBAIKI -->
  <div class="mb-2">
    <h2 class="text-sm font-bold text-slate-700 mb-0.5">Akses Cepat</h2>
    <p class="text-xs text-slate-400 mb-4">Navigasi langsung ke fitur utama sistem</p>
  </div>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <?php foreach ([
      ['/api/peta.php?user_id='.$user_id.'&username='.urlencode($username).'&nama_depan='.urlencode($nama_depan).'&nama_belakang='.urlencode($nama_belakang).'&role='.$role, 'Peta Sensor', 'Lihat posisi dan status 8 sensor di lapangan secara visual interaktif.', 'Buka Peta', '#15803D', 'green', '<polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/><line x1="9" y1="3" x2="9" y2="18"/><line x1="15" y1="6" x2="15" y2="21"/>'],
      ['/api/bps.php?user_id='.$user_id.'&username='.urlencode($username).'&nama_depan='.urlencode($nama_depan).'&nama_belakang='.urlencode($nama_belakang).'&role='.$role, 'Data BPS', 'Data luas panen, produktivitas, dan produksi padi dari BPS Jawa Tengah.', 'Lihat Data', '#1D4ED8', 'blue', '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/>'],
      ['/api/riwayat.php?user_id='.$user_id.'&username='.urlencode($username).'&nama_depan='.urlencode($nama_depan).'&nama_belakang='.urlencode($nama_belakang).'&role='.$role, 'Riwayat Data', 'Analitik historis pembacaan sensor dengan filter dan ekspor CSV.', 'Lihat Riwayat', '#6D28D9', 'violet', '<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>'],
      ['#lapor', 'Laporan Kendala', 'Laporkan masalah irigasi agar petugas dapat segera menangani.', 'Buat Laporan', '#92400E', 'amber', '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>'],
    ]) as [$href, $title, $desc, $cta, $color, $scheme, $ico]: ?>
    <a href="<?= $href ?>" class="group bg-white rounded-2xl p-5 border transition-all hover:-translate-y-1 hover:shadow-lg no-underline flex flex-col gap-3 relative overflow-hidden"
       style="border-color:rgba(6,78,59,0.08);box-shadow:0 1px 3px rgba(6,78,59,0.05),0 8px 24px rgba(6,78,59,0.06);">
      <div class="absolute top-0 left-0 right-0 h-0.5 rounded-t-2xl"
           style="background:linear-gradient(90deg,<?= $color ?>,<?= $color ?>88);"></div>
      <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:<?= [
        'green'=>'#F0FDF4','blue'=>'#EFF6FF','amber'=>'#FFFBEB'
      ][$scheme] ?>;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="<?= $color ?>" stroke-width="2"><?= $ico ?></svg>
      </div>
      <div>
        <div class="font-bold text-slate-800 text-sm mb-1"><?= $title ?></div>
        <div class="text-xs text-slate-500 leading-relaxed"><?= $desc ?></div>
      </div>
      <div class="flex items-center gap-1.5 text-xs font-bold mt-auto" style="color:<?= $color ?>;">
        <?= $cta ?>
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
      </div>
    </a>
    <?php endforeach; ?>
  </div>

  <!--  Tentang + Info  -->
  <div id="tentang" class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-2xl p-6 border" style="border-color:rgba(6,78,59,0.08);box-shadow:0 1px 3px rgba(6,78,59,0.05),0 8px 24px rgba(6,78,59,0.06);">
      <h2 class="text-base font-bold text-emerald-900 mb-3 pb-2 inline-block border-b-2 border-emerald-500">Tentang Sistem</h2>
      <p class="text-sm text-slate-500 leading-relaxed mb-4">Platform berbasis web yang mengumpulkan dan menampilkan data dari sensor di jaringan irigasi sawah secara real-time untuk mendukung efisiensi pertanian.</p>
      <div class="grid grid-cols-2 gap-2.5">
        <?php foreach ([
          ['Monitor Debit', 'Sensor flow meter otomatis'],
          ['TMA Presisi', 'Sensor ultrasonik akurasi tinggi'],
          ['Notifikasi', 'Peringatan ambang batas aman'],
          ['Peta Visual', 'Posisi sensor di lapangan'],
        ] as [$t, $d]): ?>
        <div class="flex gap-2 p-2.5 rounded-xl" style="background:rgba(16,185,129,0.04);border:1px solid rgba(16,185,129,0.10);">
          <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 flex-shrink-0 mt-1.5"></div>
          <div class="text-xs text-slate-500 leading-relaxed"><strong class="text-slate-700"><?= $t ?></strong> — <?= $d ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="bg-white rounded-2xl p-6 border" style="border-color:rgba(6,78,59,0.08);box-shadow:0 1px 3px rgba(6,78,59,0.05),0 8px 24px rgba(6,78,59,0.06);">
      <h2 class="text-base font-bold text-emerald-900 mb-3 pb-2 inline-block border-b-2 border-emerald-500">Informasi Sistem</h2>
      <table class="w-full text-sm border-collapse">
        <thead><tr class="text-left">
          <th class="py-2 px-3 text-xs font-bold uppercase tracking-wide text-white rounded-l-xl" style="background:#064E3B;">#</th>
          <th class="py-2 px-3 text-xs font-bold uppercase tracking-wide text-white" style="background:#064E3B;">Keterangan</th>
          <th class="py-2 px-3 text-xs font-bold uppercase tracking-wide text-white rounded-r-xl" style="background:#064E3B;">Detail</th>
        </tr></thead>
        <tbody>
          <?php foreach ([
            ['1','Nama Sistem','SM Irigasi'],
            ['2','Titik Sensor','8 Titik Aktif'],
            ['3','Jenis Sensor','Ultrasonik, DHT22, Flow Meter'],
            ['4','Komunikasi','LoRa / GSM / WiFi'],
            ['5','Update Interval','Setiap 4 detik'],
            ['6','Area Sawah','±240 Hektar'],
          ] as [$no, $k, $v]): ?>
          <tr class="hover:bg-emerald-50/40 transition-colors">
            <td class="py-2.5 px-3 text-slate-400 text-xs border-b" style="border-color:rgba(6,78,59,0.06);"><?= $no ?></td>
            <td class="py-2.5 px-3 text-slate-600 border-b" style="border-color:rgba(6,78,59,0.06);"><?= $k ?></td>
            <td class="py-2.5 px-3 text-slate-700 font-medium border-b" style="border-color:rgba(6,78,59,0.06);"><?= $v ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!--  Monitoring Table  -->
  <div id="monitoring" class="bg-white rounded-2xl border overflow-hidden mb-8" style="border-color:rgba(6,78,59,0.08);box-shadow:0 1px 3px rgba(6,78,59,0.05),0 8px 24px rgba(6,78,59,0.06);">
    <div class="flex items-center justify-between px-5 py-3.5 border-b bg-slate-50/70" style="border-color:rgba(6,78,59,0.06);">
      <div>
        <div class="font-bold text-slate-700 text-sm">Data Monitoring Sensor Real-Time</div>
        <div class="text-xs text-slate-400 mt-0.5 flex items-center gap-1.5">
          <span class="live-dot inline-block w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
          Diperbarui setiap 4 detik · 8 titik sensor aktif
        </div>
      </div>
      <a href="/api/peta.php?user_id=<?= $user_id ?>&username=<?= urlencode($username) ?>&nama_depan=<?= urlencode($nama_depan) ?>&nama_belakang=<?= urlencode($nama_belakang) ?>&role=<?= $role ?>" class="flex items-center gap-1 text-xs font-bold text-emerald-500 hover:text-emerald-700 transition-colors no-underline">
        Lihat Peta <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
      </a>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm border-collapse" id="tabel-data">
        <thead>
          <tr>
            <?php foreach (['No','ID Sensor','Lokasi','Debit (L/dtk)','TMA (cm)','Suhu (°C)','Lembap (%)','Status','Waktu'] as $h): ?>
            <th class="py-2.5 px-3.5 text-left text-xs font-bold uppercase tracking-wider text-slate-400 bg-slate-50/80 border-b" style="border-color:rgba(6,78,59,0.06);"><?= $h ?></th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody id="isi-tabel"></tbody>
      </table>
    </div>
    <!-- Summary bar -->
    <div class="grid grid-cols-3 gap-4 px-5 py-3 bg-slate-50/70 border-t" style="border-color:rgba(6,78,59,0.06);">
      <div class="text-xs text-slate-400">Rata-rata Debit<div class="text-sm font-bold text-slate-700 mt-0.5"><span id="rata-debit">—</span> L/dtk</div></div>
      <div class="text-xs text-slate-400">Rata-rata TMA<div class="text-sm font-bold text-slate-700 mt-0.5"><span id="rata-tma">—</span> cm</div></div>
      <div class="text-xs text-slate-400">Status Normal<div class="text-sm font-bold text-slate-700 mt-0.5"><span id="sensor-aman">—</span></div></div>
    </div>
  </div>

  <!--  Form Laporan  -->
  <div id="lapor" class="bg-white rounded-2xl p-6 border mb-8" style="border-color:rgba(6,78,59,0.08);box-shadow:0 1px 3px rgba(6,78,59,0.05),0 8px 24px rgba(6,78,59,0.06);">
    <h2 class="font-bold text-slate-700 mb-1">Laporan Kendala Irigasi</h2>
    <p class="text-xs text-slate-400 mb-5">Petani atau petugas dapat melaporkan masalah irigasi melalui formulir berikut</p>

    <form method="POST">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
        <div class="flex flex-col gap-1.5">
          <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Nama Pelapor</label>
          <input type="text" name="nama_pelapor" placeholder="Nama lengkap Anda"
                 value="<?= $namaLengkap ?>" required
                 class="px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-700 outline-none transition-all focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 placeholder:text-slate-300">
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Lokasi Kendala</label>
          <input type="text" name="lokasi_kendala" placeholder="Contoh: Saluran Ngalor D" required
                 class="px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-700 outline-none transition-all focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 placeholder:text-slate-300">
        </div>
      </div>
      <div class="flex flex-col gap-1.5 mb-5">
        <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Jenis Kendala</label>
        <select name="jenis_kendala" required
                class="px-
