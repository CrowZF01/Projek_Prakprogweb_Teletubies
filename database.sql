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
-- DATA SEEDER HASIL KONVERSI (ACCOUNTS, CAMPAIGNS, & DONATIONS)
-- ========================================================

-- Data Akun User & Manager
INSERT INTO `user` (`id`, `username`, `password`, `nama_lengkap`, `email`, `role`) VALUES
(1, 'Arvin', 'gatau123', 'Arvin', 'arvinukdw@gmail.com', 'donor'),
(3, 'yayasanhijaubumi', 'password123', 'Yayasan Hijau Bumi', 'hijaubumi@example.com', 'manager'),
(4, 'pedulisehat', 'password123', 'Peduli Sehat Indonesia', 'pedulisehat@example.com', 'manager'),
(5, 'sahabatedukasi', 'password123', 'Sahabat Edukasi', 'sahabatedukasi@example.com', 'manager'),
(6, 'relawannusantara', 'password123', 'Relawan Nusantara', 'relawannusantara@example.com', 'manager'),
(7, 'kalibersih', 'password123', 'Komunitas Kali Bersih', 'kalibersih@example.com', 'manager'),
(8, 'kasihibu', 'password123', 'Klinik Kasih Ibu', 'kasihibu@example.com', 'manager'),
(9, 'indonesiapintar', 'password123', 'Yayasan Indonesia Pintar', 'indonesiapintar@example.com', 'manager'),
(10, 'aksicepat', 'password123', 'Aksi Cepat Kemanusiaan', 'aksicepat@example.com', 'manager'),
(11, 'sahabatpenyu', 'password123', 'Sahabat Penyu Nusantara', 'sahabatpenyu@example.com', 'manager'),
(12, 'lembagasosial', 'password123', 'Lembaga Sosial Masyarakat', 'lembagasosial@example.com', 'manager'),
(13, 'gurupeduli', 'password123', 'Komunitas Guru Peduli', 'gurupeduli@example.com', 'manager'),
(14, 'globalhumanity', 'password123', 'Global Humanity Action', 'globalhumanity@example.com', 'manager'),
(16, 'i don''t know', 'gataubg', 'idk', 'idk123@gmail.com', 'donor')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- Data Kampanye
INSERT INTO `campaign` (`id`, `judul`, `sub_judul`, `kategori`, `lokasi`, `deskripsi`, `deskripsi_lengkap`, `target_dana`, `dana_terkumpul`, `penyelenggara`, `deadline`, `gambar`) VALUES
(4, 'Penanaman 1000 Mangrove di Pantai Indah', 'Upaya mencegah abrasi pantai dan melindungi ekosistem pesisir.', 'Lingkungan', 'Mamasa', 'Penanaman mangrove sangat penting untuk menjaga garis pantai dari abrasi ekstrem serta menjadi habitat alami bagi berbagai jenis biota laut.', 'Penanaman mangrove sangat penting untuk menjaga garis pantai dari abrasi ekstrem serta menjadi habitat alami bagi berbagai jenis biota laut.', 15000000, 12000000, 'Yayasan Hijau Bumi', '2027-06-09', 'uploads/mangrove.jpg'),
(5, 'Pengobatan Gratis untuk Lansia Pelosok', 'Menghadirkan layanan kesehatan layak bagi lansia di desa terpencil.', 'Kesehatan', 'Gunungkidul', 'Penyediaan pemeriksaan fisik mendalam dan obat gratis untuk para lansia.', 'Layanan kesehatan di pelosok masih sangat minim. Melalui program ini, kami mendatangkan dokter dan obat-obatan gratis langsung ke rumah para lansia dhuafa.', 25000000, 25000000, 'Peduli Sehat Indonesia', '2025-08-20', 'pengobatan_lansia.jpg'),
(6, 'Renovasi Sekolah Rusak di Desa Sukamaju', 'Bantu anak-anak belajar dengan aman di ruang kelas yang layak.', 'Pendidikan', 'Garut', 'Renovasi atap bocor dan dinding retak gedung sekolah dasar setempat.', 'Kondisi sekolah dasar di Desa Sukamaju saat ini sangat memprihatinkan dengan atap yang bocor ketika hujan, menghalangi proses belajar mengajar anak-anak.', 45000000, 30000000, 'Sahabat Edukasi', '2025-11-10', 'renovasi_sekolah.jpg'),
(7, 'Bantuan Pangan Penyintas Gempa Cianjur', 'Penyaluran paket sembako untuk keluarga terdampak bencana alam.', 'Kemanusiaan', 'Cianjur', 'Paket makanan pokok untuk masa pemulihan pasca bencana gempa.', 'Meskipun status darurat telah lewat, banyak warga kehilangan mata pencaharian. Bantuan pangan ini bertujuan membantu masa pemulihan ekonomi mereka.', 30000000, 5000000, 'Relawan Nusantara', '2027-02-18', 'uploads/bantuan_gempa.jpg'),
(8, 'Bersih-Bersih Sungai Ciliwung dari Sampah Plastik', 'Aksi nyata mengurangi pencemaran sungai terbesar di Jakarta.', 'Lingkungan', 'Jakarta', 'Sungai Ciliwung membutuhkan perhatian kita bersama. Kami menggalang dana untuk menyewa alat berat portabel dan menyediakan fasilitas tempat sampah ter', 'Sungai Ciliwung membutuhkan perhatian kita bersama. Kami menggalang dana untuk menyewa alat berat portabel dan menyediakan fasilitas tempat sampah terpilah.', 10000000, 4010000, 'Komunitas Kali Bersih', '2027-04-12', 'uploads/1780252466_sungaiCiliwung.jpg'),
(9, 'Operasi Katarak Gratis untuk Warga Kurang Mampu', 'Membantu mengembalikan penglihatan indera mata para lansia dhuafa.', 'Kesehatan', 'Solo', 'Program operasi katarak gratis bagi penderita tidak mampu di daerah Jawa Tengah.', 'Katarak masih menjadi salah satu penyebab utama kebutaan yang sebenarnya bisa diatasi. Bantuan Anda akan digunakan sepenuhnya untuk biaya medis operasi.', 50000000, 15000000, 'Klinik Kasih Ibu', '2027-06-30', 'uploads/operasi_katarak.jpg'),
(10, 'Beasiswa Anak Yatim Berprestasi', 'Menjamin kelangsungan pendidikan anak yatim hingga lulus SMA.', 'Pendidikan', 'Surabaya', 'Pemberian bantuan biaya sekolah bulanan dan perlengkapan belajar.', 'Pendidikan adalah hak setiap anak. Program ini fokus menyalurkan bantuan beasiswa bagi anak-anak yatim berprestasi agar tidak putus sekolah di tengah jalan.', 60000000, 10000000, 'Yayasan Indonesia Pintar', '2027-08-15', 'uploads/beasiswa_yatim.jpg'),
(11, 'Pembangunan Sumur Bor untuk Desa Kekeringan', 'Akses air bersih untuk warga di wilayah terdampak kekeringan ekstrem.', 'Kemanusiaan', 'NTT', 'Pembuatan sumur bor sedalam 80 meter dan instalasi pipa air ke pemukiman warga.', 'Selama bertahun-tahun, warga harus berjalan berkilo-kilometer untuk mendapatkan air bersih. Sumur bor ini akan menjadi solusi jangka panjang kebutuhan air mereka.', 75000000, 45000000, 'Aksi Cepat Kemanusiaan', '2027-10-05', 'uploads/sumur_bor.jpg'),
(12, 'Konservasi Penyu Lekang di Pantai Samas', 'Melindungi telur dan melepaskan tukik kembali ke habitat aslinya.', 'Lingkungan', 'Bantul', 'Pembuatan area penangkaran telur penyu yang aman dari predator dan perburuan liar.', 'Penyu lekang saat ini statusnya semakin terancam punah. Konservasi ini fokus melakukan edukasi bagi nelayan sekitar serta menjaga area pendaratan telur.', 20000000, 8000000, 'Sahabat Penyu Nusantara', '2027-11-20', 'uploads/konservasi_penyu.jpg'),
(13, 'Ambulans Gratis untuk Pasien Dhuafa', 'Pengadaan armada mobil ambulans untuk keadaan darurat medis tanpa biaya.', 'Kesehatan', 'Malang', 'Penyediaan layanan mobil ambulans gratis bagi warga kurang mampu di Malang Raya.', 'Banyak warga dhuafa kesulitan menyewa ambulans saat keadaan darurat. Ambulans ini akan beroperasi 24 jam penuh untuk mengantar jemput pasien yang membutuhkan.', 150000000, 90000000, 'Lembaga Sosial Masyarakat', '2027-12-01', 'uploads/ambulans_dhuafa.jpg'),
(14, 'Pengadaan Laptop untuk SMK Pelosok', 'Membantu siswa SMK mempelajari keahlian teknologi digital terkini.', 'Pendidikan', 'Jayapura', 'Penyediaan unit laptop layak pakai untuk praktik laboratorium komputer sekolah.', 'Siswa SMK jurusan komputer di sekolah mitra kami selama ini hanya belajar teori tanpa praktik langsung karena keterbatasan perangkat teknologi informasi.', 80000000, 20000000, 'Komunitas Guru Peduli', '2027-12-15', 'uploads/laptop_smk.jpg'),
(15, 'Hangatkan Musim Dingin Pengungsi Palestina', 'Penyaluran selimut, pakaian tebal, dan alat pemanas ruangan.', 'Kemanusiaan', 'Gaza', 'Paket bantuan musim dingin darurat untuk keluarga di kamp pengungsian.', 'Musim dingin ekstrem menjadi tantangan berat bagi para pengungsi yang tinggal di tenda-tenda darurat. Bantuan Anda akan dikonversi menjadi paket penghangat.', 200000000, 120000000, 'Global Humanity Action', '2027-12-30', 'uploads/musim_dingin_gaza.jpeg'),
(16, 'Bersihkan Warga Ciliwung', 'Aksi nyata mengurangi pencemaran sungai terbesar di Jakarta.', 'Lingkungan', 'Jakarta', 'Sungai Ciliwung membutuhkan perhatian kita bersama. Kami menggalang dana untuk menyewa alat berat portabel dan menyediakan fasilitas tempat sampah ter', 'Sungai Ciliwung membutuhkan perhatian kita bersama. Kami menggalang dana untuk menyewa alat berat portabel dan menyediakan fasilitas tempat sampah terpilah.', 10000000, 4000000, 'Komunitas Kali Bersih', '2027-04-12', 'uploads/1780252466_sungaiCiliwung.jpg')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- Data Riwayat Donasi
INSERT INTO `donasi` (`id_donasi`, `user_id`, `campaign_id`, `nominal_donasi`, `metode_pembayaran`, `pesan_dukungan`, `bukti_transfer`, `status`, `tgl_donasi`) VALUES
(5, 1, 12, 20000, 'Gopay', 'semangat bosku', '1780251032_Screenshot 2025-02-05 205026.png', 'PENDING', '2026-05-31 18:10:32'),
(6, 1, 8, 2000000, 'Gopay', 'a', '1780251347_Screenshot 2025-02-05 205026.png', 'BERHASIL', '2026-05-31 18:15:47'),
(7, 1, 8, 200000, 'Gopay', 'haha', '1780253327_WhatsApp Image 2026-06-01 at 01.53.39.jpeg', 'DITOLAK', '2026-05-31 18:48:47'),
(8, 16, 8, 10000, 'Gopay', 'tes', '1780274916_Screenshot 2026-05-31 150801.png', 'BERHASIL', '2026-06-01 00:48:36')
ON DUPLICATE KEY UPDATE `id_donasi`=`id_donasi`;