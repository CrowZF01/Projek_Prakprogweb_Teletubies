# 🧸 Teletubies - Platform Crowdfunding Sosial & Kemanusiaan

[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue.svg)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-orange.svg)](https://www.mysql.com/)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Design Style](https://img.shields.io/badge/Design-Neubrutalism-brightgreen.svg)](#-desain-ui-neubrutalism-modern)

**Teletubies** adalah platform crowdfunding sosial berbasis web yang dirancang khusus untuk menggalang dana bagi berbagai kampanye kemanusiaan, pendidikan, kesehatan, dan lingkungan. Aplikasi ini dibuat menggunakan PHP Native dengan desain antarmuka **Neubrutalism** yang modern, playful, dan interaktif.

Platform ini mempermudah para donatur untuk menyalurkan bantuannya secara transparan, sementara pihak pengelola dapat memverifikasi setiap donasi yang masuk serta mengelola kampanye secara terstruktur.

---

## 🌟 Fitur Utama

### 1. Sistem Multi-Role & Autentikasi
*   **Donatur (Donor):** 
    *   Menjelajahi daftar kampanye sosial yang aktif.
    *   Berdonasi dengan nominal minimal Rp 10.000 menggunakan metode pembayaran Gopay, Transfer Bank, atau OVO.
    *   Mengunggah bukti transfer pembayaran demi transparansi data.
    *   Melihat status ringkasan donasi mereka (Verified/Berhasil, Pending, Ditolak) melalui panel riwayat donasi interaktif (*slide-out pane*).
*   **Pengelola (Manager):**
    *   Dashboard manajemen kampanye personal.
    *   Melakukan operasi CRUD (Create, Read, Update, Delete) kampanye yang mereka kelola.
    *   Mengunggah foto cover kampanye dengan restriksi ekstensi (JPG, JPEG, PNG, WEBP).
    *   Memantau donasi masuk (dana berhasil & dana tertunda).
    *   Meninjau bukti transfer donatur dan melakukan verifikasi manual (Terima / Tolak donasi).
*   **Guest (Pengunjung Umum):**
    *   Melihat-lihat kampanye tanpa perlu melakukan login.

### 2. Pencarian & Penyaringan Lanjutan (Filter & Sorting)
*   **Kategori Kampanye:** Pendidikan, Kesehatan, Lingkungan, dan Kemanusiaan.
*   **Filter Batas Waktu (Urgensi):** Mendesak (≤ 7 hari), Bulan Ini (≤ 30 hari), dan Lebih dari 30 Hari.
*   **Pencarian Teks:** Berdasarkan judul kampanye, lokasi, pengelola, atau deskripsi.
*   **Pengurutan Dinamis:** Berdasarkan tanggal deadline terdekat atau nominal target dana terkecil.

### 3. Keamanan & Aturan Validasi Bisnis
*   Kampanye dengan akumulasi dana terkumpul $\ge$ Rp10.000 **tidak dapat dihapus** oleh pengelola guna menjaga akuntabilitas data keuangan.
*   Validasi berkas upload bukti transfer & cover gambar demi mencegah eksekusi kode berbahaya.

---

## 🎨 Desain UI Neubrutalism Modern
Aplikasi ini mengadopsi estetika **Neubrutalism** (Neo-Brutalism) yang saat ini sedang tren di dunia desain web modern, ditandai dengan:
*   Warna-warna kontras tinggi yang harmonis (`#25754a` sebagai hijau utama, `#fbd24b` sebagai kuning aksen, dan `#f9f9f1` sebagai latar belakang).
*   Garis tepi hitam tebal khas komik (`border: 4px solid #1a1a1a`).
*   Bayangan padat tanpa blur (`box-shadow: 12px 12px 0px #1a1a1a`).
*   Tipografi modern yang manis menggunakan Google Fonts **Plus Jakarta Sans** dan **Fredoka One**.
*   *Background dot pattern* (pola titik-titik) retro yang mempercantik tampilan kartu kampanye dan kartu login.

---

## 🛠️ Tech Stack
*   **Frontend:** HTML5, Vanilla CSS3 (Custom Neubrutalist Design System), Vanilla JavaScript (Slider menu, modal galeri bukti transfer, form submission).
*   **Backend:** Native PHP (dengan pengelolaan Session, manajemen file upload).
*   **Database:** MySQL (interaksi menggunakan ekstensi `mysqli`).

---

## 📁 Struktur Folder Proyek
```text
Projek_Prakprogweb_Teletubies/
├── bukti_transfer/         # Direktori penyimpanan unggahan bukti transfer donatur
├── uploads/                # Direktori penyimpanan unggahan gambar cover kampanye
├── logo/                   # Direktori penyimpanan logo dan ikon grafis aplikasi
├── koneksi/
│   └── koneksi.php         # Berkas konfigurasi koneksi database MySQL
├── styles/                 # Kumpulan stylesheet CSS
│   ├── styleHalUtama.css   # Style halaman beranda & riwayat donatur
│   ├── styleHalDetail.css  # Style detail kampanye & informasi dana
│   ├── styleHalDonate.css  # Style form donasi & upload bukti transfer
│   ├── styleLogin.css      # Style halaman login & pemilihan role
│   └── stylePengelola.css  # Style panel dashboard pengelola
├── editKampanye.php        # Form dan logika pembaruan & penghapusan kampanye
├── halDetail.php           # Halaman rincian kampanye & progress bar target dana
├── halDonate.php           # Halaman pengisian form donasi donatur
├── halLogin.php            # Halaman login donatur, pengelola, dan guest
├── halPengelola.php        # Dashboard pengelola (CRUD & Verifikasi Pembayaran)
├── halUtama.php            # Halaman beranda utama dengan filter pencarian & riwayat
├── logout.php              # Proses penghancuran session login
├── README.md               # Dokumentasi utama proyek
└── database.sql            # Cadangan/skema database MySQL
```

---

## 🗄️ Skema Database (MySQL)

Berikut adalah struktur tabel yang dibutuhkan untuk menjalankan aplikasi ini. Buat database dengan nama `crowdfunding` di server MySQL Anda, kemudian eksekusi perintah SQL berikut:

```sql
-- Buat database jika belum ada
CREATE DATABASE IF NOT EXISTS `crowdfunding`;
USE `crowdfunding`;

-- 1. Tabel User
CREATE TABLE IF NOT EXISTS `user` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `nama_lengkap` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `role` ENUM('donor', 'manager') NOT NULL DEFAULT 'donor'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Tabel Campaign
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

-- 3. Tabel Donasi
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

-- ==========================================
-- AKUN DEMO & DATA AWAL (SEEDERS)
-- ==========================================

-- Data Akun Demo (Password menggunakan teks biasa demi simplisitas)
INSERT INTO `user` (`username`, `password`, `nama_lengkap`, `email`, `role`) VALUES
('budi', 'budi123', 'Budi Santoso', 'budi@gmail.com', 'donor'),
('peduli', 'peduli123', 'Yayasan Peduli Sesama', 'info@pedulisesama.org', 'manager'),
('greenpeace', 'greenpeace123', 'Greenpeace Indonesia', 'info@greenpeace.id', 'manager');

-- Data Awal Kampanye
INSERT INTO `campaign` (`judul`, `sub_judul`, `kategori`, `lokasi`, `deskripsi`, `deskripsi_lengkap`, `target_dana`, `dana_terkumpul`, `penyelenggara`, `deadline`, `gambar`) VALUES
('Bantuan Gizi Balita Stunting', 'Bantu penuhi nutrisi bagi anak-anak kurang gizi di pelosok daerah agar tumbuh sehat.', 'Kesehatan', 'Nusa Tenggara Timur', 'Bantuan pemenuhan nutrisi dan susu formula bagi anak-anak yang terindikasi stunting di wilayah pedalaman NTT.', 'Masalah stunting di NTT masih menjadi perhatian serius. Melalui kampanye ini, kami bertekad membagikan paket bahan makanan pokok bergizi tinggi seperti telur, kacang-kacangan, susu formula khusus, dan vitamin untuk 100 balita selama 3 bulan penuh. Setiap bantuan Anda akan disalurkan langsung oleh relawan Yayasan Peduli Sesama di lapangan.', 50000000, 15000000, 'Yayasan Peduli Sesama', DATE_ADD(CURDATE(), INTERVAL 25 DAY), 'uploads/stunting_campaign.jpg'),
('Tanam 1000 Mangrove Pesisir', 'Aksi penanaman mangrove di pesisir pantai utara Jawa guna mencegah abrasi air laut.', 'Lingkungan', 'Semarang', 'Dukung restorasi ekosistem pantai utara Jawa dari ancaman abrasi laut yang kian parah.', 'Abrasi pantai di wilayah pesisir Semarang kian meresahkan warga sekitar. Penanaman mangrove adalah solusi alami yang efektif untuk memecah ombak dan mengembalikan ekosistem laut. Dana yang terkumpul akan digunakan untuk pengadaan bibit bakau, perawatan bibit, dan biaya operasional para relawan pecinta lingkungan di lapangan.', 25000000, 0, 'Greenpeace Indonesia', DATE_ADD(CURDATE(), INTERVAL 45 DAY), 'uploads/mangrove_campaign.jpg'),
('Beasiswa Sekolah Anak Nelayan', 'Bantu anak-anak nelayan prasejahtera mendapatkan pendidikan yang layak.', 'Pendidikan', 'Gunungkidul', 'Program beasiswa pendidikan bagi anak-anak nelayan pantai selatan untuk melanjutkan sekolah.', 'Banyak anak-anak di pesisir Gunungkidul terancam putus sekolah karena kendala biaya perlengkapan sekolah dan transportasi. Kampanye ini berfokus membiayai kebutuhan sekolah mereka selama setahun penuh, termasuk seragam, tas, buku pelajaran, dan sepeda kayuh untuk mempermudah akses ke sekolah.', 30000000, 0, 'Yayasan Peduli Sesama', DATE_ADD(CURDATE(), INTERVAL 6 DAY), 'uploads/beasiswa_campaign.jpg');
