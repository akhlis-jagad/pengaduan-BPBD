<?php
session_start();
include 'koneksi.php';

// Initialize variables
$alert = "";
$errors = [];

// Create uploads directory if it doesn't exist
$upload_dir = __DIR__ . '/uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// CSRF Protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed');
    }

    // Sanitize and validate input
    $nama = filter_input(INPUT_POST, 'nama', FILTER_SANITIZE_STRING);
    $nomor_hp = filter_input(INPUT_POST, 'nomor_hp', FILTER_SANITIZE_STRING);
    $kronologi = filter_input(INPUT_POST, 'kronologi', FILTER_SANITIZE_STRING);
    $alamat = filter_input(INPUT_POST, 'alamat', FILTER_SANITIZE_STRING);
    $tanggal = filter_input(INPUT_POST, 'tanggal', FILTER_SANITIZE_STRING);
    
    // Upload image
    $gambar_path = "";
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $filename = $_FILES['gambar']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_filename = uniqid() . '.' . $ext;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['gambar']['tmp_name'], $upload_path)) {
                // Save relative path from root website
                $gambar_path = './uploads/' . $new_filename;
            } else {
                $errors[] = "Failed to upload file. Error: " . error_get_last()['message'];
            }
        } else {
            $errors[] = "Invalid image format. Please use JPG, JPEG, or PNG.";
        }
    }

    // Validation
    if (empty($nama)) $errors[] = "Nama harus diisi";
    if (empty($nomor_hp)) $errors[] = "Nomor HP harus diisi";
    if (empty($kronologi)) $errors[] = "Kronologi harus diisi";
    if (empty($alamat)) $errors[] = "Alamat harus diisi";
    if (empty($tanggal) || !strtotime($tanggal)) $errors[] = "Tanggal tidak valid";

    if (empty($errors)) {
        try {
            // Insert into database
            $stmt = $conn->prepare("INSERT INTO pengaduan (nama, nomor_hp, kronologi, alamat, tanggal_pengaduan, gambar) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $nama, $nomor_hp, $kronologi, $alamat, $tanggal, $gambar_path);

            if ($stmt->execute()) {
                $alert = "
                    <script>
                        Swal.fire({
                            title: 'Berhasil!',
                            text: 'Pengaduan berhasil dikirim.',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.href = 'form.php';
                        });
                    </script>
                ";
            } else {
                throw new Exception($stmt->error);
            }
        } catch (Exception $e) {
            $alert = "
                <script>
                    Swal.fire({
                        title: 'Gagal!',
                        text: 'Terjadi kesalahan sistem: " . addslashes($e->getMessage()) . "',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                </script>
            ";
        }
    } else {
        $alert = "
            <script>
                Swal.fire({
                    title: 'Error!',
                    text: '" . addslashes(implode(", ", $errors)) . "',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            </script>
        ";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pengaduan - BPBD Kabupaten Kudus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="css/form.css">
</head>
<body>
    <!-- Enhanced Navbar -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="assets/image/logo.jpg" alt="BPBD Logo">
                <span class="brand-text">BPBD Kabupaten Kudus</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-home"></i> Beranda
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="form.php">
                            <i class="fas fa-file-alt"></i> Pengaduan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cooming_soon.html">
                            <i class="fas fa-search"></i> Cek Status
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Form Container -->
    <div class="container form-container">
        <h2 class="text-center mb-4">Form Pengaduan Bencana</h2>
        <form method="POST" action="" enctype="multipart/form-data" onsubmit="return validatePhoneNumber()">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nama" class="form-label">Nama Lengkap</label>
                    <input type="text" class="form-control" id="nama" name="nama" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="nama" class="form-label">Nomor HP (WhatsApp)</label>
                    <input type="tel" class="form-control" id="nomor_hp" name="nomor_hp" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="tanggal" class="form-label">Tanggal Kejadian</label>
                    <input type="date" class="form-control" id="tanggal" name="tanggal" required>
                </div>
            </div>

            <div class="mb-3">
                <label for="kronologi" class="form-label">Kronologi Kejadian</label>
                <textarea class="form-control" id="kronologi" name="kronologi" rows="4" required></textarea>
            </div>

            <div class="mb-3">
                <label for="alamat" class="form-label">Alamat Detail</label>
                <input type="text" class="form-control" id="alamat" name="alamat" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Foto Kejadian</label>
                <div class="custom-file-upload">
                    <input type="file" class="form-control" id="gambar" name="gambar" accept="image/*" onchange="previewImage(this)">
                    <i class="fas fa-camera"></i> Pilih Foto
                </div>
                <img id="preview" class="image-preview" style="display:none;">
            </div>

            <button type="submit" class="btn btn-primary w-100">
                <i class="fas fa-paper-plane"></i> Kirim Pengaduan
            </button>
        </form>
    </div>

    <?= $alert; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Image preview
        function previewImage(input) {
            const preview = document.getElementById('preview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Validate and adjust phone number
        function validatePhoneNumber() {
            var nomor_hp = document.getElementById('nomor_hp').value;
            if (nomor_hp.startsWith("0")) {
                nomor_hp = "+62" + nomor_hp.slice(1);
                document.getElementById('nomor_hp').value = nomor_hp;
            }
            return true; // Allow the form to submit
        }
    </script>
</body>
</html>
