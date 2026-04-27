<?php
require_once 'koneksi.php';
require_once 'auth_helper.php';

// Cek role administrator
if ($role !== 'administrator') {
    header("Location: index.php");
    exit();
}

$adminNama = $namaLengkap;
$adminId   = (int)$user_id;

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
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
        body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);color:var(--txt);display:flex;flex-direction:column;min-height:100vh;}

        /* Mobile: sidebar jadi menu toggle */
        .sidebar{
            position:fixed;top:0;left:-280px;width:280px;height:100%;z-index:1000;
            background:var(--sidebar);transition:left 0.3s ease;overflow-y:auto;
            box-shadow:4px 0 20px rgba(0,0,0,0.12);
        }
        .sidebar.open{left:0;}
        .sidebar-overlay{
            position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);
            z-index:999;display:none;
        }
        .sidebar-overlay.open{display:block;}
        
        /* Desktop: sidebar tetap terbuka */
        @media (min-width: 768px) {
            body{flex-direction:row;}
            .sidebar{position:sticky;left:0;width:240px;height:100vh;top:0;}
            .sidebar-overlay{display:none !important;}
            .menu-toggle{display:none !important;}
        }
        
        @media (max-width: 767px) {
            .main{width:100%;}
            .topbar{padding-left:1rem !important;padding-right:1rem !important;}
            .content{padding:1rem !important;}
            .kpi-grid{grid-template-columns:repeat(2,1fr) !important;gap:0.75rem !important;}
            .bento-grid{grid-template-columns:repeat(2,1fr) !important;gap:0.75rem !important;}
            .kpi-card{padding:0.75rem !important;}
            .kpi-num{font-size:1.4rem !important;}
            .sensor-tile{padding:0.75rem !important;}
            .tile-loc{font-size:0.7rem !important;}
            .sc-head{flex-direction:column;align-items:flex-start !important;gap:8px;}
            .data-table,.data-table tbody,.data-table tr,.data-table td{display:block;}
            .data-table thead{display:none;}
            .data-table tr{border:1px solid var(--border);border-radius:12px;margin-bottom:12px;padding:10px;}
            .data-table td{display:flex;justify-content:space-between;align-items:center;padding:8px 0;border:none;}
            .data-table td:before{content:attr(data-label);font-weight:700;font-size:0.7rem;color:var(--muted);margin-right:16px;}
            .tbl-select,.tbl-btn{padding:4px 8px !important;font-size:0.7rem !important;}
            .sb-footer{margin-bottom:20px;}
        }
        
        @media (max-width: 480px) {
            .kpi-grid{grid-template-columns:1fr !important;}
            .bento-grid{grid-template-columns:1fr !important;}
            .topbar-title{font-size:0.85rem !important;}
            .notif-btn{width:32px;height:32px;}
            .logout-btn{padding:6px 10px !important;font-size:0.7rem !important;}
        }
        
        /* Sidebar styles */
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
        .sb-badge{margin-left:auto;background:rgba(239,68,68,0.9);color:white;font-size:0.65rem;font-weight:700;padding:2px 7px;border-radius:20px;}
        .sb-footer{margin-top:auto;padding:1rem;border-top:1px solid rgba(255,255,255,0.07);}
        .sb-user{display:flex;align-items:center;gap:9px;padding:8px;border-radius:10px;cursor:pointer;transition:background 0.18s;}
        .sb-user:hover{background:var(--sidebar-hover);}
        .sb-avatar{width:34px;height:34px;border-radius:9px;background:rgba(16,185,129,0.25);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.85rem;color:var(--mint-l);flex-shrink:0;}
        .sb-uname{font-size:0.82rem;font-weight:600;color:rgba(255,255,255,0.85);line-height:1.2;}
        .sb-urole{font-size:0.65rem;color:rgba(255,255,255,0.35);font-weight:500;text-transform:capitalize;}

        /* MAIN CONTENT */
        .main{flex:1;display:flex;flex-direction:column;overflow:hidden;}

        /* TOP BAR */
        .topbar{
            background:rgba(255,255,255,0.82);
            backdrop-filter:blur(16px);
            border-bottom:1px solid var(--border);
            padding:0 1rem;height:56px;
            display:flex;align-items:center;justify-content:space-between;
            position:sticky;top:0;z-index:50;
        }
        @media (min-width: 640px){.topbar{padding:0 1.75rem;height:62px;}}
        
        .menu-toggle{
            background:none;border:none;cursor:pointer;padding:8px;
            display:flex;align-items:center;justify-content:center;
        }
        @media (min-width: 768px){.menu-toggle{display:none;}}
        
        .topbar-title{font-size:0.9rem;font-weight:700;color:var(--txt);}
        @media (min-width: 640px){.topbar-title{font-size:1rem;}}
        
        .topbar-sub{font-size:0.7rem;color:var(--muted);margin-top:1px;}
        @media (min-width: 640px){.topbar-sub{font-size:0.78rem;}}
        
        .topbar-right{display:flex;align-items:center;gap:8px;}
        .notif-btn{position:relative;width:36px;height:36px;border-radius:10px;border:1px solid var(--border);background:white;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--txt2);transition:all 0.18s;}
        .notif-dot{position:absolute;top:6px;right:6px;width:7px;height:7px;background:#EF4444;border-radius:50%;border:1.5px solid white;}
        .logout-btn{display:flex;align-items:center;gap:5px;padding:6px 12px;border-radius:10px;background:rgba(239,68,68,0.07);border:1px solid rgba(239,68,68,0.15);color:#DC2626;font-size:0.75rem;font-weight:600;cursor:pointer;font-family:inherit;text-decoration:none;transition:all 0.18s;}
        @media (min-width: 640px){.logout-btn{padding:8px 14px;gap:6px;font-size:0.82rem;}}

        /* CONTENT AREA */
        .content{padding:1rem;flex:1;overflow-y:auto;}
        @media (min-width: 640px){.content{padding:1.75rem;}}

        /* FLASH MSG */
        .flash{padding:10px 14px;border-radius:12px;font-size:0.8rem;font-weight:500;margin-bottom:1rem;display:flex;align-items:center;gap:8px;}
        .flash-ok{background:#F0FDF4;border:1px solid #BBF7D0;color:#166534;}
        .flash-err{background:#FEF2F2;border:1px solid #FECACA;color:#991B1B;}

        /* KPI GRID */
        .kpi-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:0.75rem;margin-bottom:1rem;}
        @media (min-width: 640px){.kpi-grid{gap:1rem;margin-bottom:1.5rem;}}
        @media (min-width: 1024px){.kpi-grid{grid-template-columns:repeat(4,1fr);}}
        
        .kpi-card{background:var(--card);border-radius:14px;padding:0.9rem;border:1px solid var(--border);box-shadow:var(--shadow);transition:transform 0.2s ease;display:flex;flex-direction:column;gap:8px;}
        @media (min-width: 640px){.kpi-card{padding:1.25rem 1.3rem;gap:10px;border-radius:16px;}}
        .kpi-icon{width:34px;height:34px;border-radius:10px;display:flex;align-items:center;justify-content:center;}
        .kpi-num{font-size:1.4rem;font-weight:800;color:var(--txt);letter-spacing:-0.03em;line-height:1;}
        @media (min-width: 640px){.kpi-num{font-size:1.9rem;}}
        .kpi-label{font-size:0.7rem;font-weight:600;color:var(--txt2);margin-top:2px;}
        .kpi-sub{font-size:0.65rem;color:var(--muted);}

        /* BENTO SENSOR GRID */
        .bento-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:0.75rem;margin-bottom:1rem;}
        @media (min-width: 640px){.bento-grid{gap:1rem;margin-bottom:1.5rem;}}
        @media (min-width: 1024px){.bento-grid{grid-template-columns:repeat(4,1fr);}}
        
        .sensor-tile{background:var(--card);border-radius:14px;padding:0.8rem;border:1px solid var(--border);box-shadow:var(--shadow);position:relative;overflow:hidden;transition:transform 0.2s;}
        @media (min-width: 640px){.sensor-tile{padding:1.1rem;border-radius:16px;}}
        .sensor-tile:hover{transform:translateY(-2px);}
        .sensor-tile::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;border-radius:14px 14px 0 0;}
        @media (min-width: 640px){.sensor-tile::before{border-radius:16px 16px 0 0;}}
        .tile-normal::before{background:linear-gradient(90deg,#10B981,#34D399);}
        .tile-rendah::before{background:linear-gradient(90deg,#F97316,#FDBA74);}
        .tile-tinggi::before{background:linear-gradient(90deg,#3B82F6,#93C5FD);}
        .tile-kritis::before{background:linear-gradient(90deg,#EF4444,#FCA5A5);}
        .tile-id{font-size:0.65rem;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:var(--muted);margin-bottom:5px;}
        .tile-loc{font-size:0.75rem;font-weight:600;color:var(--txt);margin-bottom:8px;line-height:1.3;}
        @media (min-width: 640px){.tile-loc{font-size:0.82rem;margin-bottom:10px;}}
        .tile-stats{display:grid;grid-template-columns:1fr 1fr;gap:5px;}
        .tile-stat{background:rgba(6,78,59,0.04);border-radius:7px;padding:5px 6px;}
        .tile-stat-val{font-size:0.8rem;font-weight:700;color:var(--txt);}
        .tile-stat-lbl{font-size:0.6rem;color:var(--muted);font-weight:500;margin-top:1px;}
        .status-pill{display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:20px;font-size:0.65rem;font-weight:700;margin-top:6px;}
        .sp-normal{background:#F0FDF4;color:#15803D;border:1px solid #BBF7D0;}
        .sp-rendah{background:#FFF7ED;color:#C2410C;border:1px solid #FED7AA;}
        .sp-tinggi{background:#EFF6FF;color:#1D4ED8;border:1px solid #BFDBFE;}
        .sp-kritis{background:#FEF2F2;color:#B91C1C;border:1px solid #FCA5A5;}

        /* SECTION CARDS */
        .section-card{background:var(--card);border-radius:16px;border:1px solid var(--border);box-shadow:var(--shadow);margin-bottom:1rem;overflow:hidden;}
        .sc-head{padding:0.9rem 1rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;}
        @media (min-width: 640px){.sc-head{padding:1.1rem 1.4rem;}}
        .sc-title{font-size:0.85rem;font-weight:700;color:var(--txt);}
        .sc-sub{font-size:0.7rem;color:var(--muted);margin-top:2px;}
        .sc-badge{padding:3px 10px;border-radius:20px;font-size:0.68rem;font-weight:700;}
        .badge-red{background:#FEF2F2;color:#B91C1C;border:1px solid #FECACA;}
        .badge-green{background:#F0FDF4;color:#15803D;border:1px solid #BBF7D0;}

        /* TABLE MOBILE FRIENDLY */
        .data-table{width:100%;border-collapse:collapse;font-size:0.8rem;}
        .data-table th{padding:10px 12px;text-align:left;font-size:0.68rem;font-weight:700;color:var(--muted);text-transform:uppercase;background:#FAFAFA;border-bottom:1px solid var(--border);}
        .data-table td{padding:10px 12px;border-bottom:1px solid rgba(6,78,59,0.05);vertical-align:middle;}
        
        @media (max-width: 767px){
            .data-table,.data-table thead,.data-table tbody,.data-table tr,.data-table th,.data-table td{display:block;}
            .data-table thead{display:none;}
            .data-table tr{border:1px solid var(--border);border-radius:12px;margin-bottom:12px;padding:8px 0;background:white;}
            .data-table td{display:flex;justify-content:space-between;align-items:center;padding:8px 12px;border:none;}
            .data-table td:before{content:attr(data-label);font-weight:700;font-size:0.68rem;color:var(--muted);margin-right:16px;flex-shrink:0;width:100px;}
            .data-table td div{text-align:right;flex:1;}
        }
        
        .role-badge{display:inline-block;padding:2px 8px;border-radius:20px;font-size:0.68rem;font-weight:700;}
        .badge-petani{background:#F0FDF4;color:#15803D;border:1px solid #BBF7D0;}
        .badge-petugas{background:#EFF6FF;color:#1D4ED8;border:1px solid #BFDBFE;}
        .badge-koordinator{background:#FFF7ED;color:#C2410C;border:1px solid #FED7AA;}
        .badge-admin{background:#FDF4FF;color:#7E22CE;border:1px solid #E9D5FF;}
        
        .ls-baru{background:#FEF2F2;color:#B91C1C;border:1px solid #FECACA;}
        .ls-ditangani{background:#FFFBEB;color:#92400E;border:1px solid #FDE68A;}
        .ls-selesai{background:#F0FDF4;color:#15803D;border:1px solid #BBF7D0;}
        
        .tbl-select{font-family:inherit;font-size:0.7rem;border:1px solid var(--border);border-radius:8px;padding:4px 8px;background:white;color:var(--txt);outline:none;}
        .tbl-btn{padding:4px 10px;border-radius:8px;border:none;font-family:inherit;font-size:0.7rem;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:4px;}
        .tbl-btn-green{background:#F0FDF4;color:#15803D;border:1px solid #BBF7D0;}
        .tbl-btn-red{background:#FEF2F2;color:#B91C1C;border:1px solid #FECACA;}
        .tbl-btn-blue{background:#EFF6FF;color:#1D4ED8;border:1px solid #BFDBFE;}
        
        .avatar{width:28px;height:28px;border-radius:8px;background:linear-gradient(135deg,var(--emerald),#10B981);display:flex;align-items:center;justify-content:center;font-size:0.7rem;font-weight:700;color:white;}
        .you-tag{background:#F1F5F9;color:#64748B;font-size:0.6rem;font-weight:700;padding:2px 6px;border-radius:20px;margin-left:6px;}
        
        @keyframes livePulse{0%,100%{opacity:1}50%{opacity:0.4}}
        .live-dot{display:inline-block;width:7px;height:7px;background:#10B981;border-radius:50%;animation:livePulse 2s infinite;margin-right:6px;}
        
        footer{background:white;border-top:1px solid var(--border);padding:0.75rem;text-align:center;font-size:0.65rem;color:var(--muted);}
        @media (min-width: 640px){footer{padding:1rem 1.75rem;font-size:0.75rem;}}
    </style>
</head>
<body>

<!-- SIDEBAR OVERLAY MOBILE -->
<div class="sidebar-overlay" id="sidebar-overlay" onclick="closeSidebar()"></div>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
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
        <button class="sb-item active" onclick="showSection('overview');closeSidebar()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            Overview
        </button>

        <div class="sb-section">Manajemen</div>
        <button class="sb-item" onclick="showSection('users');closeSidebar()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Kelola Pengguna
        </button>
        <button class="sb-item" onclick="showSection('laporan');closeSidebar()" style="position:relative">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            Laporan Kendala
            <?php if($newL>0): ?><span class="sb-badge"><?= $newL ?></span><?php endif; ?>
        </button>

        <div class="sb-section">Sensor</div>
        <a href="peta.php" class="sb-item" onclick="closeSidebar()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/><line x1="9" y1="3" x2="9" y2="18"/><line x1="15" y1="6" x2="15" y2="21"/></svg>
            Peta Sensor
        </a>
        <a href="riwayat.php" class="sb-item" onclick="closeSidebar()">
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
        <a href="logout.php" style="display:flex;align-items:center;gap:8px;padding:8px 8px 0;color:rgba(255,255,255,0.35);font-size:0.78rem;text-decoration:none;transition:color 0.18s;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Keluar
        </a>
    </div>
</aside>

<!-- MAIN -->
<div class="main">

    <!-- TOP BAR -->
    <header class="topbar">
        <div style="display:flex;align-items:center;gap:8px;">
            <button class="menu-toggle" onclick="toggleSidebar()" id="menuToggle">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>
            <div>
                <div class="topbar-title" id="topbar-title">Overview Dashboard</div>
                <div class="topbar-sub"><span class="live-dot"></span>Data sensor aktif · <?= date('d M Y, H:i') ?></div>
            </div>
        </div>
        <div class="topbar-right">
            <?php if($newL>0): ?>
            <button class="notif-btn" onclick="showSection('laporan')" title="<?= $newL ?> laporan baru">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                <span class="notif-dot"></span>
            </button>
            <?php endif; ?>
            <a href="logout.php" class="logout-btn">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Keluar
            </a>
        </div>
    </header>

    <!-- CONTENT -->
    <div class="content">

        <?php
        $msgs=['role_ok'=>['ok','Role pengguna berhasil diperbarui.'],'del_ok'=>['ok','Pengguna berhasil dihapus.'],'status_ok'=>['ok','Status laporan berhasil diperbarui.'],'self_err'=>['err','Tidak dapat menghapus akun sendiri.']];
        if(isset($_GET['msg']) && isset($msgs[$_GET['msg']])){[$type,$text]=$msgs[$_GET['msg']];
        echo '<div class="flash '.($type==='ok'?'flash-ok':'flash-err').'"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">'.($type==='ok'?'<polyline points="20 6 9 17 4 12"/>':'<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>').'</svg>'.htmlspecialchars($text).'</div>';}
        ?>

        <!-- SECTION: OVERVIEW -->
        <div id="sec-overview" class="sec-active" style="display:block">

            <div class="kpi-grid">
                <div class="kpi-card">
                    <div class="kpi-icon" style="background:#F0FDF4;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#15803D" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
                    <div><div class="kpi-num"><?= $totalU ?></div><div class="kpi-label">Total Pengguna</div><div class="kpi-sub"><?= $admCnt ?> Administrator</div></div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon" style="background:#FEF2F2;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#B91C1C" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div>
                    <div><div class="kpi-num"><?= $totalL ?></div><div class="kpi-label">Total Laporan</div><div class="kpi-sub"><?= $newL ?> laporan baru</div></div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon" style="background:#EFF6FF;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1D4ED8" stroke-width="2"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/></svg></div>
                    <div><div class="kpi-num"><?= $avgDebit ?></div><div class="kpi-label">Rata-rata Debit</div><div class="kpi-sub">L/detik · 8 sensor</div></div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-icon" style="background:<?= $kritisCnt>0?'#FEF2F2':'#F0FDF4' ?>;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="<?= $kritisCnt>0?'#B91C1C':'#15803D' ?>" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg></div>
                    <div><div class="kpi-num" style="color:<?= $kritisCnt>0?'#B91C1C':'#15803D' ?>"><?= $normalCnt ?>/8</div><div class="kpi-label">Sensor Normal</div><div class="kpi-sub"><?= $kritisCnt ?> kritis</div></div>
                </div>
            </div>

            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.75rem;flex-wrap:wrap;gap:8px;">
                <div><h2 style="font-size:0.85rem;font-weight:700;color:var(--txt);">Status Sensor Real-Time</h2><p style="font-size:0.7rem;color:var(--muted);margin-top:2px;">8 titik pantau aktif · update 4 detik</p></div>
                <a href="peta.php" style="display:flex;align-items:center;gap:5px;font-size:0.7rem;font-weight:600;color:var(--mint);text-decoration:none;">Lihat Peta<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg></a>
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
                        <div class="tile-stat"><div class="tile-stat-val"><?= number_format($s['debit'],1) ?></div><div class="tile-stat-lbl">Debit</div></div>
                        <div class="tile-stat"><div class="tile-stat-val"><?= $s['tma'] ?></div><div class="tile-stat-lbl">TMA</div></div>
                        <div class="tile-stat"><div class="tile-stat-val"><?= number_format($s['suhu'],1) ?>°</div><div class="tile-stat-lbl">Suhu</div></div>
                        <div class="tile-stat"><div class="tile-stat-val"><?= $s['lembap'] ?>%</div><div class="tile-stat-lbl">Lembap</div></div>
                    </div>
                    <span class="status-pill <?= $spClass ?>"><svg width="5" height="5" viewBox="0 0 6 6"><circle cx="3" cy="3" r="3" fill="<?= $dotColor ?>"/></svg><?= $statusLabel ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- SECTION: USERS -->
        <div id="sec-users" style="display:none">
            <div class="section-card">
                <div class="sc-head">
                    <div><div class="sc-title">Kelola Pengguna</div><div class="sc-sub">Ubah role atau hapus pengguna terdaftar</div></div>
                    <span class="sc-badge badge-green"><?= $totalU ?> pengguna</span>
                </div>
                <div style="overflow-x:auto;">
                    <table class="data-table">
                        <thead><tr><th>Pengguna</th><th>Username</th><th>Email</th><th>Role</th><th>Terdaftar</th><th>Aksi</th></tr></thead>
                        <tbody>
                        <?php mysqli_data_seek($users,0); while($u=mysqli_fetch_assoc($users)): ?>
                        <tr>
                            <td data-label="Pengguna"><div style="display:flex;align-items:center;gap:10px;"><div class="avatar"><?= strtoupper(substr($u['nama_depan'],0,1)) ?></div><div><div style="font-weight:600;font-size:0.8rem;"><?= htmlspecialchars(trim($u['nama_depan'].' '.$u['nama_belakang'])) ?><?php if((int)$u['id_users']===$adminId): ?><span class="you-tag">ANDA</span><?php endif; ?></div></div></div></td>
                            <td data-label="Username"><?= htmlspecialchars($u['username']) ?></td>
                            <td data-label="Email"><?= htmlspecialchars($u['email']) ?></td>
                            <td data-label="Role"><span class="role-badge <?= bRole($u['role']) ?>"><?= lRole($u['role']) ?></span></td>
                            <td data-label="Terdaftar"><?= date('d M Y',strtotime($u['created_at'])) ?></td>
                            <td data-label="Aksi">
                                <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                                    <form method="POST" style="display:flex;align-items:center;gap:5px;">
                                        <input type="hidden" name="aksi" value="ubah_role"><input type="hidden" name="id_user" value="<?= $u['id_users'] ?>">
                                        <select name="role" class="tbl-select"><?php foreach($roleList as $rl): ?><option value="<?= $rl ?>" <?= $u['role']===$rl?'selected':'' ?>><?= lRole($rl) ?></option><?php endforeach; ?></select>
                                        <button type="submit" class="tbl-btn tbl-btn-green"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>Simpan</button>
                                    </form>
                                    <?php if((int)$u['id_users']!==$adminId): ?>
                                    <form method="POST" onsubmit="return confirm('Hapus pengguna <?= htmlspecialchars($u['username'],ENT_QUOTES) ?>?')">
                                        <input type="hidden" name="aksi" value="hapus_user"><input type="hidden" name="id_user" value="<?= $u['id_users'] ?>">
                                        <button type="submit" class="tbl-btn tbl-btn-red"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>Hapus</button>
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
        </div>

        <!-- SECTION: LAPORAN -->
        <div id="sec-laporan" style="display:none">
            <div class="section-card">
                <div class="sc-head">
                    <div><div class="sc-title">Laporan Kendala dari Petani</div><div class="sc-sub">Pantau dan tangani masalah yang dilaporkan</div></div>
                    <?php if($newL>0): ?><span class="sc-badge badge-red"><?= $newL ?> laporan baru</span><?php else: ?><span class="sc-badge badge-green">Semua tertangani</span><?php endif; ?>
                </div>

                <?php if(mysqli_num_rows($laporan)===0): ?>
                <div style="padding:2rem;text-align:center;color:var(--muted);"><svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin:0 auto 10px;opacity:0.3;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg><p style="font-weight:600;margin-bottom:4px;">Belum ada laporan</p><p style="font-size:0.7rem;">Laporan dari petani akan muncul di sini</p></div>
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
                            <td data-label="Pelapor"><div style="font-weight:600;font-size:0.8rem;"><?= htmlspecialchars($lp['nama_pelapor']) ?></div><?php if($lp['username']): ?><div style="font-size:0.65rem;color:var(--muted);">@<?= htmlspecialchars($lp['username']) ?></div><?php endif; ?></td>
                            <td data-label="Lokasi"><?= htmlspecialchars($lp['lokasi']) ?></td>
                            <td data-label="Kendala"><?= htmlspecialchars($lp['jenis_kendala']) ?></td>
                            <td data-label="Tanggal"><?= date('d M Y H:i',strtotime($lp['created_at'])) ?></td>
                            <td data-label="Status"><span class="status-pill <?= $lsClass ?>" style="margin:0;"><svg width="5" height="5" viewBox="0 0 6 6"><circle cx="3" cy="3" r="3" fill="<?= $lsDot ?>"/></svg><?= $lsLabel ?></span></td>
                            <td data-label="Aksi">
                                <form method="POST" style="display:flex;align-items:center;gap:5px;">
                                    <input type="hidden" name="aksi" value="ubah_status_laporan"><input type="hidden" name="id_laporan" value="<?= $lp['id_laporan'] ?>">
                                    <select name="status" class="tbl-select"><option value="baru" <?= $lp['status']==='baru'?'selected':'' ?>>Baru</option><option value="ditangani" <?= $lp['status']==='ditangani'?'selected':'' ?>>Ditangani</option><option value="selesai" <?= $lp['status']==='selesai'?'selected':'' ?>>Selesai</option></select>
                                    <button type="submit" class="tbl-btn tbl-btn-blue"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 4 23 11 16 11"/><path d="M20.49 15a9 9 0 1 1-.18-4.96"/></svg>Update</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <footer>
        &copy; 2026 SM Irigasi — Panel Administrator · Universitas Sebelas Maret
    </footer>
</div>

<script>
var sections={overview:'sec-overview',users:'sec-users',laporan:'sec-laporan'};
var titles={overview:'Overview Dashboard',users:'Kelola Pengguna',laporan:'Laporan Kendala'};

function showSection(id){
    Object.values(sections).forEach(function(s){document.getElementById(s).style.display='none';});
    document.getElementById(sections[id]).style.display='block';
    document.getElementById('topbar-title').textContent=titles[id];
    document.querySelectorAll('.sb-item').forEach(function(b){b.classList.remove('active');});
    if(event && event.currentTarget) event.currentTarget.classList.add('active');
}

function toggleSidebar(){
    var sidebar=document.getElementById('sidebar');
    var overlay=document.getElementById('sidebar-overlay');
    sidebar.classList.toggle('open');
    overlay.classList.toggle('open');
}

function closeSidebar(){
    var sidebar=document.getElementById('sidebar');
    var overlay=document.getElementById('sidebar-overlay');
    sidebar.classList.remove('open');
    overlay.classList.remove('open');
}

var hash=window.location.hash.replace('#','');
if(hash && sections[hash]) showSection(hash);
</script>
</body>
</html>
