<?php
session_start();

// Cek apakah session login ada (misalnya cek session 'user_id' atau 'username')
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Jika tidak ada session, alihkan ke halaman login
    header("Location: login.php");
    exit();
}

include '../koneksi.php';

// Proses POST request dari AJAX untuk approve/reject/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $action = $_POST['action'] ?? null;
    
    if (!$id || !$action) {
        echo json_encode(['status' => 'error', 'message' => 'ID atau aksi tidak valid']);
        exit;
    }
    
    if ($action === 'approve') {
        $status = 'disetujui';
    } elseif ($action === 'reject') {
        $status = 'ditolak';
    } elseif ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM pengaduan WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Pengaduan berhasil dihapus']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus pengaduan']);
        }
        exit;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Aksi tidak dikenali']);
        exit;
    }
    
    // Proses update status pengaduan
    $stmt = $conn->prepare("UPDATE pengaduan SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => "Pengaduan berhasil $status"]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui pengaduan']);
    }
    exit;
}

// Fungsi untuk export data ke Excel
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    // Pastikan bulan dan tahun dipilih
    if (isset($_GET['filter_month']) && !empty($_GET['filter_month']) && 
        isset($_GET['filter_year']) && !empty($_GET['filter_year'])) {
        
        $filter_month = $_GET['filter_month'];
        $filter_year = $_GET['filter_year'];
        
        // Buat query untuk export
        $export_query = "SELECT * FROM pengaduan WHERE MONTH(tanggal_pengaduan) = '$filter_month' AND YEAR(tanggal_pengaduan) = '$filter_year' ORDER BY id DESC";
        $export_result = mysqli_query($conn, $export_query);
        
        // Set header untuk download Excel
        $filename = "Pengaduan_" . $filter_month . "_" . $filter_year . ".xls";
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Pragma: no-cache");
        header("Expires: 0");
        
        // Output header table untuk Excel
        echo '<table border="1">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>No</th>';
        echo '<th>Tanggal</th>';
        echo '<th>Nama</th>';
        echo '<th>Nomor HP</th>';
        echo '<th>Alamat</th>';
        echo '<th>Kronologi</th>';
        echo '<th>Status</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        if ($export_result && mysqli_num_rows($export_result) > 0) {
            $no = 1;
            while($row = mysqli_fetch_assoc($export_result)) {
                $tanggal = date('d/m/Y', strtotime($row['tanggal_pengaduan']));
                $status = $row['status'] ?? 'Pending';
                
                echo '<tr>';
                echo '<td>' . $no . '</td>';
                echo '<td>' . $tanggal . '</td>';
                echo '<td>' . htmlspecialchars($row['nama']) . '</td>';
                echo '<td>' . htmlspecialchars($row['nomor_hp']) . '</td>';
                echo '<td>' . htmlspecialchars($row['alamat']) . '</td>';
                echo '<td>' . htmlspecialchars($row['kronologi']) . '</td>';
                echo '<td>' . $status . '</td>';
                echo '</tr>';
                
                $no++;
            }
        }

        echo '</tbody>';
        echo '</table>';
        exit;
    } else {
        // Redirect kembali ke halaman dengan pesan error jika tidak ada filter
        header("Location: pengaduan.php?error=Pilih bulan dan tahun terlebih dahulu untuk export");
        exit;
    }
}

// Inisialisasi filter untuk query
$where_clause = "";
$filter_date = "";
$filter_month = "";
$filter_year = "";

// Filter berdasarkan tanggal (hari)
if (isset($_GET['filter_date']) && !empty($_GET['filter_date'])) {
    $filter_date = $_GET['filter_date'];
    $where_clause = " WHERE DATE(tanggal_pengaduan) = '$filter_date'";
}

// Filter berdasarkan bulan dan tahun
if (isset($_GET['filter_month']) && !empty($_GET['filter_month']) && 
    isset($_GET['filter_year']) && !empty($_GET['filter_year'])) {
    $filter_month = $_GET['filter_month'];
    $filter_year = $_GET['filter_year'];
    $where_clause = " WHERE MONTH(tanggal_pengaduan) = '$filter_month' AND YEAR(tanggal_pengaduan) = '$filter_year'";
}

// Ambil data pengaduan dari database dengan filter
$query = "SELECT * FROM pengaduan" . $where_clause . " ORDER BY id DESC";
$result = mysqli_query($conn, $query);

$alert = "";

