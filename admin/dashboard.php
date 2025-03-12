    <?php
    session_start();
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
        <link rel="stylesheet" href="./css/dashboard.css">
    </head>
    <body>
        <?php echo $alert; ?>

        <button class="btn btn-dark toggle-sidebar">
            <i class="bi bi-list"></i>
        </button>

        <div class="container-fluid">
            <div class="row">
                <!-- Sidebar -->
                <div class="col-md-2 sidebar">
                    <div class="text-center mb-4">
                        <img src="../assets/image/logo.jpg" alt="BPBD Logo" class="img-fluid rounded-circle" style="width: 100px;">
                        <h5 class="text-white mt-3">Admin BPBD</h5>
                    </div>
                    <a href="./dashboard.php" class="active"><i class="bi bi-house-door"></i> Dashboard</a>
                    <a href="./pengaduan.php"><i class="bi bi-file-text"></i> Pengaduan</a>
                    <a href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
                </div>

                <!-- Main Content -->
                <div class="col-md-10 main-content">
                    <h2 class="mb-4">Dashboard Admin</h2>
                    
                    <!-- Statistik Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card stats-card bg-primary text-white" onclick="window.location.href='?status=all'">
                                <div class="card-body">
                                    <h5 class="card-title">Total Pengaduan</h5>
                                    <h2 class="card-text"><?= $stats['total'] ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stats-card bg-success text-white" onclick="window.location.href='?status=Disetujui '">
                                <div class="card-body">
                                    <h5 class="card-title">Disetujui</h5>
                                    <h2 class="card-text"><?= $stats['disetujui'] ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stats-card bg-warning" onclick="window.location.href='?status=pending'">
                                <div class="card-body">
                                    <h5 class="card-title">Pending</h5>
                                    <h2 class="card-text"><?= $stats['pending'] ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
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
                                            <td><?= htmlspecialchars($row['kronologi']) ?></td>
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
                                            <tr><td colspan="8" class="text-center">Tidak ada pengaduan</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const toggleBtn = document.querySelector('.toggle-sidebar');
                const sidebar = document.querySelector('.sidebar');
                const mainContent = document.querySelector('.main-content');
                
                toggleBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('collapsed');
                    mainContent.classList.toggle('expanded');
                });
            });
        </script>
    </body>
    </html>