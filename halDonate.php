<?php
session_start();
require "koneksi/koneksi.php";

if(!isset($_GET['id']) || empty($_GET['id'])){
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

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $nominal = $_POST['amount'];
    $metode = $_POST['method'];
    $pesan = $_POST['message'];
    
    $nama_file = $_FILES['proof']['name'];
    $temp_file = $_FILES['proof']['tmp_name'];
    $folder_upload = "bukti_transfer/";

    if(move_uploaded_file($temp_file, $folder_upload . $nama_file)){
        $masukkan = mysqli_query($koneksi, "INSERT INTO donasi (user_id, campaign_id, nominal_donasi, metode_pembayaran, pesan_dukungan, bukti_transfer, status) VALUES ('$user_id', '$id', '$nominal', '$metode', '$pesan', '$nama_file', 'PENDING')");
        if($masukkan){
            echo "<script>alert('Donasi berhasil, menunggu verifikasi admin'); window.location='halUtama.php';</script>";
        } else {
            echo "gagal menyimpan data";
        }
    } else{
        echo "gagal mengunggah gambar";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donasi</title>
    <link rel="stylesheet" href="styles/styleHalDonate.css">
</head>

<body>

    <header>
        <div class="logo">
            <a href="halUtama.php">
                <img src="img/T.png" alt="Klik gambar ini" />
            </a>
        </div>
        <?php
        echo "<p class = 'datang'>Selamat Datang $nama, Selamat Berdonasi</p>";
        ?>
        <nav class="links">
            <a href="halUtama.php" class="active">Home</a>
            <?php if (isset($_SESSION["role"]) && $_SESSION["role"] == "guest"): ?>
                <a href="halLogin.php">Login</a>
            <?php else: ?>
                <a href="logout.php">Logout</a>
            <?php endif; ?>
        </nav>
    </header>

    <main>
        <a href="halDetail.php?id=<?php echo $id; ?>" class="back">&larr; Kembali ke Detail</a>
        <section class="donate">
            <h1>Formulir Donasi</h1>
            <p class="campaign-summary">Anda akan mendonasikan untuk <strong><?php echo $data['judul_detail'] ?></strong> oleh <?php echo $data['penyelenggara'] ?></p>
            <form action="" method="POST" enctype="multipart/form-data">
                <label for="nama">Nama Lengkap</label>
                <input type="text" id="nama" name="nama" value="<?php echo $user_data['nama_lengkap']; ?>" required>
                <br>

                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo $user_data['email']; ?>" required>
                <br>

                <label for="amount">Nominal Donasi (Min Rp 10.000)</label>
                <input type="number" id="amount" name="amount" min="10000" step="10000" required>
                <br>

                <label for="method">Metode Pembayaran</label>
                <select id="method" name="method">
                    <option value="bank">Transfer Bank</option>
                    <option value="e-wallet">E-Wallet</option>
                </select>
                <br>

                <label for="message">Pesan Dukungan (opsional)</label>
                <textarea id="message" name="message"></textarea>
                <br>

                <label for="proof">Bukti Transfer (PDF/JPG/PNG)</label>
                <input type="file" id="proof" name="proof" accept=".pdf,.jpg,.png">
                <br>

                <button class="btn" type="submit">Kirim Donasi</button>
            </form>
        </section>
    </main>

    <footer>
        <h2>Kirimkan dukunganmu segera. Setiap rupiah yang kamu berikan itu sangat berarti bagi mereka :) </h2>
    </footer>

</body>

</html>