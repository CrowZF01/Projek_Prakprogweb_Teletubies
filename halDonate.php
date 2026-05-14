<?php
session_start();
require "koneksi/koneksi.php";

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("location:halUtama.php");
    exit();
}

$id = (int) $_GET['id'];
$query = mysqli_query(
    $koneksi,
    "SELECT * FROM detail_campaign WHERE campaign_id = $id"
);

if (mysqli_num_rows($query) == 0) {
    echo "Detail kampanye tidak ditemukan";
    exit();
}

$data = mysqli_fetch_assoc($query);

if (!isset($_SESSION["id"])) {
    header("location:halLogin.php");
    exit();
}

if (isset($_SESSION["nama_user"])) {
    $nama = $_SESSION["nama_user"];
} else {
    $nama = "user";
}

$user_id = $_SESSION['id'];
$query_user = mysqli_query($koneksi, "SELECT nama_lengkap, email FROM user WHERE id = $user_id");
$user_data = mysqli_fetch_assoc($query_user);
$query_camp = mysqli_query($koneksi, "SELECT * FROM campaign WHERE id = $id");
$camp_data = mysqli_fetch_assoc($query_camp);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nominal = $_POST['amount'];
    $metode = $_POST['method'];
    $pesan = $_POST['message'];

    $nama_file = $_FILES['proof']['name'];
    $temp_file = $_FILES['proof']['tmp_name'];
    $folder_upload = "bukti_transfer/";

    if (move_uploaded_file($temp_file, $folder_upload . $nama_file)) {
        $masukkan = mysqli_query($koneksi, "INSERT INTO donasi (user_id, campaign_id, nominal_donasi, metode_pembayaran, pesan_dukungan, bukti_transfer, status) VALUES ('$user_id', '$id', '$nominal', '$metode', '$pesan', '$nama_file', 'PENDING')");
        if ($masukkan) {
            echo "<script>alert('Donasi berhasil, menunggu verifikasi admin'); window.location='halUtama.php';</script>";
        } else {
            echo "gagal menyimpan data";
        }
    } else {
        echo "gagal mengunggah gambar";
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donasi - Neo-Prok</title>
    <link rel="stylesheet" href="styles/styleHalDonate.css">
</head>

<body>

    <header>
        <div class="logo">
            <img src="img/T.png" alt="Logo"> <!-- Pastikan path logo benar -->
        </div>
        <div class="links">
            <div class="datang">Mari Berbagi!</div>
            <nav>
                <a href="halUtama.php">Home</a>
                <a href="halLogin.php">Logout</a>
            </nav>
        </div>
    </header>

    <main>
        <div class="donate-card">
            <h1>Donasi <span>Sekarang</span></h1>

            <form action="proses_donasi.php" method="POST">
                <div class="form-group">
                    <label for="nama">NAMA LENGKAP</label>
                    <input type="text" name="nama" id="nama" placeholder="Masukkan nama Anda" required>
                </div>

                <div class="form-group">
                    <label for="jumlah">NOMINAL DONASI (RP)</label>
                    <input type="number" name="jumlah" id="jumlah" placeholder="Contoh: 50000" required>
                </div>

                <div class="form-group">
                    <label for="pesan">PESAN BAIK</label>
                    <textarea name="pesan" id="pesan" rows="3" placeholder="Tulis doa atau pesan singkat..."></textarea>
                </div>

                <div class="btn-container">
                    <button type="submit" class="btn-primary">Kirim Donasi</button>
                    <a href="halUtama.php" class="btn-secondary"> Kembali ke Beranda</a>
                </div>
            </form>
        </div>
    </main>

</body>

</html>