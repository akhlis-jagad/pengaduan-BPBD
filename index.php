<?php 
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BPBD Kabupaten Kudus - Badan Penanggulangan Bencana Daerah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/index.css">
    
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="assets/image/logo.jpg" alt="BPBD Logo">
                <span class="ms-2 fw-bold" style="color: #FF8C00;">BPBD Kabupaten Kudus</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="form.php">Pengaduan</a>
                    <li class="nav-item">
                        <a class="nav-link" href="kontak.html">Kontak</a>
                    <li class="nav-item">
                        <a class="nav-link" href="admin/login.php">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-4">Siap Siaga Menghadapi Bencana</h1>
                    <p class="lead mb-4">BPBD Kabupaten Kudus berkomitmen untuk melindungi masyarakat dan menanggulangi bencana dengan cepat dan efektif.</p>
                    <a href="form.php" class="btn btn-primary btn-lg">Laporkan Bencana</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="services">
        <div class="container">
            <h2 class="text-center mb-5">Layanan Kami</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="service-card text-center">
                        <i class="fas fa-exclamation-triangle service-icon"></i>
                        <h3>Penanggulangan Bencana</h3>
                        <p>Penanganan cepat dan efektif untuk berbagai jenis bencana alam dan non-alam.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="service-card text-center">
                        <i class="fas fa-hand-holding-heart service-icon"></i>
                        <h3>Bantuan Darurat</h3>
                        <p>Penyaluran bantuan logistik dan kebutuhan dasar bagi korban bencana.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="service-card text-center">
                        <i class="fas fa-book service-icon"></i>
                        <h3>Edukasi Masyarakat</h3>
                        <p>Program edukasi dan sosialisasi mitigasi bencana untuk masyarakat.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Kontak Darurat -->
    <section class="emergency-contact">
        <div class="container text-center">
            <h2>Nomor Darurat 24 Jam</h2>
            <div class="emergency-number">
                <i class="fas fa-phone-alt me-3"></i>112
            </div>
            <p>Hubungi kami segera jika terjadi bencana di sekitar Anda</p>
        </div>
    </section>

   

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h4>BPBD Kabupaten Kudus</h4>
                    <p>Jl. PG Rendeng, Mlati Norowito, Kec. Kota Kudus, Kabupaten Kudus, Jawa Tengah 59319</p>
                </div>
                <div class="col-md-4 mb-4">
                    <h4>Kontak</h4>
                    <p>
                        <i class="fas fa-phone me-2"></i> +628112996112 <br>
                        <i class="fas fa-envelope me-2"></i> 
                        bpbdkabupatenkudus@gmail.com
                    </p>
                </div>
                <div class="col-md-4 mb-4">
                    <h4>Media Sosial</h4>
                    <div class="social-links">
                        <a href="https://www.facebook.com/bpbdkuduskab"><i class="fab fa-facebook"></i></a>
                        <a href="https://x.com/bpbdkudus"><i class="fab fa-twitter"></i></a>
                        <a href="https://www.instagram.com/bpbdkuduskab"><i class="fab fa-instagram"></i></a>
                        <a href="https://www.youtube.com/channel/UC-ld4aBDiY7YVZibXBacY7A"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Full-width Copyright Section -->
    <div class="text-center" style="background-color: white; color: #333; padding: 15px 0; width: 100%;">
        <p class="mb-0">Made with <i class="fas fa-heart"></i> by BPBD Kabupaten Kudus</p>
    </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>