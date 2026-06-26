-- Buat database jika belum ada
CREATE DATABASE IF NOT EXISTS `crowdfunding`;
USE `crowdfunding`;

-- --------------------------------------------------------
-- 1. Struktur dari tabel `user`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `user` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `nama_lengkap` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `role` ENUM('donor', 'manager') NOT NULL DEFAULT 'donor'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 2. Struktur dari tabel `campaign`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `campaign` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `judul` VARCHAR(255) NOT NULL,
  `sub_judul` TEXT NOT NULL,
  `kategori` ENUM('Pendidikan', 'Kesehatan', 'Lingkungan', 'Kemanusiaan') NOT NULL,
  `lokasi` VARCHAR(100) NOT NULL,
  `deskripsi` VARCHAR(150) NOT NULL, -- Ringkasan singkat untuk tampilan kartu utama
  `deskripsi_lengkap` TEXT NOT NULL,  -- Deskripsi detail kampanye
  `target_dana` INT NOT NULL,
  `dana_terkumpul` INT NOT NULL DEFAULT 0,
  `penyelenggara` VARCHAR(100) NOT NULL, -- Menghubungkan ke nama_lengkap pengelola
  `deadline` DATE NOT NULL,
  `gambar` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 3. Struktur dari tabel `donasi`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `donasi` (
  `id_donasi` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `campaign_id` INT NOT NULL,
  `nominal_donasi` INT NOT NULL,
  `metode_pembayaran` ENUM('Gopay', 'Transfer Bank', 'OVO') NOT NULL,
  `pesan_dukungan` TEXT DEFAULT NULL,
  `bukti_transfer` VARCHAR(255) NOT NULL,
  `status` ENUM('PENDING', 'BERHASIL', 'DITOLAK') NOT NULL DEFAULT 'PENDING',
  `tgl_donasi` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `user`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`campaign_id`) REFERENCES `campaign`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================================
-- DATA SEEDER UNTUK PENGUJIAN (ACCOUNTS & CAMPAIGNS)
-- ========================================================

-- Akun Demo Donatur & Pengelola (Plaintext Password)
INSERT INTO `user` (`username`, `password`, `nama_lengkap`, `email`, `role`) VALUES
('budi', 'budi123', 'Budi Santoso', 'budi@gmail.com', 'donor'),
('peduli', 'peduli123', 'Yayasan Peduli Sesama', 'info@pedulisesama.org', 'manager'),
('greenpeace', 'greenpeace123', 'Greenpeace Indonesia', 'info@greenpeace.id', 'manager')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- Data Awal Kampanye untuk Simulasi
INSERT INTO `campaign` (`id`, `judul`, `sub_judul`, `kategori`, `lokasi`, `deskripsi`, `deskripsi_lengkap`, `target_dana`, `dana_terkumpul`, `penyelenggara`, `deadline`, `gambar`) VALUES
(1, 'Bantuan Gizi Balita Stunting', 'Bantu penuhi nutrisi bagi anak-anak kurang gizi di pelosok daerah agar tumbuh sehat.', 'Kesehatan', 'Nusa Tenggara Timur', 'Bantuan pemenuhan nutrisi dan susu formula bagi anak-anak yang terindikasi stunting di wilayah pedalaman NTT.', 'Masalah stunting di NTT masih menjadi perhatian serius. Melalui kampanye ini, kami bertekad membagikan paket bahan makanan pokok bergizi tinggi seperti telur, kacang-kacangan, susu formula khusus, dan vitamin untuk 100 balita selama 3 bulan penuh. Setiap bantuan Anda akan disalurkan langsung oleh relawan Yayasan Peduli Sesama di lapangan.', 50000000, 15000000, 'Yayasan Peduli Sesama', DATE_ADD(CURDATE(), INTERVAL 25 DAY), 'uploads/stunting_campaign.jpg'),
(2, 'Tanam 1000 Mangrove Pesisir', 'Aksi penanaman mangrove di pesisir pantai utara Jawa guna mencegah abrasi air laut.', 'Lingkungan', 'Semarang', 'Dukung restorasi ekosistem pantai utara Jawa dari ancaman abrasi laut yang kian parah.', 'Abrasi pantai di wilayah pesisir Semarang kian meresahkan warga sekitar. Penanaman mangrove adalah solusi alami yang efektif untuk memecah ombak dan mengembalikan ekosistem laut. Dana yang terkumpul akan digunakan untuk pengadaan bibit bakau, perawatan bibit, dan biaya operasional para relawan pecinta lingkungan di lapangan.', 25000000, 0, 'Greenpeace Indonesia', DATE_ADD(CURDATE(), INTERVAL 45 DAY), 'uploads/mangrove_campaign.jpg'),
(3, 'Beasiswa Sekolah Anak Nelayan', 'Bantu anak-anak nelayan prasejahtera mendapatkan pendidikan yang layak.', 'Pendidikan', 'Gunungkidul', 'Program beasiswa pendidikan bagi anak-anak nelayan pantai selatan untuk melanjutkan sekolah.', 'Banyak anak-anak di pesisir Gunungkidul terancam putus sekolah karena kendala biaya perlengkapan sekolah dan transportasi. Kampanye ini berfokus membiayai kebutuhan sekolah mereka selama setahun penuh, termasuk seragam, tas, buku pelajaran, dan sepeda kayuh untuk mempermudah akses ke sekolah.', 30000000, 0, 'Yayasan Peduli Sesama', DATE_ADD(CURDATE(), INTERVAL 6 DAY), 'uploads/beasiswa_campaign.jpg')
ON DUPLICATE KEY UPDATE `id`=`id`;