// Handle approval/rejection dari URL parameter (non-AJAX)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $action = $_GET['action'];
    
    if ($action == 'delete') {
        // Menggunakan prepared statement untuk menghindari SQL injection
        $stmt = $conn->prepare("DELETE FROM pengaduan WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo "<script>
                Swal.fire({
                    title: 'Berhasil!',
                    text: 'Pengaduan berhasil dihapus',
                    icon: 'success'
                }).then(() => {
                    location.reload();
                });
            </script>";
            exit; // Hentikan eksekusi agar halaman tidak menampilkan konten tambahan
        } else {
            echo "<script>
                Swal.fire({
                    title: 'Gagal!',
                    text: 'Terjadi kesalahan!',
                    icon: 'error'
                });
            </script>";
        }
    } else {
        $status = ($action == 'approve') ? 'disetujui' : 'ditolak';
        
        // Menggunakan prepared statement untuk menghindari SQL injection
        $stmt = $conn->prepare("UPDATE pengaduan SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);

        if ($stmt->execute()) {
            $alert = "<script>
                Swal.fire({
                    title: 'Berhasil!',
                    text: 'Pengaduan berhasil $status',
                    icon: 'success',
                    
                }).then(() => {
                    window.location.href='pengaduan.php';
                });
            </script>";
        } else {
            $alert = "<script>
                Swal.fire({
                    title: 'Gagal!',
                    text: 'Terjadi kesalahan!',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            </script>";
        }
    }
}

// Menampilkan alert jika ada
echo $alert;
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pengaduan - BPBD Kudus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        
        /* Filter styles */
        .filter-container {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .filter-title {
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .action-buttons button {
            margin-bottom: 5px;
        }
        
        /* Responsif untuk tabel pada mobile */
        @media (max-width: 768px) {
            .table-responsive {
                font-size: 0.85rem;
            }
            
            .action-buttons {
                display: flex;
                flex-direction: column;
            }
            
            .action-buttons button {
                margin-bottom: 5px;
                font-size: 0.75rem;
                padding: 3px 5px;
            }
            
            /* Adjust modal size */
            .modal-dialog.modal-lg {
                max-width: 95%;
                margin: 10px auto;
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
    <a href="./dashboard.php"><i class="bi bi-house-door"></i> <span class="menu-text">Dashboard</span></a>
    <a href="./pengaduan.php" class="active"><i class="bi bi-file-text"></i> <span class="menu-text">Pengaduan</span></a>
    <a href="logout.php"><i class="bi bi-box-arrow-right"></i> <span class="menu-text">Logout</span></a>
</div>

<!-- Tombol untuk memunculkan sidebar kembali setelah dikolapskan -->
<button class="sidebar-expand" id="sidebarExpand">
    <i class="bi bi-chevron-right"></i>
</button>

<!-- Main Content -->
<div class="main-content" id="mainContent">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Daftar Pengaduan</h2>
    </div>

    <!-- Filter Container -->
    <div class="filter-container">
        <div class="filter-title">Filter Data</div>
        <form method="GET" action="">
            <div class="row g-3">
                <!-- Filter per Hari -->
                <div class="col-md-4 col-sm-12">
                    <label for="filter_date" class="form-label">Filter per Hari</label>
                    <input type="date" class="form-control" id="filter_date" name="filter_date" value="<?= $filter_date ?>">
                </div>
                <div class="col-md-2 col-sm-12">
                    <button type="submit" class="btn btn-primary w-100 mt-sm-2">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                </div>
            </div>
        </form>
        
        <hr class="my-3">
        
        <form method="GET" action="">
            <div class="row g-3">
                <!-- Filter per Bulan -->
                <div class="col-md-2 col-sm-6">
                    <label for="filter_month" class="form-label">Bulan</label>
                    <select class="form-select" id="filter_month" name="filter_month">
                        <option value="">Pilih Bulan</option>
                        <?php
                        for ($i = 1; $i <= 12; $i++) {
                            $month = str_pad($i, 2, '0', STR_PAD_LEFT);
                            $selected = ($filter_month == $month) ? 'selected' : '';
                            echo "<option value=\"$month\" $selected>" . date('F', mktime(0, 0, 0, $i, 1)) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-2 col-sm-6">
                    <label for="filter_year" class="form-label">Tahun</label>
                    <select class="form-select" id="filter_year" name="filter_year">
                        <option value="">Pilih Tahun</option>
                        <?php
                        $current_year = date('Y');
                        for ($y = $current_year; $y >= $current_year - 5; $y--) {
                            $selected = ($filter_year == $y) ? 'selected' : '';
                            echo "<option value=\"$y\" $selected>$y</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-2 col-sm-4 mt-sm-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                </div>
                <!-- Export Excel Button (only for monthly filter) -->
                <div class="col-md-2 col-sm-4 mt-sm-3">
                    <button type="submit" name="export" value="excel" class="btn btn-success w-100" <?= (empty($filter_month) || empty($filter_year)) ? 'disabled' : '' ?>>
                        <i class="bi bi-file-excel"></i> Export
                    </button>
                </div>
                <!-- Reset Filter -->
                <div class="col-md-2 col-sm-4 mt-sm-3">
                    <a href="pengaduan.php" class="btn btn-secondary w-100">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger">
        <?= htmlspecialchars($_GET['error']) ?>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Nama</th>
                            <th>Nomor HP</th>
                            <th>Kronologi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result && mysqli_num_rows($result) > 0) {
                            $no = 1;
                            while($row = mysqli_fetch_assoc($result)) {
                                $status = $row['status'] ?? 'pending';

                                // Tentukan kelas background berdasarkan status
                                $statusClass = match ($status) {
                                    'disetujui' => 'bg-success',
                                    'ditolak' => 'bg-danger',
                                    default => 'bg-warning'
                                };          
                                                           
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= isset($row['tanggal_pengaduan']) ? date('d/m/Y', strtotime($row['tanggal_pengaduan'])) : '-' ?></td>
                            <td><?= htmlspecialchars($row['nama']) ?></td>
                            <td>
                            <a href="https://wa.me/<?= urlencode($row['nomor_hp']) ?>?text=Kami%20dari%20BPBD%20Kabupaten%20Kudus%20siap%20membantu%20dalam%20penanganan%20bencana.%20Segera%20laporkan%20kejadian%20bencana%20untuk%20tindakan%20cepat%20dan%20tepat." target="_blank">
                                <?= htmlspecialchars($row['nomor_hp']) ?>
                            </a>

                            </td>
                            <td><?= substr(htmlspecialchars($row['kronologi']), 0, 50) . '...' ?></td>
                            <td><span class="badge <?= $statusClass ?>"><?= $row['status'] ?? 'Pending' ?></span></td>
                            <td class="action-buttons">
                                <button type="button" class="btn btn-info btn-sm" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#detailModal<?= $row['id'] ?>">
                                    <i class="bi bi-eye"></i> Detail
                                </button>
                                <button class="btn btn-success btn-sm" onclick="prosesPengaduan(<?= $row['id'] ?>, 'approve')">
                                    <i class="bi bi-check-circle"></i> Terima
                                </button>
                                <button class="btn btn-danger btn-sm" onclick="prosesPengaduan(<?= $row['id'] ?>, 'reject')">
                                    <i class="bi bi-x-circle"></i> Tolak
                                </button>
                                <button class="btn btn-danger btn-sm" onclick="prosesPengaduan(<?= $row['id'] ?>, 'delete')">
                                    <i class="bi bi-trash"></i> Hapus
                                </button>
                            </td>
                        </tr>

                        <!-- Detail Modal for each row -->
                        <div class="modal fade" id="detailModal<?= $row['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Detail Pengaduan</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <?php if(!empty($row['gambar']) && file_exists('../' . ltrim($row['gambar'], './'))): ?>
                                            <div class="image-container">
                                                <div class="detail-label">Foto Kejadian:</div>
                                                <img src="../<?= ltrim($row['gambar'], './') ?>" 
                                                    alt="Foto Kejadian"
                                                    class="img-fluid">
                                            </div>
                                        <?php endif; ?>

                                        <div class="detail-item">
                                            <div class="detail-label">Nama Pelapor:</div>
                                            <div class="detail-value"><?= htmlspecialchars($row['nama']) ?></div>
                                        </div>

                                        <div class="detail-item">
                                            <div class="detail-label">Nomor HP:</div>
                                            <div class="detail-value">
                                            <a href="https://wa.me/<?= urlencode($row['nomor_hp']) ?>?text=Kami%20dari%20BPBD%20Kabupaten%20Kudus%20siap%20membantu%20dalam%20penanganan%20bencana.%20Segera%20laporkan%20kejadian%20bencana%20untuk%20tindakan%20cepat%20dan%20tepat." target="_blank">
                                                <?= htmlspecialchars($row['nomor_hp']) ?>
                                            </a>
                                            </div>
                                        </div>  

                                        <div class="detail-item">
                                            <div class="detail-label">Tanggal Kejadian:</div>
                                            <div class="detail-value"><?= date('d/m/Y', strtotime($row['tanggal_pengaduan'])) ?></div>
                                        </div>

                                        <div class="detail-item">
                                            <div class="detail-label">Kronologi Lengkap:</div>
                                            <div class="detail-value"><?= nl2br(htmlspecialchars($row['kronologi'])) ?></div>
                                        </div>

                                        <div class="detail-item">
                                            <div class="detail-label">Alamat Detail:</div>
                                            <div class="detail-value"><?= htmlspecialchars($row['alamat']) ?></div>
                                        </div>

                                        <div class="detail-item">
                                            <div class="detail-label">Status:</div>
                                            <div class="detail-value">
                                                <span class="badge <?= $statusClass ?>"><?= $row['status'] ?? 'Pending' ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                        <?php if($row['status'] !== 'Diterima' && $row['status'] !== 'Ditolak'): ?>
                                            <a href="pengaduan.php?action=approve&id=<?= $row['id'] ?>" 
                                            class="btn btn-success">
                                                <i class="bi bi-check-circle"></i> Terima
                                            </a>
                                            <a href="pengaduan.php?action=reject&id=<?= $row['id'] ?>" 
                                            class="btn btn-danger">
                                                <i class="bi bi-x-circle"></i> Tolak
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php } } else { ?>
                            <tr><td colspan="7" class="text-center">Tidak ada pengaduan untuk ditampilkan</td></tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
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

function prosesPengaduan(id, action) {
    Swal.fire({
        title: 'Konfirmasi',
        text: `Apakah Anda yakin ingin ${action === 'approve' ? 'menerima' : action === 'reject' ? 'menolak' : 'menghapus'} pengaduan ini?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, lanjutkan!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('pengaduan.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${id}&action=${action}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire('Berhasil!', data.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Gagal!', data.message, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error!', 'Terjadi kesalahan koneksi.', 'error');
            });
        }
    });
}
</script>

</body>
</html>