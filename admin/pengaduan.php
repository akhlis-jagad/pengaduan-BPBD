<?php
session_start();
include '../koneksi.php';

// Ambil data pengaduan dari database
$query = "SELECT * FROM pengaduan ORDER BY id DESC";
$result = mysqli_query($conn, $query);

$alert = "";

// Handle approval/rejection
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $action = $_GET['action'];
    $status = ($action == 'approve') ? 'disetujui' : 'ditolak'; // Pastikan aksi disesuaikan dengan kondisi
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



// Handle deletion
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Menggunakan prepared statement untuk menghindari SQL injection
    $stmt = $conn->prepare("DELETE FROM pengaduan WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $alert = "<script>
            Swal.fire({
                title: 'Berhasil!',
                text: 'Pengaduan berhasil dihapus',
                icon: 'success',
                confirmButtonText: 'OK'
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
    <link rel="stylesheet" href="./css/pengaduan.css">
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2 sidebar">
            <div class="text-center mb-4">
                <img src="../assets/image/logo.jpg" alt="BPBD Logo" class="img-fluid rounded-circle" style="width: 100px;">
                <h5 class="text-white mt-3">Admin BPBD</h5>
            </div>
            <a href="./dashboard.php"><i class="bi bi-house-door"></i> Dashboard</a>
            <a href="" class="active"><i class="bi bi-file-text"></i> Pengaduan</a>
            <a href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
        </div>

        <!-- Main Content -->
        <div class="col-md-10 main-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Daftar Pengaduan</h2>
            </div>

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
                                    <td><?= htmlspecialchars($row['nomor_hp']) ?></td>
                                    <td><?= substr(htmlspecialchars($row['kronologi']), 0, 50) . '...' ?></td>
                                    <td><span class="badge <?= $statusClass ?>"><?= $row['status'] ?? 'Pending' ?></span></td>
                                    <td class="action-buttons">
                                        <button type="button" class="btn btn-info btn-sm" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#detailModal<?= $row['id'] ?>">
                                            <i class="bi bi-eye"></i> Detail
                                        </button>
                                        <?php if($row['status'] !== 'Diterima' && $row['status'] !== 'Ditolak'): ?>
                                            <a href="pengaduan.php?action=approve&id=<?= $row['id'] ?>" 
                                            class="btn btn-success btn-sm" 
                                            onclick="return confirm('Yakin ingin menerima pengaduan?')">
                                                <i class="bi bi-check-circle"></i> Terima
                                            </a>
                                            <a href="pengaduan.php?action=reject&id=<?= $row['id'] ?>" 
                                            class="btn btn-danger btn-sm"
                                            onclick="return confirm('Yakin ingin menolak pengaduan?')">
                                                <i class="bi bi-x-circle"></i> Tolak
                                            </a>
                                            <a href="pengaduan.php?action=delete&id=<?= $row['id'] ?>" 
                                            class="btn btn-danger btn-sm"
                                            onclick="return confirm('Yakin ingin menghapus pengaduan?')">
                                                <i class="bi bi-trash"></i> Hapus
                                            </a>
                                        <?php endif; ?>
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
                                                    <div class="detail-value"><?= htmlspecialchars($row['nomor_hp']) ?></div>
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
                                    <tr><td colspan="7">Tidak ada pengaduan untuk ditampilkan</td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
