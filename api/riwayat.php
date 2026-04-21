<?php

session_start();
if (!isset($_SESSION['username'])) { header("Location: login.php"); exit(); }
$namaDepan = htmlspecialchars($_SESSION['nama_depan'] ?? $_SESSION['username'] ?? '');
$role      = $_SESSION['role'] ?? 'petani';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Riwayat Data — SM Irigasi</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    theme: {
      extend: {
        fontFamily: { sans: ["'Plus Jakarta Sans'", 'sans-serif'] },
        colors: { emerald: { 950:'#022C22',900:'#064E3B',800:'#065F46',700:'#047857',600:'#059669',500:'#10B981',400:'#34D399' }}
      }
    }
  }
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  body { font-family: 'Plus Jakarta Sans', sans-serif; }
  @keyframes livePulse { 0%,100%{opacity:1} 50%{opacity:0.3} }
  .live-dot { animation: livePulse 2s ease-in-out infinite; }
  .sp { display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:20px;font-size:0.68rem;font-weight:700; }
  .sp-normal{background:#F0FDF4;color:#15803D;border:1px solid #BBF7D0;}
  .sp-rendah{background:#FFF7ED;color:#C2410C;border:1px solid #FED7AA;}
  .sp-tinggi{background:#EFF6FF;color:#1D4ED8;border:1px solid #BFDBFE;}
  .sp-kritis{background:#FEF2F2;color:#B91C1C;border:1px solid #FCA5A5;}
</style>
</head>
<body class="min-h-screen flex flex-col bg-slate-50" style="color:#0A2218;">

<!-- NAV -->
<nav class="sticky top-0 z-50" style="background:#064E3B;box-shadow:0 2px 20px rgba(0,0,0,0.15);">
  <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
    <a href="index.php" class="flex items-center gap-2.5 no-underline">
      <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0" style="background:rgba(16,185,129,0.18);">
        <svg width="18" height="18" viewBox="0 0 44 44" fill="none">
          <path d="M22 7C22 7 13 18 13 24C13 29.52 17.03 34 22 34C26.97 34 31 29.52 31 24C31 18 22 7 22 7Z" fill="#10B981"/>
          <line x1="18" y1="24" x2="26" y2="24" stroke="white" stroke-width="1.8" stroke-linecap="round"/>
        </svg>
      </div>
      <div>
        <div class="text-base font-extrabold text-white tracking-tight leading-none">SM Irigasi</div>
        <div class="text-xs font-semibold uppercase tracking-widest" style="color:rgba(255,255,255,0.35);">Monitoring</div>
      </div>
    </a>
    <div class="flex items-center gap-1">
      <a href="index.php#monitoring" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-all hover:bg-white/10 no-underline" style="color:rgba(255,255,255,0.65);">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>Monitor
      </a>
      <a href="peta.php" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-all hover:bg-white/10 no-underline" style="color:rgba(255,255,255,0.65);">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/></svg>Peta
      </a>
      <a href="riwayat.php" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-semibold transition-all no-underline" style="background:rgba(16,185,129,0.20);color:#34D399;border-radius:9px;">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>Riwayat
      </a>
      <?php if ($role === 'administrator'): ?>
      <a href="dashboard.php" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-all hover:bg-white/10 no-underline" style="color:rgba(255,255,255,0.65);">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>Admin
      </a>
      <?php endif; ?>
      <a href="logout.php" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-semibold transition-all hover:bg-red-500/20 no-underline" style="background:rgba(239,68,68,0.12);color:rgba(255,180,180,0.9);">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>Keluar
      </a>
    </div>
  </div>
</nav>

<main class="flex-1 max-w-7xl mx-auto w-full px-6 py-7">

  <!-- Page header -->
  <div class="mb-5">
    <h1 class="text-2xl font-extrabold tracking-tight" style="color:#0A2218;">Riwayat Data Sensor</h1>
    <p class="text-sm text-slate-400 mt-0.5 flex items-center gap-1.5">
      <span class="live-dot inline-block w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
      Rekaman historis pembacaan sensor dalam 24 jam terakhir · Auto-refresh 8 detik
    </p>
  </div>

  <!-- Filter Card -->
  <div class="bg-white rounded-2xl px-5 py-4 border mb-5 flex flex-wrap items-end gap-4" style="border-color:rgba(6,78,59,0.08);box-shadow:0 1px 3px rgba(6,78,59,0.06),0 8px 24px rgba(6,78,59,0.07);">
    <div class="flex flex-col gap-1.5">
      <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Pilih Sensor</label>
      <select id="filter-sensor" onchange="renderTabel()"
        class="px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-700 outline-none transition-all focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 min-w-[180px]">
        <option value="semua">Semua Sensor</option>
        <option value="SNS-01">SNS-01 — Saluran Induk Ngidul</option>
        <option value="SNS-02">SNS-02 — Percabangan Blok A</option>
        <option value="SNS-03">SNS-03 — Saluran Blok B</option>
        <option value="SNS-04">SNS-04 — Bak Penampungan C1</option>
        <option value="SNS-05">SNS-05 — Saluran Ngalor D</option>
        <option value="SNS-06">SNS-06 — Saluran Ngetan E</option>
        <option value="SNS-07">SNS-07 — Saluran Petak 12</option>
        <option value="SNS-08">SNS-08 — Embung Ngulon</option>
      </select>
    </div>
    <div class="flex flex-col gap-1.5">
      <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Status</label>
      <select id="filter-status" onchange="renderTabel()"
        class="px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-700 outline-none transition-all focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 min-w-[140px]">
        <option value="semua">Semua Status</option>
        <option value="normal">Normal</option>
        <option value="rendah">Rendah</option>
        <option value="tinggi">Tinggi</option>
        <option value="kritis">Kritis</option>
      </select>
    </div>
    <div class="flex flex-col gap-1.5">
      <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Cari Lokasi</label>
      <input type="text" id="filter-cari" oninput="renderTabel()" placeholder="Ketik nama lokasi..."
        class="px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-700 outline-none transition-all focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 min-w-[180px] placeholder:text-slate-300">
    </div>
    <button onclick="eksporCSV()"
      class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold text-white transition-all hover:-translate-y-0.5 ml-auto"
      style="background:linear-gradient(135deg,#065F46,#064E3B);box-shadow:0 3px 10px rgba(6,78,59,0.22);">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
      Ekspor CSV
    </button>
  </div>

  <!-- Bento: Chart + Stats -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">
    <!-- Chart -->
    <div class="lg:col-span-2 bg-white rounded-2xl p-5 border" style="border-color:rgba(6,78,59,0.08);box-shadow:0 1px 3px rgba(6,78,59,0.06),0 8px 24px rgba(6,78,59,0.07);">
      <div class="flex items-center justify-between mb-4">
        <div>
          <div class="font-bold text-slate-700 text-sm">Tren Debit Air — 8 Jam Terakhir</div>
          <div class="text-xs text-slate-400 mt-0.5">SNS-01 · Saluran Induk Ngidul</div>
        </div>
        <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold" style="background:#F0FDF4;border:1px solid #BBF7D0;color:#15803D;">
          <span class="live-dot inline-block w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>Live
        </div>
      </div>
      <!-- Chart bars -->
      <div class="flex items-end gap-1.5 h-36 px-1" id="grafik-container"></div>
    </div>

    <!-- Stats -->
    <div class="flex flex-col gap-4">
      <?php foreach ([
        ['st-total', 'Total Catatan', 'entri data sensor'],
        ['st-tampil', 'Ditampilkan', 'setelah filter'],
        ['st-waktu', 'Terakhir Update', 'auto-refresh aktif'],
      ] as [$id, $lbl, $sub]): ?>
      <div class="bg-white rounded-2xl p-4 border" style="border-color:rgba(6,78,59,0.08);box-shadow:0 1px 3px rgba(6,78,59,0.06),0 8px 24px rgba(6,78,59,0.07);">
        <div class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1"><?= $lbl ?></div>
        <div class="text-2xl font-extrabold tracking-tight" style="color:#0A2218;" id="<?= $id ?>">—</div>
        <div class="text-xs text-slate-400 mt-1"><?= $sub ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Table -->
  <div class="bg-white rounded-2xl border overflow-hidden mb-6" style="border-color:rgba(6,78,59,0.08);box-shadow:0 1px 3px rgba(6,78,59,0.06),0 8px 24px rgba(6,78,59,0.07);">
    <div class="flex items-center justify-between px-5 py-3.5 border-b bg-slate-50/70" style="border-color:rgba(6,78,59,0.06);">
      <span class="font-bold text-slate-700 text-sm">Log Data Sensor</span>
      <span class="text-xs text-slate-400">Menampilkan <span id="jml-tampil" class="font-bold text-slate-600">—</span> dari <span id="jml-total" class="font-bold text-slate-600">—</span> catatan</span>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm border-collapse">
        <thead>
          <tr>
            <?php foreach (['#','Waktu','Sensor','Lokasi','Debit (L/dtk)','TMA (cm)','Suhu (°C)','Lembap (%)','Status'] as $h): ?>
            <th class="py-2.5 px-3 text-left text-xs font-bold uppercase tracking-wider text-slate-400 bg-slate-50/80 border-b" style="border-color:rgba(6,78,59,0.06);"><?= $h ?></th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody id="tbody-riwayat">
          <tr><td colspan="9" class="text-center py-12 text-slate-300 text-sm">Memuat data...</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</main>

<footer class="text-center py-4 text-xs" style="background:#064E3B;color:rgba(255,255,255,0.40);">
  © 2026 SM Irigasi — Universitas Sebelas Maret · Sistem Monitoring Irigasi Sawah
</footer>

<script>
var sensorBase=[
  {id:"SNS-01",lokasi:"Saluran Induk Ngidul",debit:12.4,tma:42,suhu:26.8,lembap:68},
  {id:"SNS-02",lokasi:"Percabangan Blok A",debit:8.7,tma:35,suhu:27.1,lembap:72},
  {id:"SNS-03",lokasi:"Saluran Blok B",debit:3.2,tma:18,suhu:28.3,lembap:45},
  {id:"SNS-04",lokasi:"Bak Penampungan C1",debit:18.9,tma:71,suhu:26.2,lembap:80},
  {id:"SNS-05",lokasi:"Saluran Ngalor D",debit:6.5,tma:28,suhu:27.8,lembap:63},
  {id:"SNS-06",lokasi:"Saluran Ngetan E",debit:1.1,tma:10,suhu:29.0,lembap:31},
  {id:"SNS-07",lokasi:"Saluran Petak 12",debit:9.3,tma:38,suhu:26.5,lembap:70},
  {id:"SNS-08",lokasi:"Embung Ngulon",debit:7.8,tma:32,suhu:27.4,lembap:66}
];
function getStatus(tma){if(tma<15)return'kritis';if(tma<25)return'rendah';if(tma>65)return'tinggi';return'normal';}
var riwayatData=[];
var now=new Date();
for(var h=23;h>=0;h--){
  var waktu=new Date(now.getTime()-h*60*60000);
  var wStr=String(waktu.getHours()).padStart(2,'0')+':'+String(waktu.getMinutes()).padStart(2,'0');
  var idx=h%sensorBase.length;var s=sensorBase[idx];
  var varTma=Math.max(5,s.tma+Math.round((Math.random()-0.5)*10));
  riwayatData.push({waktu:wStr,id:s.id,lokasi:s.lokasi,debit:s.debit+(Math.random()-0.5)*3,tma:varTma,suhu:s.suhu+(Math.random()-0.5)*1.5,lembap:Math.min(100,Math.max(10,s.lembap+Math.round((Math.random()-0.5)*8))),status:getStatus(varTma)});
}
sensorBase.forEach(function(s){
  var varTma=Math.max(5,s.tma+Math.round((Math.random()-0.5)*5));
  riwayatData.unshift({waktu:String(now.getHours()).padStart(2,'0')+':'+String(now.getMinutes()).padStart(2,'0'),id:s.id,lokasi:s.lokasi,debit:s.debit+(Math.random()-0.5),tma:varTma,suhu:s.suhu,lembap:s.lembap,status:getStatus(varTma)});
});
function filterData(){
  var fS=document.getElementById('filter-sensor').value;
  var fSt=document.getElementById('filter-status').value;
  var fC=document.getElementById('filter-cari').value.toLowerCase();
  return riwayatData.filter(function(r){return(fS==='semua'||r.id===fS)&&(fSt==='semua'||r.status===fSt)&&(!fC||r.lokasi.toLowerCase().includes(fC)||r.id.toLowerCase().includes(fC));});
}
var spStyle={
  normal:"background:#F0FDF4;color:#15803D;border:1px solid #BBF7D0;",
  rendah:"background:#FFF7ED;color:#C2410C;border:1px solid #FED7AA;",
  tinggi:"background:#EFF6FF;color:#1D4ED8;border:1px solid #BFDBFE;",
  kritis:"background:#FEF2F2;color:#B91C1C;border:1px solid #FCA5A5;"
};
var spDot={normal:"#10B981",rendah:"#F97316",tinggi:"#3B82F6",kritis:"#EF4444"};
var labelSt={normal:"Normal",rendah:"Rendah",tinggi:"Tinggi",kritis:"Kritis!"};
function renderTabel(){
  var data=filterData();var html='';
  data.forEach(function(r,i){
    html+='<tr style="border-bottom:1px solid rgba(6,78,59,0.04);" onmouseover="this.style.background=\'rgba(16,185,129,0.025)\'" onmouseout="this.style.background=\'\'">';
    html+='<td class="py-2.5 px-3 text-slate-400">'+(i+1)+'</td>';
    html+='<td class="py-2.5 px-3 tabular-nums text-slate-600 text-xs">'+r.waktu+'</td>';
    html+='<td class="py-2.5 px-3"><span class="text-xs font-bold px-2 py-0.5 rounded-lg" style="background:#F0FDF4;color:#15803D;border:1px solid #BBF7D0;">'+r.id+'</span></td>';
    html+='<td class="py-2.5 px-3 text-slate-600 text-sm">'+r.lokasi+'</td>';
    html+='<td class="py-2.5 px-3 tabular-nums font-semibold text-right">'+r.debit.toFixed(2)+'</td>';
    html+='<td class="py-2.5 px-3 tabular-nums text-right">'+r.tma+'</td>';
    html+='<td class="py-2.5 px-3 tabular-nums text-right">'+r.suhu.toFixed(1)+'</td>';
    html+='<td class="py-2.5 px-3 tabular-nums text-right">'+r.lembap+'</td>';
    html+='<td class="py-2.5 px-3 text-center"><span class="sp" style="'+spStyle[r.status]+'"><svg width="5" height="5" viewBox="0 0 6 6"><circle cx="3" cy="3" r="3" fill="'+spDot[r.status]+'"/></svg>'+labelSt[r.status]+'</span></td>';
    html+='</tr>';
  });
  if(!data.length)html='<tr><td colspan="9" class="text-center py-12 text-slate-300 text-sm">Tidak ada data yang cocok dengan filter.</td></tr>';
  document.getElementById('tbody-riwayat').innerHTML=html;
  document.getElementById('jml-tampil').textContent=data.length;
  document.getElementById('jml-total').textContent=riwayatData.length;
  document.getElementById('st-total').textContent=riwayatData.length;
  document.getElementById('st-tampil').textContent=data.length;
  var nn=new Date();document.getElementById('st-waktu').textContent=String(nn.getHours()).padStart(2,'0')+':'+String(nn.getMinutes()).padStart(2,'0')+':'+String(nn.getSeconds()).padStart(2,'0');
}
function renderGrafik(){
  var d1=riwayatData.filter(function(r){return r.id==='SNS-01';}).slice(0,8).reverse();
  if(!d1.length)return;
  var maxD=Math.max.apply(null,d1.map(function(r){return r.debit;}))+1;
  var wMap={kritis:"#EF4444",rendah:"#F97316",tinggi:"#3B82F6",normal:"#10B981"};
  var cHtml='';
  d1.forEach(function(r){
    var pct=Math.max(8,(r.debit/maxD)*100);
    cHtml+='<div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:4px;">';
    cHtml+='<div style="width:100%;height:'+pct+'%;background:'+wMap[r.status]+';border-radius:6px 6px 0 0;min-height:8px;position:relative;" title="'+r.debit.toFixed(1)+'"></div>';
    cHtml+='<div style="font-size:9px;color:#94A3B8;font-weight:500;white-space:nowrap;">'+r.waktu+'</div>';
    cHtml+='</div>';
  });
  document.getElementById('grafik-container').innerHTML=cHtml;
}
function eksporCSV(){
  var data=filterData();
  var csv='Waktu,ID Sensor,Lokasi,Debit (L/dtk),TMA (cm),Suhu (C),Lembap (%),Status\n';
  data.forEach(function(r){csv+=[r.waktu,r.id,'"'+r.lokasi+'"',r.debit.toFixed(2),r.tma,r.suhu.toFixed(1),r.lembap,r.status].join(',')+'\\n';});
  var blob=new Blob([csv],{type:'text/csv;charset=utf-8;'});
  var a=document.createElement('a');a.href=URL.createObjectURL(blob);a.download='riwayat-sensor-'+new Date().toISOString().slice(0,10)+'.csv';a.click();
}
renderTabel();renderGrafik();
setInterval(function(){
  var s=sensorBase[Math.floor(Math.random()*sensorBase.length)];
  var varTma=Math.max(5,s.tma+Math.round((Math.random()-0.5)*8));
  var n2=new Date();
  riwayatData.unshift({waktu:String(n2.getHours()).padStart(2,'0')+':'+String(n2.getMinutes()).padStart(2,'0')+':'+String(n2.getSeconds()).padStart(2,'0'),id:s.id,lokasi:s.lokasi,debit:s.debit+(Math.random()-0.5),tma:varTma,suhu:s.suhu,lembap:s.lembap,status:getStatus(varTma)});
  if(riwayatData.length>200)riwayatData.pop();
  renderTabel();renderGrafik();
},8000);
</script>
</body>
</html>
