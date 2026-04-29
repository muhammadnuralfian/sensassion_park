-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 29, 2026 at 07:01 AM
-- Server version: 5.7.39
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sensasion`
--

-- --------------------------------------------------------

--
-- Table structure for table `event`
--

CREATE TABLE `event` (
  `id` int(10) UNSIGNED NOT NULL,
  `nama` varchar(255) NOT NULL,
  `deskripsi` text,
  `jadwal` varchar(100) DEFAULT NULL,
  `biaya` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `icon` varchar(60) DEFAULT 'bi-calendar-event',
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `dibuat_pada` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `event`
--

INSERT INTO `event` (`id`, `nama`, `deskripsi`, `jadwal`, `biaya`, `icon`, `status`, `dibuat_pada`) VALUES
(1, 'Lomba Mancing Bulanan', 'Event lomba mancing rutin 2x sebulan dengan berbagai hadiah menarik.', '2x per bulan', 250000, 'bi-trophy', 'aktif', '2026-04-29 08:53:37'),
(2, 'Program Ekskul Renang', 'Program renang pagi hari untuk anak-anak sekolah. Aman dan terawasi.', 'Setiap hari 09.00-12.00', 0, 'bi-mortarboard', 'aktif', '2026-04-29 08:53:37'),
(3, 'Gathering & Acara Privat', 'Gazebo bisa disewa untuk acara privat dengan reservasi lebih dulu.', 'On request', 0, 'bi-people-fill', 'aktif', '2026-04-29 08:53:37');

-- --------------------------------------------------------

--
-- Table structure for table `fasilitas`
--

CREATE TABLE `fasilitas` (
  `id` int(10) UNSIGNED NOT NULL,
  `nama_fasilitas` varchar(255) NOT NULL,
  `deskripsi` text,
  `gambar` varchar(512) DEFAULT NULL,
  `urutan` smallint(6) NOT NULL DEFAULT '0',
  `dibuat_pada` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `diubah_pada` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `fasilitas`
--

INSERT INTO `fasilitas` (`id`, `nama_fasilitas`, `deskripsi`, `gambar`, `urutan`, `dibuat_pada`, `diubah_pada`) VALUES
(1, 'Kolam Pemancingan', 'Kolam ikan segar dengan berbagai jenis ikan pilihan. Cocok untuk semua usia.', NULL, 1, '2026-04-29 08:53:37', '2026-04-29 08:53:37'),
(2, 'Kolam Renang Keluarga', 'Kolam renang bersih dan terawat untuk dewasa & anak-anak.', NULL, 2, '2026-04-29 08:53:37', '2026-04-29 08:53:37'),
(3, 'Gazebo Tepi Kolam', 'Area bersantai di tepi kolam dengan pemandangan indah.', NULL, 3, '2026-04-29 08:53:37', '2026-04-29 08:53:37'),
(4, 'Kantin & Kuliner', 'Menu pilihan lezat dengan harga terjangkau di tepi kolam.', NULL, 4, '2026-04-29 08:53:37', '2026-04-29 08:53:37');

-- --------------------------------------------------------

--
-- Table structure for table `galeri`
--

CREATE TABLE `galeri` (
  `id` int(10) UNSIGNED NOT NULL,
  `judul` varchar(255) NOT NULL,
  `deskripsi` text,
  `kategori` enum('pemancingan','renang','fasilitas','aktivitas','umum') NOT NULL DEFAULT 'umum',
  `nama_file` varchar(255) NOT NULL,
  `path_file` varchar(512) NOT NULL,
  `tampil` tinyint(1) NOT NULL DEFAULT '1',
  `urutan` smallint(6) NOT NULL DEFAULT '0',
  `dibuat_pada` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `harga`
--

CREATE TABLE `harga` (
  `id` int(10) UNSIGNED NOT NULL,
  `nama` varchar(255) NOT NULL,
  `harga` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `satuan` varchar(60) DEFAULT 'per orang',
  `catatan` varchar(255) DEFAULT NULL,
  `icon` varchar(60) DEFAULT 'bi-ticket',
  `gambar` varchar(512) DEFAULT NULL,
  `aktif` tinyint(1) NOT NULL DEFAULT '1',
  `urutan` smallint(6) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `harga`
--

INSERT INTO `harga` (`id`, `nama`, `harga`, `satuan`, `catatan`, `icon`, `aktif`, `urutan`) VALUES
(1, 'Kolam Renang', 25000, 'per orang', 'Berlaku semua usia', 'bi-water', 1, 1),
(2, 'Pemancingan', 5000, 'per orang', 'Harga ikan mengikuti pasar', 'bi-fish', 1, 2),
(3, 'Gazebo', 50000, 'per sesi', 'Hingga selesai digunakan', 'bi-house-heart', 1, 3),
(4, 'Umpan Mancing', 15000, 'per paket', 'Tersedia di lokasi', 'bi-droplet', 1, 4),
(5, 'Lomba Mancing', 250000, 'per peserta', '2x per bulan, kondisional', 'bi-trophy', 1, 5);

-- --------------------------------------------------------

--
-- Table structure for table `reservasi`
--

CREATE TABLE `reservasi` (
  `id` int(10) UNSIGNED NOT NULL,
  `kode_reservasi` varchar(20) NOT NULL,
  `nama` varchar(150) NOT NULL,
  `telepon` varchar(20) NOT NULL,
  `tanggal_kunjungan` date NOT NULL,
  `jumlah_pengunjung` int(10) UNSIGNED NOT NULL DEFAULT '1',
  `jenis_kunjungan` varchar(100) NOT NULL DEFAULT 'Umum',
  `catatan` text,
  `qr_path` varchar(512) DEFAULT NULL,
  `status` enum('menunggu','disetujui','selesai','dibatalkan') NOT NULL DEFAULT 'menunggu',
  `dibuat_pada` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `diubah_pada` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `reservasi`
--

INSERT INTO `reservasi` (`id`, `kode_reservasi`, `nama`, `telepon`, `tanggal_kunjungan`, `jumlah_pengunjung`, `jenis_kunjungan`, `catatan`, `qr_path`, `status`, `dibuat_pada`, `diubah_pada`) VALUES
(1, 'SNS260429D9F41', 'inem', '08123456789', '2026-04-29', 2, 'Ekskul Renang', NULL, 'public/qrcodes/qr_SNS260429D9F41.png', 'disetujui', '2026-04-29 10:41:19', '2026-04-29 10:42:43'),
(2, 'SNS260429356C2', 'inem', '08123456789', '2026-04-29', 1, 'Umum (Renang + Mancing)', NULL, 'public/qrcodes/qr_SNS260429356C2.png', 'menunggu', '2026-04-29 11:46:56', '2026-04-29 11:46:56'),
(3, 'SNS260429AC24A', 'inem', '08123456789', '2026-04-29', 1, 'Ekskul Renang', NULL, 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=http%3A%2F%2Flocalhost%3A3000%2Fsensasion_gabung%2Fverifikasi.php%3Fkode%3DSNS260429AC24A&format=png&ecc=M&margin=10', 'menunggu', '2026-04-29 11:55:01', '2026-04-29 11:55:01'),
(4, 'SNS260429743E1', 'unem', '08123456789', '2026-04-29', 1, 'Umum (Renang + Mancing)', NULL, NULL, 'menunggu', '2026-04-29 12:04:52', '2026-04-29 12:04:52');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `key` varchar(100) NOT NULL,
  `value` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`) VALUES
