<?php

session_start();
require_once 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'administrator') {
    header("Location: index.php"); exit();
}

$adminNama = trim(($_SESSION['nama_depan']??'').' '.($_SESSION['nama_belakang']??'')) ?: ($_SESSION['username']??'Admin');
$adminId   = (int)($_SESSION['user_id']??0);

/*  POST actions  */
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['aksi'])) {
    if ($_POST['aksi']==='ubah_role') {
        $id_t=$_POST['id_user']??0; $r=$_POST['role']??'';
        $ok=['petani','petugas_lapangan','koordinator_irigasi','administrator'];
        if ($id_t>0 && in_array($r,$ok)) {
            $st=mysqli_prepare($conn,"UPDATE users SET role=? WHERE id_users=?");
            mysqli_stmt_bind_param($st,'si',$r,$id_t);
            mysqli_stmt_execute($st); mysqli_stmt_close($st);
        }
        header("Location: dashboard.php?msg=role_ok"); exit();
    }
    if ($_POST['aksi']==='hapus_user') {
        $id_t=(int)($_POST['id_user']??0);
        if ($id_t===$adminId){header("Location: dashboard.php?msg=self_err");exit();}
        if ($id_t>0){$st=mysqli_prepare($conn,"DELETE FROM users WHERE id_users=?");mysqli_stmt_bind_param($st,'i',$id_t);mysqli_stmt_execute($st);mysqli_stmt_close($st);}
        header("Location: dashboard.php?msg=del_ok"); exit();
    }
    if ($_POST['aksi']==='ubah_status_laporan') {
        $id_l=(int)($_POST['id_laporan']??0); $s=$_POST['status']??'';
        if ($id_l>0 && in_array($s,['baru','ditangani','selesai'])){
            $st=mysqli_prepare($conn,"UPDATE laporan_kendala SET status=? WHERE id_laporan=?");
            mysqli_stmt_bind_param($st,'si',$s,$id_l); mysqli_stmt_execute($st); mysqli_stmt_close($st);
        }
        header("Location: dashboard.php?msg=status_ok#laporan"); exit();
    }
}

/*  Data queries  */
$users    = mysqli_query($conn,"SELECT * FROM users ORDER BY created_at DESC");
$laporan  = mysqli_query($conn,"SELECT lk.*,u.username FROM laporan_kendala lk LEFT JOIN users u ON lk.id_users=u.id_users ORDER BY lk.created_at DESC");
$totalU   = (int)mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM users"))['c'];
$totalL   = (int)mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM laporan_kendala"))['c'];
$newL     = (int)mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM laporan_kendala WHERE status='baru'"))['c'];
$admCnt   = (int)mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM users WHERE role='administrator'"))['c'];
$roleList = ['petani','petugas_lapangan','koordinator_irigasi','administrator'];

function lRole(string $r):string{ return match($r){'petani'=>'Petani','petugas_lapangan'=>'Petugas Lapangan','koordinator_irigasi'=>'Koordinator Irigasi','administrator'=>'Administrator',default=>$r}; }
function bRole(string $r):string{ return match($r){'petani'=>'badge-petani','petugas_lapangan'=>'badge-petugas','koordinator_irigasi'=>'badge-koordinator','administrator'=>'badge-admin',default=>''}; }

