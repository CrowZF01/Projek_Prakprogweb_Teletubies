<?php
session_start();
require "koneksi/koneksi.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] != "manager") {
    header("location:halLogin.php");
    exit();
}

if (isset($_SESSION["nama_user"])) {
    $nama = $_SESSION["nama_user"];
} else {
    $nama = "Pengelola";
}

$nama_pengelola = mysqli_real_escape_string($koneksi, $nama);

if (isset($_GET['id'])) {
    $id_campaign = (int)$_GET['id'];
} else {
    $_SESSION['msg_error'] = "Pilih kampanye terlebih dahulu.";
    header("Location: halPengelola.php");
    exit();
}

// Check ownership first
$owner_check = mysqli_query($koneksi, "SELECT * FROM campaign WHERE id = $id_campaign AND penyelenggara = '$nama_pengelola'");
$cp = mysqli_fetch_assoc($owner_check);

if (!$cp) {
    $_SESSION['msg_error'] = "Kampanye tidak ditemukan atau Anda tidak memiliki akses.";
    header("Location: halPengelola.php");
    exit();
}

// --- PROSES ACTION FORM ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. UPDATE KAMPANYE
    if (isset($_POST['update'])) {
        $judul = mysqli_real_escape_string($koneksi, $_POST['judul']);
        $sub_judul = mysqli_real_escape_string($koneksi, $_POST['sub_judul']);
        $kategori = mysqli_real_escape_string($koneksi, $_POST['kategori']);
        $lokasi = mysqli_real_escape_string($koneksi, $_POST['lokasi']);
        $deskripsi_lengkap = mysqli_real_escape_string($koneksi, $_POST['deskripsi_lengkap']);
        $target_dana = (int)$_POST['target_dana'];
        $penyelenggara_baru = mysqli_real_escape_string($koneksi, $_POST['penyelenggara']);
        $deadline = mysqli_real_escape_string($koneksi, $_POST['deadline']);

        $update_gambar_sql = "";
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $nama_file = $_FILES['gambar']['name'];
            $temp_file = $_FILES['gambar']['tmp_name'];
            $ekstensi = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($ekstensi, $allowed)) {
                $nama_file_baru = time() . "_" . $nama_file;
                $folder_upload = "uploads/";

                if (!is_dir($folder_upload)) {
                    mkdir($folder_upload, 0777, true);
                }

                if (move_uploaded_file($temp_file, $folder_upload . $nama_file_baru)) {
                    $path_database = $folder_upload . $nama_file_baru;
                    $update_gambar_sql = ", gambar='$path_database'";
                } else {
                    $_SESSION['msg_error'] = "Gagal memindahkan file gambar yang diunggah.";
                }
            } else {
                $_SESSION['msg_error'] = "Format gambar tidak didukung! Harus berupa JPG, JPEG, PNG, atau WEBP.";
            }
        }

        // Sync 'deskripsi' field as truncated version of 'deskripsi_lengkap' (max 150 chars)
        $deskripsi_pendek = mysqli_real_escape_string($koneksi, substr(strip_tags($_POST['deskripsi_lengkap']), 0, 150));

        $sql_update = "UPDATE campaign SET 
                        judul='$judul', 
                        sub_judul='$sub_judul', 
                        kategori='$kategori', 
                        lokasi='$lokasi', 
                        deskripsi='$deskripsi_pendek',
                        deskripsi_lengkap='$deskripsi_lengkap', 
                        target_dana='$target_dana', 
                        penyelenggara='$penyelenggara_baru', 
                        deadline='$deadline'
                        $update_gambar_sql 
                        WHERE id='$id_campaign'";

        if (mysqli_query($koneksi, $sql_update)) {
            if ($nama != $penyelenggara_baru) {
                $_SESSION["nama_user"] = $penyelenggara_baru;
            }
            $_SESSION['msg_success'] = "Data kampanye berhasil diperbarui!";
            header("Location: editKampanye.php?id=" . $id_campaign);
            exit();
        } else {
            $_SESSION['msg_error'] = "Gagal memperbarui data: " . mysqli_error($koneksi);
        }
    }

    // 2. HAPUS KAMPANYE
    if (isset($_POST['delete'])) {
        $dana = (int)$cp['dana_terkumpul'];
        if ($dana >= 10000) {
            $_SESSION['msg_error'] = "Gagal menghapus kampanye! Kampanye yang memiliki dana terkumpul >= Rp10.000 tidak dapat dihapus.";
            header("Location: editKampanye.php?id=" . $id_campaign);
            exit();
        } else {
            $sql_delete = "DELETE FROM campaign WHERE id = $id_campaign";
            if (mysqli_query($koneksi, $sql_delete)) {
                $_SESSION['msg_success'] = "Kampanye berhasil dihapus.";
                header("Location: halPengelola.php");
                exit();
            } else {
                $_SESSION['msg_error'] = "Gagal menghapus kampanye dari database: " . mysqli_error($koneksi);
                header("Location: editKampanye.php?id=" . $id_campaign);
                exit();
            }
        }
    }
}