(1, 'nama_tempat', 'Sensasion'),
(2, 'tagline', 'Tempat Terbaik untuk Waktu Berkualitas Bersama Keluarga'),
(3, 'whatsapp', '6281234567890'),
(4, 'instagram', '@sensasion_samarinda'),
(5, 'lokasi', 'Samarinda, Kalimantan Timur'),
(6, 'jam_buka', '09.00 – 18.00 WITA'),
(7, 'hari_libur', 'Senin & Hari Besar Nasional'),
(8, 'pengelola', 'Pak Deni'),
(9, 'tahun_berdiri', '2020');

-- --------------------------------------------------------

--
-- Table structure for table `ulasan`
--

CREATE TABLE `ulasan` (
  `id` int(10) UNSIGNED NOT NULL,
  `nama` varchar(100) NOT NULL,
  `komentar` text NOT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL DEFAULT '5',
  `tanggal` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `tampil` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `ulasan`
--

INSERT INTO `ulasan` (`id`, `nama`, `komentar`, `rating`, `tanggal`, `tampil`) VALUES
(1, 'Budi Santoso', 'Tempatnya asik banget! Ikannya gampang kena, gazebonya nyaman.', 5, '2026-04-24 08:53:37', 1),
(2, 'Siti Rahayu', 'Kolam renangnya bersih dan aman buat anak-anak. Harganya terjangkau.', 5, '2026-04-26 08:53:37', 1),
(3, 'Ahmad Fauzi', 'Suasananya enak, cocok buat santai bareng teman. Kantin enak juga.', 4, '2026-04-27 08:53:37', 1),
(4, 'Dewi Lestari', 'Anak-anak suka banget! Renang pagi, siang mancing. Paket komplit!', 5, '2026-04-28 08:53:37', 1),
(5, 'Rizky Pratama', 'Gazebonya nyaman, view ke kolam bagus. Harga sangat worth it!', 4, '2026-04-28 20:53:37', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `nama` varchar(150) NOT NULL,
  `email` varchar(200) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','operator') NOT NULL DEFAULT 'operator',
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `foto_profil` varchar(500) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `dibuat_pada` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `email`, `password`, `role`, `status`, `foto_profil`, `last_login`, `dibuat_pada`) VALUES
(2, 'keisya', 'nfsakeisya@gmail.com', '$2y$10$hJj/ymorNT86QQGd7Oadu.6w0b8n.oZ9oO1cVw3io7d24KGZDbDw6', 'admin', 'aktif', NULL, '2026-04-29 08:59:33', '2026-04-29 08:58:47'),
(3, 'nanda', 'nandananda@gmail.com', '$2y$10$qqts3UmRw3e0Lgdv57HbjONlsojLZwV.js8./qleldfqPcGNeNNua', 'operator', 'aktif', '/sensasion_gabung/uploads/profil/profil_3_520d5f4c43aa.png', '2026-04-29 13:35:39', '2026-04-29 08:59:04');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `event`
--
ALTER TABLE `event`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `fasilitas`
--
ALTER TABLE `fasilitas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_urutan` (`urutan`);

--
-- Indexes for table `galeri`
--
ALTER TABLE `galeri`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_kategori` (`kategori`),
  ADD KEY `idx_tampil` (`tampil`);

--
-- Indexes for table `harga`
--
ALTER TABLE `harga`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_aktif` (`aktif`);

-- ⚠️ MIGRASI: Jika database sudah ada sebelumnya, jalankan query ini:
-- ALTER TABLE `harga` ADD COLUMN `gambar` VARCHAR(512) DEFAULT NULL AFTER `icon`;

--
-- Indexes for table `reservasi`
--
ALTER TABLE `reservasi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_reservasi` (`kode_reservasi`),
  ADD KEY `idx_tanggal` (`tanggal_kunjungan`),
  ADD KEY `idx_kode` (`kode_reservasi`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`);

--
-- Indexes for table `ulasan`
--
ALTER TABLE `ulasan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tampil` (`tampil`),
  ADD KEY `idx_rating` (`rating`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `event`
--
ALTER TABLE `event`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `fasilitas`
--
ALTER TABLE `fasilitas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `galeri`
--
ALTER TABLE `galeri`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `harga`
--
ALTER TABLE `harga`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `reservasi`
--
ALTER TABLE `reservasi`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `ulasan`
--
ALTER TABLE `ulasan`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
