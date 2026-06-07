<?php
session_start();
require "koneksi/koneksi.php";

// 1. VALIDASI LOGIN
if (!isset($_SESSION["id"])) {
    header("location:halLogin.php");
    exit();
}

// 2. VALIDASI ID CAMPAIGN
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("location:halUtama.php");
    exit();
}

$id_campaign = (int)$_GET['id'];
$user_id = $_SESSION['id'];

// 3. AMBIL DATA KAMPANYE (Ringkasan)
$query_camp = mysqli_query($koneksi, "SELECT * FROM campaign WHERE id = $id_campaign");
$camp_data = mysqli_fetch_assoc($query_camp);

if (!$camp_data) {
    echo "Kampanye tidak ditemukan";
    exit();
}

// 4. AMBIL DATA USER (Nama & Email)
$query_user = mysqli_query($koneksi, "SELECT nama_lengkap, email FROM user WHERE id = $user_id");
$user_data = mysqli_fetch_assoc($query_user);

// 5. PROSES SUBMIT DONASI
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nominal = $_POST['amount'];
    $metode = $_POST['method'];
    $pesan = $_POST['message'];

    // Validasi Minimal 10.000
    if ($nominal < 10000) {
        echo "<script>alert('Minimal donasi adalah Rp 10.000');</script>";
    } else {
        $nama_file = $_FILES['proof']['name'];
        $temp_file = $_FILES['proof']['tmp_name'];
        $ekstensi = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];

        if (in_array($ekstensi, $allowed)) {
            $nama_file_baru = time() . "_" . $nama_file; // Hindari nama file duplikat
            $folder_upload = "bukti_transfer/";

            if (move_uploaded_file($temp_file, $folder_upload . $nama_file_baru)) {
                $sql = "INSERT INTO donasi (user_id, campaign_id, nominal_donasi, metode_pembayaran, pesan_dukungan, bukti_transfer, status) 
                        VALUES ('$user_id', '$id_campaign', '$nominal', '$metode', '$pesan', '$nama_file_baru', 'PENDING')";

                if (mysqli_query($koneksi, $sql)) {
                    echo "<script>alert('Donasi berhasil terkirim! Status: PENDING (Menunggu Verifikasi)'); window.location='halUtama.php';</script>";
                } else {
                    echo "Error: " . mysqli_error($koneksi);
                }
            }
        } else {
            echo "<script>alert('Format file harus JPG, PNG, atau PDF!');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donasi - Teletubies</title>
    <link rel="stylesheet" href="styles/styleHalDonate.css">
</head>

<body>

    <header>
        <div class="logo"><img src="logo/T.png" alt="Logo"></div>
        <div class="links">
            <nav>
                <a href="halUtama.php">Home</a>
                <a href="logout.php">Logout</a>
            </nav>
        </div>
    </header>

    <main>
        <div class="donate-card">
            <div class="camp-summary">
                <h2>Donasi Sekarang</h2>
                <!-- Ringkasan Kampanye -->
                <div class="campaign-detail-summary">
                    <div class="camp-info">
                        <span class="camp-label">Kampanye Pilihan Anda</span>
                        <h3 class="camp-title"><?php echo htmlspecialchars($camp_data['judul']); ?></h3>
                    </div>
                    <div class="camp-stats">
                        <div class="stat-item">
                            <span class="stat-label">Target Dana</span>
                            <span class="stat-value">Rp <?php echo number_format($camp_data['target_dana'], 0, ',', '.'); ?></span>
                        </div>
                        <div class="stat-divider"></div>
                        <div class="stat-item">
                            <span class="stat-label">Dana Terkumpul</span>
                            <span class="stat-value">Rp <?php echo number_format($camp_data['dana_terkumpul'], 0, ',', '.'); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <form action="" method="POST" enctype="multipart/form-data">
                <!-- KOLOM KIRI -->
                <div class="column-left">
                    <div class="form-group">
                        <label>NAMA LENGKAP</label>
                        <input type="text" value="<?php echo $user_data['nama_lengkap']; ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label>EMAIL</label>
                        <input type="text" value="<?php echo $user_data['email']; ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label>PESAN DUKUNGAN (OPSIONAL)</label>
                        <textarea name="message" placeholder="Tulis doa atau dukungan..."></textarea>
                    </div>
                </div>

                <!-- KOLOM KANAN -->
                <div class="column-right">
                    <div class="form-group">
                        <label>NOMINAL DONASI (MIN RP 10.000)</label>
                        <input type="number" name="amount" min="10000" placeholder="Contoh: 50000" required>
                    </div>
                    <div class="form-group">
                        <label>METODE PEMBAYARAN</label>
                        <select name="method" required>
                            <option value="Gopay">Gopay</option>
                            <option value="Transfer Bank">Transfer Bank</option>
                            <option value="OVO">OVO</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>BUKTI TRANSFER (JPG/PNG/PDF)</label>
                        <input type="file" name="proof" required>
                    </div>
                </div>

                <!-- TOMBOL -->
                <div class="btn-container">
                    <button type="submit" class="btn-primary">Kirim Donasi Sekarang</button>
                    <a href="halDetail.php?id=<?php echo $id_campaign; ?>" class="btn-secondary">Kembali</a>
                </div>
            </form>
        </div>
    </main>

</body>

</html>