// Re-fetch data if update succeeded or didn't run redirect yet
$owner_check = mysqli_query($koneksi, "SELECT * FROM campaign WHERE id = $id_campaign AND penyelenggara = '$nama_pengelola'");
$cp = mysqli_fetch_assoc($owner_check);

$progress = 0;
if ($cp['target_dana'] > 0) {
    $progress = ($cp['dana_terkumpul'] / $cp['target_dana']) * 100;
    if ($progress > 100) {
        $progress = 100;
    }
}

// Hitung total donasi pending
$query_pending = mysqli_query($koneksi, "
    SELECT SUM(nominal_donasi) AS total_pending 
    FROM donasi 
    WHERE campaign_id = $id_campaign 
    AND status = 'PENDING'
");
$row_pending = mysqli_fetch_assoc($query_pending);
$dana_pending = $row_pending['total_pending'] ? (float)$row_pending['total_pending'] : 0.0;
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Edit Kampanye - Panel Pengelola</title>
    <link rel="stylesheet" href="styles/styleHalDetail.css" />
    <link rel="stylesheet" href="styles/stylePengelola.css" />
</head>

<body style="background-color: #fffdf4;">
    <header>
        <div class="logo">
            <a href="halUtama.php">
                <img src="logo/T.png" alt="Logo" />
            </a>
        </div>
        <?php echo "<p class='datang'>👋 Edit Kampanye: " . htmlspecialchars($cp['judul']) . "</p>"; ?>
        <nav class="links">
            <a href="logout.php">👋 Logout</a>
        </nav>
    </header>

    <main>
        <section class="detail">
            <a href="halPengelola.php?id=<?php echo $id_campaign; ?>" class="btn-back">← Kembali ke Panel</a>
            <?php if (isset($_SESSION['msg_success'])): ?>
                <div class="alert alert-success">
                    <?php
                    echo $_SESSION['msg_success'];
                    unset($_SESSION['msg_success']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['msg_error'])): ?>
                <div class="alert alert-error">
                    <?php
                    echo $_SESSION['msg_error'];
                    unset($_SESSION['msg_error']);
                    ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" enctype="multipart/form-data">
                <div class="campaign-title">
                    <h1>
                        <input type="text" name="judul" class="edit-judul" value="<?php echo htmlspecialchars($cp['judul']); ?>" required>
                    </h1>
                    <textarea name="sub_judul" class="edit-subtitle" required><?php echo htmlspecialchars($cp['sub_judul']); ?></textarea>
                </div>

                <div class="detail-content">
                    <div class="poster">
                        <div class="poster-edit-container">
                            <img src="<?php echo $cp['gambar']; ?>" alt="detail kampanye" />
                            <div class="file-upload">
                                <span>Ganti Gambar: </span>
                                <input type="file" name="gambar">
                            </div>
                        </div>

                        <div class="tags edit-mode-tags">
                            <span class="tag">🔖 <input type="text" name="kategori" value="<?php echo htmlspecialchars($cp['kategori']); ?>" required></span>
                            <span class="tag">📍 <input type="text" name="lokasi" value="<?php echo htmlspecialchars($cp['lokasi']); ?>" required></span>
                        </div>

                        <div class="desc-edit-area">
                            <label>Deskripsi Lengkap:</label>
                            <textarea name="deskripsi_lengkap" required><?php echo htmlspecialchars($cp['deskripsi_lengkap']); ?></textarea>
                        </div>
                    </div>

                    <div class="info">
                        <div class="progress-container">
                            <div class="progress-bar-fill" style="width: <?php echo $progress; ?>%"></div>
                        </div>

                        <label class="label-manage">Dana Terkumpul (Berhasil)</label>
                        <h2 class="dana-terkumpul">
                            Rp<?php echo number_format($cp['dana_terkumpul']); ?>
                        </h2>

                        <label class="label-manage">Dana Pending (Belum Diverifikasi)</label>
                        <h2 class="dana-pending">
                            Rp<?php echo number_format($dana_pending); ?>
                        </h2>

                        <label class="label-manage">Target Dana (Rp)</label>
                        <input type="number" name="target_dana" class="edit-number" value="<?php echo $cp['target_dana']; ?>" required>

                        <div class="stats-box manage">
                            <label>Penyelenggara:</label>
                            <input type="text" name="penyelenggara" value="<?php echo htmlspecialchars($cp['penyelenggara']); ?>" required>

                            <label>Deadline:</label>
                            <input type="date" name="deadline" value="<?php echo $cp['deadline']; ?>" required>
                        </div>

                        <button type="submit" name="update" class="btn">Simpan Perubahan Data</button>
                        <button type="submit" name="delete" class="btn" style="background-color: #c0392b; margin-top: 10px;" onclick="return confirm('Hapus kampanye ini?')">Hapus Kampanye</button>
                    </div>
                </div>
            </form>
        </section>
    </main>

    <footer>
        <h2>Panel Kendali Pengelola Kampanye - Crowdfunding Platform</h2>
    </footer>
</body>

</html>