<?php
session_start();
require "koneksi/koneksi.php";

// Proteksi Halaman: Pastikan yang login adalah manager
if (!isset($_SESSION["role"]) || $_SESSION["role"] != "manager") {
    header("location:halLogin.php");
    exit();
}

$nama = isset($_SESSION["nama_user"]) ? $_SESSION["nama_user"] : "Pengelola";

// 1. Ambil semua list kampanye untuk Dropdown (Testing: Bisa melihat semuanya)
$query_list = mysqli_query($koneksi, "SELECT campaign_id, judul_detail FROM detail_campaign");

// 2. Ambil detail kampanye yang sedang dipilih via Dropdown
$id_dipilih = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$data = null;
$progress = 0;

if ($id_dipilih > 0) {
    $query_detail = mysqli_query($koneksi, "SELECT * FROM detail_campaign WHERE campaign_id = $id_dipilih");
    $data = mysqli_fetch_assoc($query_detail);
    
    if ($data) {
        if ($data['target_dana'] > 0) {
            $progress = ($data['dana_terkumpul'] / $data['target_dana']) * 100;
            if ($progress > 100) $progress = 100;
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Panel Pengelola - Crowdfunding</title>
    <!-- Menggunakan CSS Detail asli kamu + CSS Tambahan Pengelola -->
    <link rel="stylesheet" href="styles/styleHalDetail.css" />
    <link rel="stylesheet" href="styles/stylePengelola.css" />
</head>
<body style="background-color: #fffdf4;">
    <header>
        <div class="logo">
            <a href="halUtama.php">
                <img src="img/T.png" alt="Logo" />
            </a>
        </div>
        <?php echo "<p class='datang'>Panel Pengelola: $nama</p>"; ?>
        <nav class="links">
            <a href="halUtama.php">Home</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <main>
        <section class="detail">
            <!-- SELECTOR KAMPANYE -->
            <div class="selector-container">
                <label for="campaign_select">Pilih Kampanye Anda:</label>
                <select id="campaign_select" onchange="window.location.href='halPengelola.php?id=' + this.value">
                    <option value="">-- Pilih Kampanye untuk Dikelola --</option>
                    <?php while($list = mysqli_fetch_assoc($query_list)): ?>
                        <option value="<?php echo $list['campaign_id']; ?>" <?php echo ($id_dipilih == $list['campaign_id']) ? 'selected' : ''; ?>>
                            <?php echo $list['judul_detail']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <?php if ($data): ?>
            <!-- FORM EDIT (CRUD MODE) -->
            <form action="proses_update_kampanye.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="campaign_id" value="<?php echo $data['campaign_id']; ?>">
                
                <div class="campaign-title">
                    <h1>
                        <input type="text" name="judul_detail" class="edit-judul" value="<?php echo $data['judul_detail']; ?>">
                    </h1>
                    <textarea name="sub_judul" class="edit-subtitle"><?php echo $data['sub_judul']; ?></textarea>
                </div>

                <div class="detail-content">
                    <div class="poster">
                        <div class="poster-edit-container">
                            <img src="img/<?php echo $data['gambar_detail']; ?>" alt="detail kampanye" />
                            <div class="file-upload">
                                <span>Ganti Gambar: </span>
                                <input type="file" name="gambar_detail">
                            </div>
                        </div>

                        <div class="tags edit-mode-tags">
                            <span class="tag">🔖 <input type="text" name="kategori" value="<?php echo $data['kategori']; ?>"></span>
                            <span class="tag">📍 <input type="text" name="lokasi" value="<?php echo $data['lokasi']; ?>"></span>
                        </div>

                        <div class="desc-edit-area">
                            <label>Deskripsi Lengkap:</label>
                            <textarea name="deskripsi_lengkap"><?php echo $data['deskripsi_lengkap']; ?></textarea>
                        </div>
                    </div>

                    <div class="info">
                        <div class="progress-container">
                            <div class="progress-bar-fill" style="width: <?php echo $progress; ?>%"></div>
                        </div>

                        <label class="label-manage">Dana Terkumpul (Otomatis)</label>
                        <h2 class="dana-terkumpul">
                            Rp<?php echo number_format($data['dana_terkumpul']); ?>
                        </h2>

                        <label class="label-manage">Target Dana (Rp)</label>
                        <input type="number" name="target_dana" class="edit-number" value="<?php echo $data['target_dana']; ?>">

                        <div class="stats-box manage">
                            <label>Penyelenggara:</label>
                            <input type="text" name="penyelenggara" value="<?php echo $data['penyelenggara']; ?>">
                            
                            <label>Deadline:</label>
                            <input type="date" name="deadline" value="<?php echo $data['deadline']; ?>">
                        </div>

                        <button type="submit" name="update" class="btn">Simpan Perubahan Data</button>
                        <button type="submit" name="delete" class="btn" style="background-color: #c0392b; margin-top: 10px;" onclick="return confirm('Hapus kampanye ini?')">Hapus Kampanye</button>
                    </div>
                </div>
            </form>

            <!-- BUKTI TRANSFER (HORIZONTAL SCROLL) -->
            <div class="bukti-section">
                <h3>Bukti Donatur Masuk</h3>
                <div class="scroll-wrapper">
                    <?php
                    $q_donasi = mysqli_query($koneksi, "SELECT * FROM donasi WHERE campaign_id = $id_dipilih ORDER BY tgl_donasi DESC");
                    if (mysqli_num_rows($q_donasi) > 0):
                        while($donasi = mysqli_fetch_assoc($q_donasi)):
                    ?>
                        <div class="bukti-card">
                            <img src="img/<?php echo $donasi['bukti_transfer']; ?>" alt="Bukti Transfer">
                            <div class="bukti-info">
                                <p><strong>Rp<?php echo number_format($donasi['nominal_donasi']); ?></strong></p>
                                <p class="status-donasi"><?php echo $donasi['status']; ?></p>
                                <p class="pesan">"<?php echo $donasi['pesan_dukungan']; ?>"</p>
                                <small><?php echo date("d M Y", strtotime($donasi['tgl_donasi'])); ?></small>
                            </div>
                        </div>
                    <?php 
                        endwhile;
                    else:
                        echo "<p class='no-data'>Belum ada donasi yang masuk.</p>";
                    endif;
                    ?>
                </div>
            </div>

            <?php else: ?>
                <div class="welcome-manager">
                    <p>Silahkan pilih kampanye yang ingin Anda kelola melalui menu dropdown di atas.</p>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <h2>Panel Kendali Pengelola Kampanye - Crowdfunding Platform</h2>
    </footer>
</body>
</html>