/* Sensor data (hardcoded - replace with DB in production) */
$sensors=[
    ['id'=>'SNS-01','lokasi'=>'Saluran Induk Ngidul','debit'=>12.4,'tma'=>42,'suhu'=>26.8,'lembap'=>68,'status'=>'normal'],
    ['id'=>'SNS-02','lokasi'=>'Percabangan Blok A','debit'=>8.7,'tma'=>35,'suhu'=>27.1,'lembap'=>72,'status'=>'normal'],
    ['id'=>'SNS-03','lokasi'=>'Saluran Blok B','debit'=>3.2,'tma'=>18,'suhu'=>28.3,'lembap'=>45,'status'=>'rendah'],
    ['id'=>'SNS-04','lokasi'=>'Bak Penampungan C1','debit'=>18.9,'tma'=>71,'suhu'=>26.2,'lembap'=>80,'status'=>'tinggi'],
    ['id'=>'SNS-05','lokasi'=>'Saluran Ngalor D','debit'=>6.5,'tma'=>28,'suhu'=>27.8,'lembap'=>63,'status'=>'normal'],
    ['id'=>'SNS-06','lokasi'=>'Saluran Ngetan E','debit'=>1.1,'tma'=>10,'suhu'=>29.0,'lembap'=>31,'status'=>'kritis'],
    ['id'=>'SNS-07','lokasi'=>'Saluran Petak 12','debit'=>9.3,'tma'=>38,'suhu'=>26.5,'lembap'=>70,'status'=>'normal'],
    ['id'=>'SNS-08','lokasi'=>'Embung Ngulon','debit'=>7.8,'tma'=>32,'suhu'=>27.4,'lembap'=>66,'status'=>'normal'],
];
$avgDebit=round(array_sum(array_column($sensors,'debit'))/count($sensors),1);
$avgTMA  =round(array_sum(array_column($sensors,'tma'))/count($sensors));
$normalCnt=count(array_filter($sensors,fn($s)=>$s['status']==='normal'));
$kritisCnt=count(array_filter($sensors,fn($s)=>$s['status']==='kritis'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin — SM Irigasi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        :root{
            --bg:#F0FDF4;
            --sidebar:#064E3B;
            --sidebar-hover:rgba(255,255,255,0.08);
            --sidebar-active:rgba(16,185,129,0.18);
            --mint:#10B981;
            --mint-l:#34D399;
            --emerald:#064E3B;
            --card:#FFFFFF;
            --border:rgba(6,78,59,0.08);
            --txt:#0A2218;
            --txt2:#4B7563;
            --muted:#94A3B8;
            --shadow:0 1px 3px rgba(6,78,59,0.06),0 8px 24px rgba(6,78,59,0.07);
            --shadow-lg:0 4px 6px rgba(6,78,59,0.04),0 20px 50px rgba(6,78,59,0.10);
        }
        body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);color:var(--txt);display:flex;min-height:100vh;overflow-x:hidden;}

        /*  SIDEBAR  */
        .sidebar{
            width:240px;min-height:100vh;
            background:var(--sidebar);
            display:flex;flex-direction:column;
            position:sticky;top:0;flex-shrink:0;
            box-shadow:4px 0 20px rgba(0,0,0,0.12);
        }
        .sb-logo{padding:1.5rem 1.25rem;border-bottom:1px solid rgba(255,255,255,0.08);}
        .sb-logo-inner{display:flex;align-items:center;gap:10px;}
        .sb-logo-txt{font-size:1.1rem;font-weight:800;color:white;letter-spacing:-0.02em;line-height:1.1;}
        .sb-logo-sub{font-size:0.63rem;color:rgba(255,255,255,0.35);font-weight:600;letter-spacing:0.09em;text-transform:uppercase;}
        .sb-section{padding:1.1rem 1rem 0.4rem;font-size:0.65rem;font-weight:700;color:rgba(255,255,255,0.28);letter-spacing:0.1em;text-transform:uppercase;}
        .sb-item{
            display:flex;align-items:center;gap:10px;
            padding:10px 1rem;margin:2px 8px;border-radius:10px;
            color:rgba(255,255,255,0.60);font-size:0.875rem;font-weight:500;
            text-decoration:none;cursor:pointer;border:none;background:none;
            font-family:inherit;width:calc(100% - 16px);text-align:left;
            transition:all 0.18s ease;
        }
        .sb-item:hover{background:var(--sidebar-hover);color:rgba(255,255,255,0.85);}
        .sb-item.active{background:var(--sidebar-active);color:var(--mint-l);font-weight:600;}
        .sb-item svg{flex-shrink:0;opacity:0.8;}
        .sb-item.active svg{opacity:1;}
        .sb-badge{margin-left:auto;background:rgba(239,68,68,0.9);color:white;font-size:0.65rem;font-weight:700;padding:2px 7px;border-radius:20px;}
        .sb-footer{margin-top:auto;padding:1rem;border-top:1px solid rgba(255,255,255,0.07);}
        .sb-user{display:flex;align-items:center;gap:9px;padding:8px;border-radius:10px;cursor:pointer;transition:background 0.18s;}
        .sb-user:hover{background:var(--sidebar-hover);}
        .sb-avatar{width:34px;height:34px;border-radius:9px;background:rgba(16,185,129,0.25);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.85rem;color:var(--mint-l);flex-shrink:0;}
        .sb-uname{font-size:0.82rem;font-weight:600;color:rgba(255,255,255,0.85);line-height:1.2;}
        .sb-urole{font-size:0.65rem;color:rgba(255,255,255,0.35);font-weight:500;text-transform:capitalize;}

        /*  MAIN CONTENT  */
        .main{flex:1;display:flex;flex-direction:column;overflow:hidden;}

        /*  TOP BAR  */
        .topbar{
            background:rgba(255,255,255,0.82);
            backdrop-filter:blur(16px);
            border-bottom:1px solid var(--border);
            padding:0 1.75rem;height:62px;
            display:flex;align-items:center;justify-content:space-between;
            position:sticky;top:0;z-index:50;
        }
        .topbar-title{font-size:1rem;font-weight:700;color:var(--txt);}
        .topbar-sub{font-size:0.78rem;color:var(--muted);margin-top:1px;}
        .topbar-right{display:flex;align-items:center;gap:10px;}
        .notif-btn{position:relative;width:38px;height:38px;border-radius:10px;border:1px solid var(--border);background:white;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--txt2);transition:all 0.18s;}
        .notif-btn:hover{background:var(--bg);border-color:var(--mint);}
        .notif-dot{position:absolute;top:7px;right:7px;width:8px;height:8px;background:#EF4444;border-radius:50%;border:1.5px solid white;}
        .logout-btn{display:flex;align-items:center;gap:6px;padding:8px 14px;border-radius:10px;background:rgba(239,68,68,0.07);border:1px solid rgba(239,68,68,0.15);color:#DC2626;font-size:0.82rem;font-weight:600;cursor:pointer;font-family:inherit;text-decoration:none;transition:all 0.18s;}
        .logout-btn:hover{background:rgba(239,68,68,0.12);}

        /*  CONTENT AREA  */
        .content{padding:1.75rem;flex:1;overflow-y:auto;}

        /*  ALERT MSG  */
        .flash{padding:11px 16px;border-radius:12px;font-size:0.855rem;font-weight:500;margin-bottom:1.25rem;display:flex;align-items:center;gap:9px;}
        .flash-ok{background:#F0FDF4;border:1px solid #BBF7D0;color:#166534;}
        .flash-err{background:#FEF2F2;border:1px solid #FECACA;color:#991B1B;}

        /*  BENTO KPI GRID  */
        .kpi-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem;}
        .kpi-card{
            background:var(--card);border-radius:16px;
            padding:1.25rem 1.3rem;border:1px solid var(--border);
            box-shadow:var(--shadow);
            transition:transform 0.2s ease,box-shadow 0.2s ease;
            display:flex;flex-direction:column;gap:10px;
        }
        .kpi-card:hover{transform:translateY(-2px);box-shadow:var(--shadow-lg);}
        .kpi-icon{width:40px;height:40px;border-radius:11px;display:flex;align-items:center;justify-content:center;}
        .kpi-num{font-size:1.9rem;font-weight:800;color:var(--txt);letter-spacing:-0.03em;line-height:1;}
        .kpi-label{font-size:0.78rem;font-weight:600;color:var(--txt2);margin-top:2px;}
        .kpi-sub{font-size:0.72rem;color:var(--muted);}

        /*  BENTO SENSOR GRID  */
        .bento-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem;}
        .sensor-tile{
            background:var(--card);border-radius:16px;padding:1.1rem;
            border:1px solid var(--border);box-shadow:var(--shadow);
            position:relative;overflow:hidden;
            transition:transform 0.2s,box-shadow 0.2s;
        }
        .sensor-tile:hover{transform:translateY(-2px);box-shadow:var(--shadow-lg);}
        .sensor-tile::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;border-radius:16px 16px 0 0;}
        .tile-normal::before{background:linear-gradient(90deg,#10B981,#34D399);}
        .tile-rendah::before{background:linear-gradient(90deg,#F97316,#FDBA74);}
        .tile-tinggi::before{background:linear-gradient(90deg,#3B82F6,#93C5FD);}
        .tile-kritis::before{background:linear-gradient(90deg,#EF4444,#FCA5A5);}
        .tile-id{font-size:0.7rem;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:var(--muted);margin-bottom:6px;}
        .tile-loc{font-size:0.82rem;font-weight:600;color:var(--txt);margin-bottom:10px;line-height:1.3;}
        .tile-stats{display:grid;grid-template-columns:1fr 1fr;gap:6px;}
        .tile-stat{background:rgba(6,78,59,0.04);border-radius:8px;padding:7px 8px;}
        .tile-stat-val{font-size:0.95rem;font-weight:700;color:var(--txt);}
        .tile-stat-lbl{font-size:0.65rem;color:var(--muted);font-weight:500;margin-top:1px;}
        .status-pill{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;font-size:0.7rem;font-weight:700;letter-spacing:0.04em;text-transform:uppercase;margin-top:8px;}
        .sp-normal{background:#F0FDF4;color:#15803D;border:1px solid #BBF7D0;}
        .sp-rendah{background:#FFF7ED;color:#C2410C;border:1px solid #FED7AA;}
        .sp-tinggi{background:#EFF6FF;color:#1D4ED8;border:1px solid #BFDBFE;}
        .sp-kritis{background:#FEF2F2;color:#B91C1C;border:1px solid #FCA5A5;}

        /*  SECTION CARDS  */
        .section-card{background:var(--card);border-radius:18px;border:1px solid var(--border);box-shadow:var(--shadow);margin-bottom:1.5rem;overflow:hidden;}
        .sc-head{padding:1.1rem 1.4rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;}
        .sc-title{font-size:0.95rem;font-weight:700;color:var(--txt);}
        .sc-sub{font-size:0.78rem;color:var(--muted);margin-top:2px;}
        .sc-badge{padding:4px 12px;border-radius:20px;font-size:0.72rem;font-weight:700;}
        .badge-red{background:#FEF2F2;color:#B91C1C;border:1px solid #FECACA;}
        .badge-green{background:#F0FDF4;color:#15803D;border:1px solid #BBF7D0;}

        /*  TABLE  */
        .data-table{width:100%;border-collapse:collapse;font-size:0.855rem;}
        .data-table th{padding:10px 14px;text-align:left;font-size:0.72rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:0.06em;background:#FAFAFA;border-bottom:1px solid var(--border);}
        .data-table td{padding:11px 14px;border-bottom:1px solid rgba(6,78,59,0.05);vertical-align:middle;}
        .data-table tr:last-child td{border-bottom:none;}
        .data-table tr:hover td{background:rgba(16,185,129,0.03);}
        .data-table .avatar{width:32px;height:32px;border-radius:9px;background:linear-gradient(135deg,var(--emerald),#10B981);display:flex;align-items:center;justify-content:center;font-size:0.78rem;font-weight:700;color:white;}
        .you-tag{background:#F1F5F9;color:#64748B;font-size:0.65rem;font-weight:700;padding:2px 7px;border-radius:20px;margin-left:6px;letter-spacing:0.04em;}

        /* Role badges */
        .badge-petani{background:#F0FDF4;color:#15803D;border:1px solid #BBF7D0;}
        .badge-petugas{background:#EFF6FF;color:#1D4ED8;border:1px solid #BFDBFE;}
        .badge-koordinator{background:#FFF7ED;color:#C2410C;border:1px solid #FED7AA;}
        .badge-admin{background:#FDF4FF;color:#7E22CE;border:1px solid #E9D5FF;}
        .role-badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:0.72rem;font-weight:700;}

        /* Laporan status */
        .ls-baru{background:#FEF2F2;color:#B91C1C;border:1px solid #FECACA;}
        .ls-ditangani{background:#FFFBEB;color:#92400E;border:1px solid #FDE68A;}
        .ls-selesai{background:#F0FDF4;color:#15803D;border:1px solid #BBF7D0;}

        /* Form controls inside table */
        .tbl-select{font-family:'Plus Jakarta Sans',sans-serif;font-size:0.78rem;border:1px solid var(--border);border-radius:8px;padding:5px 8px;background:white;color:var(--txt);outline:none;cursor:pointer;transition:border-color 0.18s;}
        .tbl-select:focus{border-color:var(--mint);}
        .tbl-btn{padding:5px 12px;border-radius:8px;border:none;font-family:'Plus Jakarta Sans',sans-serif;font-size:0.78rem;font-weight:600;cursor:pointer;transition:all 0.18s;display:inline-flex;align-items:center;gap:5px;}
        .tbl-btn-green{background:#F0FDF4;color:#15803D;border:1px solid #BBF7D0;}
        .tbl-btn-green:hover{background:#10B981;color:white;border-color:#10B981;}
        .tbl-btn-red{background:#FEF2F2;color:#B91C1C;border:1px solid #FECACA;}
        .tbl-btn-red:hover{background:#EF4444;color:white;border-color:#EF4444;}
        .tbl-btn-blue{background:#EFF6FF;color:#1D4ED8;border:1px solid #BFDBFE;}
        .tbl-btn-blue:hover{background:#3B82F6;color:white;border-color:#3B82F6;}

        /* Section nav */
        .sec-nav{display:flex;gap:4px;border-bottom:1px solid var(--border);padding:0 1.4rem;background:white;}
        .sec-nav-btn{padding:11px 14px;background:none;border:none;border-bottom:2px solid transparent;cursor:pointer;font-family:inherit;font-size:0.855rem;font-weight:600;color:var(--muted);transition:all 0.18s;margin-bottom:-1px;display:flex;align-items:center;gap:6px;}
        .sec-nav-btn.active{color:var(--emerald);border-bottom-color:var(--mint);}
        .sec-nav-btn:hover{color:var(--txt);}
        .tab-content{display:none;padding:1.25rem 1.4rem;}
        .tab-content.active{display:block;}

        /* Live dot */
        @keyframes livePulse{0%,100%{opacity:1}50%{opacity:0.4}}
        .live-dot{display:inline-block;width:7px;height:7px;background:#10B981;border-radius:50%;animation:livePulse 2s infinite;margin-right:6px;}

        /* Responsive */
        @media(max-width:1024px){.kpi-grid{grid-template-columns:repeat(2,1fr)}.bento-grid{grid-template-columns:repeat(2,1fr)}}
        @media(max-width:768px){.sidebar{display:none}.kpi-grid{grid-template-columns:1fr 1fr}.bento-grid{grid-template-columns:1fr 1fr}}
        @media(max-width:480px){.kpi-grid{grid-template-columns:1fr}.bento-grid{grid-template-columns:1fr}}
    </style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sb-logo">
        <div class="sb-logo-inner">
            <svg width="36" height="36" viewBox="0 0 44 44" fill="none">
                <rect width="44" height="44" rx="11" fill="rgba(16,185,129,0.14)" stroke="rgba(52,211,153,0.22)" stroke-width="1"/>
                <path d="M22 7C22 7 13 18 13 24C13 29.52 17.03 34 22 34C26.97 34 31 29.52 31 24C31 18 22 7 22 7Z" fill="#10B981"/>
                <line x1="18" y1="24" x2="26" y2="24" stroke="white" stroke-width="1.6" stroke-linecap="round"/>
                <circle cx="18" cy="24" r="1.4" fill="white"/>
                <circle cx="26" cy="24" r="1.4" fill="white"/>
                <line x1="22" y1="20" x2="22" y2="28" stroke="white" stroke-width="1.6" stroke-linecap="round"/>
            </svg>
            <div>
                <div class="sb-logo-txt">SM Irigasi</div>
                <div class="sb-logo-sub">Admin Panel</div>
            </div>
        </div>
    </div>

    <div style="padding:0.5rem 0 0;">
        <div class="sb-section">Utama</div>
        <a href="index.php" class="sb-item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            Monitoring
        </a>
        <button class="sb-item active" onclick="showSection('overview')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            Overview
        </button>

        <div class="sb-section">Manajemen</div>
        <button class="sb-item" onclick="showSection('users')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Kelola Pengguna
        </button>
        <button class="sb-item" onclick="showSection('laporan')" style="position:relative">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            Laporan Kendala
            <?php if($newL>0): ?><span class="sb-badge"><?= $newL ?></span><?php endif; ?>
        </button>

        <div class="sb-section">Sensor</div>
        <a href="peta.php" class="sb-item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/><line x1="9" y1="3" x2="9" y2="18"/><line x1="15" y1="6" x2="15" y2="21"/></svg>
            Peta Sensor
        </a>
        <a href="riwayat.php" class="sb-item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            Riwayat Data
        </a>
    </div>

    <div class="sb-footer">
        <div class="sb-user">
            <div class="sb-avatar"><?= strtoupper(substr($adminNama,0,1)) ?></div>
            <div>
                <div class="sb-uname"><?= htmlspecialchars($adminNama) ?></div>
                <div class="sb-urole">Administrator</div>
            </div>
        </div>
        <a href="logout.php" style="display:flex;align-items:center;gap:8px;padding:8px 8px 0;color:rgba(255,255,255,0.35);font-size:0.78rem;text-decoration:none;transition:color 0.18s;" onmouseover="this.style.color='#FCA5A5'" onmouseout="this.style.color='rgba(255,255,255,0.35)'">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Keluar
        </a>
    </div>
</aside>

<!-- MAIN -->
<div class="main">

    <!-- TOP BAR -->
    <header class="topbar">
        <div>
            <div class="topbar-title" id="topbar-title">Overview Dashboard</div>
            <div class="topbar-sub"><span class="live-dot"></span>Data sensor aktif · <?= date('d M Y, H:i') ?></div>
        </div>
        <div class="topbar-right">
            <?php if($newL>0): ?>
            <button class="notif-btn" onclick="showSection('laporan')" title="<?= $newL ?> laporan baru">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                <span class="notif-dot"></span>
            </button>
            <?php endif; ?>
            <a href="logout.php" class="logout-btn">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Keluar
            </a>
        </div>
    </header>

    <!-- CONTENT -->
    <div class="content">

        <?php
        $msgs=['role_ok'=>['ok','Role pengguna berhasil diperbarui.'],'del_ok'=>['ok','Pengguna berhasil dihapus.'],'status_ok'=>['ok','Status laporan berhasil diperbarui.'],'self_err'=>['err','Tidak dapat menghapus akun sendiri.']];
        if(isset($_GET['msg']) && isset($msgs[$_GET['msg']])){[$type,$text]=$msgs[$_GET['msg']];
        echo '<div class="flash '.($type==='ok'?'flash-ok':'flash-err').'"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">'.($type==='ok'?'<polyline points="20 6 9 17 4 12"/>':'<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>').'</svg>'.htmlspecialchars($text).'</div>';}
        ?>

        <!--  SECTION: OVERVIEW  -->
        <div id="sec-overview" class="sec-active" style="display:block">

            <!-- KPI Row -->
            <div class="kpi-grid">
                <div class="kpi-card">
                    <div class="kpi-icon" style="background:#F0FDF4;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#15803D" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <div>
                        <div class="kpi-num"><?= $totalU ?></div>
                        <div class="kpi-label">Total Pengguna</div>
                        <div class="kpi-sub"><?= $admCnt ?> Administrator</div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon" style="background:#FEF2F2;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#B91C1C" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    </div>
                    <div>
                        <div class="kpi-num"><?= $totalL ?></div>
                        <div class="kpi-label">Total Laporan</div>
                        <div class="kpi-sub"><?= $newL ?> laporan baru</div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon" style="background:#EFF6FF;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1D4ED8" stroke-width="2"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/></svg>
                    </div>
                    <div>
                        <div class="kpi-num"><?= $avgDebit ?></div>
                        <div class="kpi-label">Rata-rata Debit</div>
                        <div class="kpi-sub">L/detik · 8 sensor</div>
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon" style="background:<?= $kritisCnt>0?'#FEF2F2':'#F0FDF4' ?>;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="<?= $kritisCnt>0?'#B91C1C':'#15803D' ?>" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    </div>
                    <div>
                        <div class="kpi-num" style="color:<?= $kritisCnt>0?'#B91C1C':'#15803D' ?>"><?= $normalCnt ?>/8</div>
                        <div class="kpi-label">Sensor Normal</div>
                        <div class="kpi-sub"><?= $kritisCnt ?> kritis<?= $kritisCnt>0?' — perlu perhatian':'' ?></div>
                    </div>
                </div>
            </div>

            <!-- Sensor Bento Grid -->
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.85rem;">
                <div>
                    <h2 style="font-size:0.95rem;font-weight:700;color:var(--txt);">Status Sensor Real-Time</h2>
                    <p style="font-size:0.78rem;color:var(--muted);margin-top:2px;">8 titik pantau aktif · update setiap 4 detik</p>
                </div>
                <a href="peta.php" style="display:flex;align-items:center;gap:5px;font-size:0.78rem;font-weight:600;color:var(--mint);text-decoration:none;">
                    Lihat Peta
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
            </div>

            <div class="bento-grid">
                <?php foreach($sensors as $s):
                    $spClass = 'sp-'.$s['status'];
                    $tileClass = 'tile-'.$s['status'];
                    $statusLabel = ['normal'=>'Normal','rendah'=>'Rendah','tinggi'=>'Tinggi','kritis'=>'Kritis'][$s['status']]??$s['status'];
                    $dotColor = ['normal'=>'#10B981','rendah'=>'#F97316','tinggi'=>'#3B82F6','kritis'=>'#EF4444'][$s['status']];
                ?>
                <div class="sensor-tile <?= $tileClass ?>">
                    <div class="tile-id"><?= $s['id'] ?></div>
                    <div class="tile-loc"><?= htmlspecialchars($s['lokasi']) ?></div>
                    <div class="tile-stats">
                        <div class="tile-stat">
                            <div class="tile-stat-val"><?= number_format($s['debit'],1) ?></div>
                            <div class="tile-stat-lbl">Debit L/dtk</div>
                        </div>
                        <div class="tile-stat">
                            <div class="tile-stat-val"><?= $s['tma'] ?></div>
                            <div class="tile-stat-lbl">TMA cm</div>
                        </div>
                        <div class="tile-stat">
                            <div class="tile-stat-val"><?= number_format($s['suhu'],1) ?>°</div>
                            <div class="tile-stat-lbl">Suhu C</div>
                        </div>
                        <div class="tile-stat">
                            <div class="tile-stat-val"><?= $s['lembap'] ?>%</div>
                            <div class="tile-stat-lbl">Lembap</div>
                        </div>
                    </div>
                    <span class="status-pill <?= $spClass ?>">
                        <svg width="6" height="6" viewBox="0 0 6 6"><circle cx="3" cy="3" r="3" fill="<?= $dotColor ?>"/></svg>
                        <?= $statusLabel ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>

        </div><!-- /sec-overview -->

        <!--  SECTION: USERS  -->
        <div id="sec-users" style="display:none">
            <div class="section-card">
                <div class="sc-head">
                    <div>
                        <div class="sc-title">Kelola Pengguna</div>
                        <div class="sc-sub">Ubah role atau hapus pengguna terdaftar</div>
                    </div>
                    <span class="sc-badge badge-green"><?= $totalU ?> pengguna</span>
                </div>
                <div style="overflow-x:auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Pengguna</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Terdaftar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php mysqli_data_seek($users,0); $no=1; while($u=mysqli_fetch_assoc($users)): ?>
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <div class="avatar"><?= strtoupper(substr($u['nama_depan'],0,1)) ?></div>
                                    <div>
                                        <div style="font-weight:600;font-size:0.875rem;">
                                            <?= htmlspecialchars(trim($u['nama_depan'].' '.$u['nama_belakang'])) ?>
                                            <?php if((int)$u['id_users']===$adminId): ?><span class="you-tag">ANDA</span><?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td style="color:var(--txt2);font-size:0.845rem;">@<?= htmlspecialchars($u['username']) ?></td>
                            <td style="color:var(--txt2);font-size:0.845rem;"><?= htmlspecialchars($u['email']) ?></td>
                            <td><span class="role-badge <?= bRole($u['role']) ?>"><?= lRole($u['role']) ?></span></td>
                            <td style="color:var(--muted);font-size:0.78rem;"><?= date('d M Y',strtotime($u['created_at'])) ?></td>
                            <td>
                                <div style="display:flex;align-items:center;gap:6px;">
                                    <form method="POST" style="display:flex;align-items:center;gap:5px;">
                                        <input type="hidden" name="aksi" value="ubah_role">
                                        <input type="hidden" name="id_user" value="<?= $u['id_users'] ?>">
                                        <select name="role" class="tbl-select">
                                            <?php foreach($roleList as $rl): ?>
                                            <option value="<?= $rl ?>" <?= $u['role']===$rl?'selected':'' ?>><?= lRole($rl) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="tbl-btn tbl-btn-green">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>Simpan
                                        </button>
                                    </form>
                                    <?php if((int)$u['id_users']!==$adminId): ?>
                                    <form method="POST" onsubmit="return confirm('Hapus pengguna <?= htmlspecialchars($u['username'],ENT_QUOTES) ?>?')">
                                        <input type="hidden" name="aksi" value="hapus_user">
                                        <input type="hidden" name="id_user" value="<?= $u['id_users'] ?>">
                                        <button type="submit" class="tbl-btn tbl-btn-red">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>Hapus
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div><!-- /sec-users -->

        <!--  SECTION: LAPORAN  -->
        <div id="sec-laporan" style="display:none">
            <div class="section-card">
                <div class="sc-head">
                    <div>
                        <div class="sc-title">Laporan Kendala dari Petani</div>
                        <div class="sc-sub">Pantau dan tangani masalah yang dilaporkan</div>
                    </div>
                    <?php if($newL>0): ?>
                    <span class="sc-badge badge-red"><?= $newL ?> laporan baru</span>
                    <?php else: ?>
                    <span class="sc-badge badge-green">Semua tertangani</span>
                    <?php endif; ?>
                </div>

                <?php if(mysqli_num_rows($laporan)===0): ?>
                <div style="padding:3rem;text-align:center;color:var(--muted);">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin:0 auto 12px;opacity:0.3;display:block;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    <p style="font-weight:600;margin-bottom:4px;">Belum ada laporan</p>
                    <p style="font-size:0.78rem;">Laporan dari petani akan muncul di sini</p>
                </div>
                <?php else: ?>
                <div style="overflow-x:auto;">
                    <table class="data-table">
                        <thead><tr><th>Pelapor</th><th>Lokasi</th><th>Kendala</th><th>Tanggal</th><th>Status</th><th>Aksi</th></tr></thead>
                        <tbody>
                        <?php while($lp=mysqli_fetch_assoc($laporan)):
                            $lsClass='ls-'.$lp['status'];
                            $lsLabel=['baru'=>'Baru','ditangani'=>'Ditangani','selesai'=>'Selesai'][$lp['status']]??$lp['status'];
                            $lsDot=['baru'=>'#EF4444','ditangani'=>'#F59E0B','selesai'=>'#10B981'][$lp['status']];
                        ?>
                        <tr>
                            <td>
                                <div style="font-weight:600;font-size:0.875rem;"><?= htmlspecialchars($lp['nama_pelapor']) ?></div>
                                <?php if($lp['username']): ?><div style="font-size:0.73rem;color:var(--muted);">@<?= htmlspecialchars($lp['username']) ?></div><?php endif; ?>
                            </td>
                            <td style="font-size:0.845rem;"><?= htmlspecialchars($lp['lokasi']) ?></td>
                            <td style="font-size:0.845rem;max-width:180px;"><?= htmlspecialchars($lp['jenis_kendala']) ?></td>
                            <td style="color:var(--muted);font-size:0.78rem;"><?= date('d M Y H:i',strtotime($lp['created_at'])) ?></td>
                            <td>
                                <span class="status-pill <?= $lsClass ?>" style="margin:0;">
                                    <svg width="6" height="6" viewBox="0 0 6 6"><circle cx="3" cy="3" r="3" fill="<?= $lsDot ?>"/></svg>
                                    <?= $lsLabel ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" style="display:flex;align-items:center;gap:5px;">
                                    <input type="hidden" name="aksi" value="ubah_status_laporan">
                                    <input type="hidden" name="id_laporan" value="<?= $lp['id_laporan'] ?>">
                                    <select name="status" class="tbl-select">
                                        <option value="baru"      <?= $lp['status']==='baru'     ?'selected':'' ?>>Baru</option>
                                        <option value="ditangani" <?= $lp['status']==='ditangani'?'selected':'' ?>>Ditangani</option>
                                        <option value="selesai"   <?= $lp['status']==='selesai'  ?'selected':'' ?>>Selesai</option>
                                    </select>
                                    <button type="submit" class="tbl-btn tbl-btn-blue">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 4 23 11 16 11"/><path d="M20.49 15a9 9 0 1 1-.18-4.96"/></svg>Update
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div><!-- /sec-laporan -->

    </div><!-- /content -->

    <footer style="padding:1rem 1.75rem;border-top:1px solid var(--border);background:white;font-size:0.75rem;color:var(--muted);text-align:center;">
        © 2026 SM Irigasi — Panel Administrator · Universitas Sebelas Maret
    </footer>
</div><!-- /main -->

<script>
var sections={overview:'sec-overview',users:'sec-users',laporan:'sec-laporan'};
var titles={overview:'Overview Dashboard',users:'Kelola Pengguna',laporan:'Laporan Kendala'};

function showSection(id){
    // Hide all
    Object.values(sections).forEach(function(s){document.getElementById(s).style.display='none';});
    document.getElementById(sections[id]).style.display='block';
    document.getElementById('topbar-title').textContent=titles[id];
    // Update sidebar active
    document.querySelectorAll('.sb-item').forEach(function(b){b.classList.remove('active');});
    event && event.currentTarget && event.currentTarget.classList && event.currentTarget.classList.add('active');
    // scroll to top
    document.querySelector('.content').scrollTop=0;
}

// Check URL hash for section
(function(){
    var h=window.location.hash.replace('#','');
    if(h && sections[h]) showSection(h);
})();
</script>
</body>
</html>