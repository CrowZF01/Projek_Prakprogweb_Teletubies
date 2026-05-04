<?php
session_start();
require "koneksi/koneksi.php";

if (!isset($_SESSION["id"]) || $_SESSION["role"] != "manager") {
    header("location:login.php");
    exit();
}

$id_campaign = $_GET['id'];

// --- LOGIC UPDATE ---
if (isset($_POST['update'])) {
    $judul = $_POST['judul'];
    $kat = $_POST['kategori'];
    $lok = $_POST['lokasi'];
    $target = $_POST['target_dana'];
    $desc = $_POST['deskripsi'];
    $dl = $_POST['deadline'];

    $sql = "UPDATE campaign SET judul='$judul', kategori='$kat', lokasi='$lok', target_dana='$target', deskripsi='$desc', deadline='$dl' WHERE id='$id_campaign'";
    mysqli_query($koneksi, $sql);
    $msg = "Data berhasil diperbarui!";
}

// --- LOGIC DELETE ---
if (isset($_POST['delete'])) {
    mysqli_query($koneksi, "DELETE FROM campaign WHERE id='$id_campaign'");
    header("location:halPengelola.php");
    exit();
}

// Ambil data kampanye
$data = mysqli_query($koneksi, "SELECT * FROM campaign WHERE id = '$id_campaign'");
$cp = mysqli_fetch_assoc($data);

// Ambil bukti donasi dari tabel donasi join user
$donasi_query = mysqli_query($koneksi, "SELECT donasi.*, user.nama_lengkap 
    FROM donasi 
    JOIN user ON donasi.user_id = user.id 
    WHERE donasi.campaign_id = '$id_campaign'");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Kampanye</title>
    <link rel="stylesheet" href="styles/stylePengelola.css">
</head>

<body>
    <div class="container">
        <a href="halPengelola.php" class="btn-back">← Kembali</a>

        <section class="edit-section">
            <h1>Edit Detail Kampanye</h1>
            <?php if (isset($msg)) echo "<p class='alert'>$msg</p>"; ?>

            <form action="" method="post" class="form-edit">
                <div class="form-group">
                    <label>Judul Kampanye</label>
                    <input type="text" name="judul" value="<?php echo $cp['judul']; ?>" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Kategori</label>
                        <input type="text" name="kategori" value="<?php echo $cp['kategori']; ?>">
                    </div>
                    <div class="form-group">
                        <label>Lokasi</label>
                        <input type="text" name="lokasi" value="<?php echo $cp['lokasi']; ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Target Dana (Rp)</label>
                        <input type="number" name="target_dana" value="<?php echo $cp['target_dana']; ?>">
                    </div>
                    <div class="form-group">
                        <label>Deadline</label>
                        <input type="date" name="deadline" value="<?php echo $cp['deadline']; ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" rows="5"><?php echo $cp['deskripsi']; ?></textarea>
                </div>

                <div class="btn-group">
                    <button type="submit" name="update" class="btn-save">Simpan Perubahan</button>
                    <button type="submit" name="delete" class="btn-delete" onclick="return confirm('Hapus kampanye ini?')">Hapus Kampanye</button>
                </div>
            </form>
        </section>

        <hr class="separator">

        <section class="donation-evidence">
            <h2>Bukti Donasi Masuk</h2>
            <div class="evidence-grid">
                <?php if (mysqli_num_rows($donasi_query) > 0): ?>
                    <?php while ($don = mysqli_fetch_assoc($donasi_query)): ?>
                        <div class="evidence-card">
                            <div class="evidence-img">
                                <!-- Link ke file bukti transfer -->
                                <img src="img/<?php echo $don['bukti_transfer']; ?>" alt="Bukti Transfer">
                            </div>
                            <div class="evidence-info">
                                <h4><?php echo $don['nama_lengkap']; ?></h4>
                                <p class="amount">Rp <?php echo number_format($don['nominal_donasi']); ?></p>
                                <p class="date"><?php echo $don['tgl_donasi']; ?></p>
                                <span class="status <?php echo strtolower($don['status']); ?>"><?php echo $don['status']; ?></span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>Belum ada donasi masuk untuk kampanye ini.</p>
                <?php endif; ?>
            </div>
        </section>
    </div>
</body>

</html>