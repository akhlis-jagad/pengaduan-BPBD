-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 10 Mar 2025 pada 15.34
-- Versi server: 10.4.24-MariaDB
-- Versi PHP: 8.1.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pengaduan_db`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengaduan`
--

CREATE TABLE `pengaduan` (
  `id` int(11) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `kronologi` text NOT NULL,
  `alamat` varchar(255) NOT NULL,
  `tanggal_pengaduan` date NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `status` enum('pending','disetujui','ditolak') DEFAULT 'pending',
  `nomor_hp` int(13) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `pengaduan`
--

INSERT INTO `pengaduan` (`id`, `nama`, `kronologi`, `alamat`, `tanggal_pengaduan`, `gambar`, `status`, `nomor_hp`) VALUES
(25, 'sdsds', 'dsds', 'dsds', '2025-03-10', './uploads/67cedfd8e4da1.png', 'disetujui', 11114),
(27, 'xcxcxccx', 'fddfd', 'dfdfdfd', '2025-03-10', './uploads/67cee2bddf84c.png', 'disetujui', 2147483647),
(28, 'akhlis', 'DSDASD', 'SDSD', '2025-03-10', './uploads/67cee69c54257.png', 'ditolak', 2147483647),
(29, 'akhlis', 'sdfsfsf', 'fsfsfs', '2025-03-10', './uploads/67cee81f2cfec.png', 'disetujui', 2147483647),
(30, 'MATSURI', 'Tanah Longsor Ngebruk,i anake', 'bategede rt06 rw04 (ngisor tebing), nalumsaari, Jepara', '2025-03-10', './uploads/67cef44443b5d.png', 'disetujui', 2147483647);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`) VALUES
(222, 'suri', 'surikece123\r\n'),
(1111, 'admin', '$2y$10$DXKh4Bt6X9d2XMOLtFGxbece0luJ0aql2sE8sZw9Q2w0z5rZ7zvIy');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `pengaduan`
--
ALTER TABLE `pengaduan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `pengaduan`
--
ALTER TABLE `pengaduan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1112;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
