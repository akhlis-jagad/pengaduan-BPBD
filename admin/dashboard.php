<?php
session_start();

// Cek apakah session login ada (misalnya cek session 'user_id' atau 'username')
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Jika tidak ada session, alihkan ke halaman login
    header("Location: login.php");
    exit();
}

include '../koneksi.php';

// Mengambil statistik pengaduan
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Disetujui' THEN 1 ELSE 0 END) as disetujui,
    SUM(CASE WHEN status = 'Ditolak' THEN 1 ELSE 0 END) as ditolak,
    SUM(CASE WHEN status IS NULL OR status = 'Pending' THEN 1 ELSE 0 END) as pending
FROM pengaduan";
$stats = mysqli_fetch_assoc(mysqli_query($conn, $stats_query));

// Filter status dari query parameter
$status_filter = $_GET['status'] ?? 'all';
$filter = ($status_filter !== 'all') ? "WHERE status = '" . ($status_filter === 'pending' ? 'Pending' : $status_filter) . "'" : '';
$result = mysqli_query($conn, "SELECT * FROM pengaduan $filter ORDER BY id DESC");

$alert = "";

// Handle approval/rejection
if (isset($_GET['action'], $_GET['id'])) {
    $status = ($_GET['action'] == 'approve') ? 'Diterima' : 'Ditolak';
    if (mysqli_query($conn, "UPDATE pengaduan SET status = '$status' WHERE id = {$_GET['id']}")) {
        echo "<script>
            Swal.fire('Berhasil!', 'Pengaduan berhasil $status', 'success')
            .then(() => window.location.href='admin.php');
        </script>";
    } else {
        echo "<script>Swal.fire('Gagal!', 'Terjadi kesalahan!', 'error');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - BPBD Kudus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- <link rel="stylesheet" href="./css/dashboard.css"> -->
    <style>
        /* Reset beberapa style */
        body {
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        
        /* Warna & Styling Sidebar */
        .sidebar {
            background-color: #FF8C00; /* Warna oranye */
            height: 100%;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            overflow-y: auto;
            transition: all 0.3s ease;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            padding-top: 20px;
        }
        
        /* State ketika sidebar dikolapskan/ditutup */
        .sidebar.collapsed {
            left: -200px;
            width: 50px;
        }
        
        /* Styling untuk tombol panah sidebar */
        .sidebar-toggle-arrow {
            position: absolute;
            top: 10px;
            right: 10px;
            color: white;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 1002;
        }
        
        /* Rotasi dan posisi tombol saat sidebar dikolapskan */
        .sidebar.collapsed .sidebar-toggle-arrow {
            right: 10px;
            transform: rotate(180deg);
        }
        
        /* Tombol panah untuk memunculkan sidebar kembali saat dikolapskan */
        .sidebar-expand {
            position: fixed;
            top: 10px;
            left: 10px;
            color: white;
            background: #FF8C00;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: none; /* Sembunyikan awalnya */
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 1003;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        /* Tampilkan tombol ekspand saat sidebar dikolapskan */
        .sidebar.collapsed ~ .sidebar-expand {
            display: flex;
        }
        
        /* Menyembunyikan teks menu ketika sidebar dikolapskan */
        .sidebar.collapsed .menu-text {
            display: none;
        }
        
        /* Styling untuk logo dan header ketika dikolapskan */
        .sidebar.collapsed .logo-container {
            padding: 5px;
        }
        
        .sidebar.collapsed .logo-container img {
            width: 40px;
            height: 40px;
        }
        
        .sidebar.collapsed .logo-container h5 {
            display: none;
        }
        
        /* Link di dalam sidebar */
        .sidebar a {
            display: block;
            color: white;
            padding: 15px 20px;
            text-decoration: none;
            transition: all 0.3s;
            border-left: 4px solid transparent;
        }
        
        .sidebar a:hover {
            background-color: rgba(255,255,255,0.2);
            border-left: 4px solid white;
        }
        
        .sidebar a.active {
            background-color: rgba(255,255,255,0.3);
            border-left: 4px solid white;
        }
        
        .sidebar a i {
            margin-right: 10px;
        }
        
        /* Penyesuaian untuk menu ketika sidebar dikolapskan */
        .sidebar.collapsed a {
            padding: 15px 15px;
            text-align: center;
        }
        
        .sidebar.collapsed a i {
            margin-right: 0;
            font-size: 1.2rem;
        }
        
        /* Logo dan header */
        .logo-container {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo-container img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 3px solid white;
            object-fit: cover;
        }
        
        .logo-container h5 {
            color: white;
            margin-top: 10px;
            font-weight: 600;
        }
        
        /* Main content */
        .main-content {
            transition: margin-left 0.3s ease;
            margin-left: 250px;
            width: calc(100% - 250px);
            padding: 20px;
        }
        
        .main-content.expanded {
            margin-left: 50px;
            width: calc(100% - 50px);
        }
        
        /* Stats cards */
        .stats-card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            cursor: pointer;
            margin-bottom: 15px;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }
        
        /* Responsif untuk tabel pada mobile */
        @media (max-width: 768px) {
            .table-responsive {
                font-size: 0.85rem;
            }
        }
        
        /* Media query untuk responsif */
        @media (max-width: 991px) {
            .sidebar {
                left: 0; /* Sidebar tetap terlihat di layar kecil */
                width: 50px; /* Otomatis kolaps ke mode mini pada layar kecil */
            }
            
            .sidebar .menu-text {
                display: none; /* Sembunyikan teks menu di layar kecil */
            }
            
            .sidebar .logo-container h5 {
                display: none; /* Sembunyikan judul di layar kecil */
            }
            
            .sidebar .logo-container img {
                width: 40px;
                height: 40px;
            }
            
            .sidebar a {
                padding: 15px 15px;
                text-align: center;
            }
            
            .sidebar a i {
                margin-right: 0;
                font-size: 1.2rem;
            }
            
            .main-content {
                margin-left: 50px;
                width: calc(100% - 50px);
            }
            
            .sidebar-toggle-arrow {
                display: none; /* Sembunyikan tombol panah di layar kecil */
            }
            
            .sidebar-expand {
                display: none !important; /* Selalu sembunyikan tombol ekspand di mobile */
            }
        }
    </style>
</head>
<body>
    <?php echo $alert; ?>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <!-- Tombol panah untuk desktop -->
        <button class="sidebar-toggle-arrow" id="sidebarToggleArrow">
            <i class="bi bi-chevron-left"></i>
        </button>
        
        <div class="logo-container">
            <img src="../assets/image/logo.jpg" alt="BPBD Logo" class="img-fluid">
            <h5>Admin BPBD</h5>
        </div>
        <a href="./dashboard.php" class="active"><i class="bi bi-house-door"></i> <span class="menu-text">Dashboard</span></a>
        <a href="./pengaduan.php"><i class="bi bi-file-text"></i> <span class="menu-text">Pengaduan</span></a>
        <a href="logout.php"><i class="bi bi-box-arrow-right"></i> <span class="menu-text">Logout</span></a>
    </div>
    
    <!-- Tombol untuk memunculkan sidebar kembali setelah dikolapskan -->
    <button class="sidebar-expand" id="sidebarExpand">
        <i class="bi bi-chevron-right"></i>
    </button>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <h2 class="mb-4">Dashboard Admin</h2>
        
        <!-- Statistik Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="card stats-card bg-primary text-white" onclick="window.location.href='?status=all'">
                    <div class="card-body">
                        <h5 class="card-title">Total Pengaduan</h5>
                        <h2 class="card-text"><?= $stats['total'] ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card stats-card bg-success text-white" onclick="window.location.href='?status=Disetujui'">
                    <div class="card-body">
                        <h5 class="card-title">Disetujui</h5>
                        <h2 class="card-text"><?= $stats['disetujui'] ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card stats-card bg-warning" onclick="window.location.href='?status=pending'">
                    <div class="card-body">
                        <h5 class="card-title">Pending</h5>
                        <h2 class="card-text"><?= $stats['pending'] ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card stats-card bg-danger text-white" onclick="window.location.href='?status=Ditolak'">
                    <div class="card-body">
                        <h5 class="card-title">Ditolak</h5>
                        <h2 class="card-text"><?= $stats['ditolak'] ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    Daftar Pengaduan 
                    <?php
                    if ($status_filter !== 'all') {
                        echo "- " . ucfirst($status_filter);
                    }
                    ?>
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Nama</th>
                                <th>No Hp</th>
                                <th>Kronologi</th>
                                <th>Alamat</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($result) > 0): 
                                $no = 1;
                                while ($row = mysqli_fetch_assoc($result)): 
                                    $status = $row['status'] ?? 'Pending';
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= date('d/m/Y', strtotime($row['tanggal_pengaduan'] ?? 'now')) ?></td>
                                <td><?= htmlspecialchars($row['nama']) ?></td>
                                <td>
                                    <a href="https://wa.me/<?= urlencode($row['nomor_hp']) ?>?text=Kami%20dari%20BPBD%20Kabupaten%20Kudus%20siap%20membantu%20dalam%20penanganan%20bencana.%20Segera%20laporkan%20kejadian%20bencana%20untuk%20tindakan%20cepat%20dan%20tepat." target="_blank">
                                    <?= htmlspecialchars($row['nomor_hp']) ?></a>
                                </td>
                                <td><?= htmlspecialchars(substr($row['kronologi'], 0, 50)) . '...' ?></td>
                                <td><?= htmlspecialchars($row['alamat']) ?></td>
                                
                                <td>
                                    <?php 
                                        // Pastikan status selalu memiliki nilai default 'Pending' jika NULL
                                        $status = $row['status'] ?? 'pending';

                                        // Tentukan kelas background berdasarkan status
                                        $statusClass = match ($status) {
                                            'disetujui' => 'bg-success',
                                            'ditolak' => 'bg-danger',
                                            default => 'bg-warning'
                                        };
                                    ?>
                                    <span class="badge <?= $statusClass ?>">
                                        <?= htmlspecialchars($status) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                                <tr><td colspan="6" class="text-center">Tidak ada pengaduan</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const sidebarToggleArrow = document.getElementById('sidebarToggleArrow');
        const sidebarExpand = document.getElementById('sidebarExpand');
        
        // Toggle sidebar collapse with arrow button (desktop)
        if (sidebarToggleArrow) {
            sidebarToggleArrow.addEventListener('click', function() {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('expanded');
                localStorage.setItem('sidebarCollapsed', 'true');
            });
        }
        
        // Expand sidebar with expand button
        if (sidebarExpand) {
            sidebarExpand.addEventListener('click', function() {
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('expanded');
                localStorage.setItem('sidebarCollapsed', 'false');
            });
        }
        
        // Check localStorage for sidebar state on page load
        const sidebarState = localStorage.getItem('sidebarCollapsed');
        if (sidebarState === 'true') {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('expanded');
        }
    });
    </script>
</body>
</html>