<?php
session_start();
require "koneksi/koneksi.php";

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

// if (!isset($_SESSION["id"])) {
//   header("location:halLogin.php");
//   exit();
// }

if (isset($_SESSION["nama_user"])) {
  $nama = $_SESSION["nama_user"];
} else {
  $nama = "user";
}

$progress = 0;
if ($data['target_dana'] > 0) {
  $progress = ($data['dana_terkumpul'] / $data['target_dana']) * 100;
}
if ($progress > 100) {
  $progress = 100;
}

?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Detail Kampanye</title>
  <link rel="stylesheet" href="styles/styleHalDetail.css" />
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
    <section class="detail">
      <a href="halUtama.php" class="back">&larr; Kembali ke Beranda</a>
      <div class="campaign-title">
        <h1>
          <span class="pertama"><?php echo $data['judul_detail']; ?></span>
        </h1>
        <p class="subtitle">
          <?php echo $data['sub_judul']; ?>
        </p>
      </div>
      <div class="detail-content">
        <div class="poster">
          <img src="img/<?php echo $data['gambar_detail']; ?>" alt="detail kampanye" />
          <div class="tags">
            <span class="tag">🔖 <?php echo $data['kategori']; ?></span>
            <span class="tag">📍 <?php echo $data['lokasi']; ?></span>
          </div>

          <p style="margin-top: 20px; line-height: 1.6; color: #555">
            <?php echo nl2br($data['deskripsi_lengkap']); ?>
          </p>
        </div>
        <div class="info">
          <div class="progress-container">
            <div class="progress-bar-fill" style="width: <?php echo $progress; ?>%"></div>
          </div>

          <h2 class="dana-terkumpul">
            Rp<?php echo number_format($data['dana_terkumpul']); ?>
          </h2>

          <p class="dana-target">
            terkumpul dari target Rp<?php echo number_format($data['target_dana']); ?>
          </p>

          <div class="stats-box">
            <p><strong>Penyelenggara:</strong><br /><?php echo $data['penyelenggara']; ?></p>
            <p><strong>Deadline:</strong><br /><?php echo date("d M Y", strtotime($data['deadline'])); ?></p>
          </div>
          <?php  
          if(isset($_SESSION["id"]) && $_SESSION["role"] != "guest"):?>
          <a href="halDonate.php?id=<?php echo $id; ?>" class="btn">
            Donasi Sekarang
          </a>
          <?php else: ?>
            <a href="halLogin.php" class="btn" onclick="return confirm('Silahkan login terlebih dahulu untuk memberikan donasi')">Donasi Sekarang</a>
            <?php endif; ?>
          <p style="font-size: 12px; color: #888; margin-top: 15px">
            Proyek ini hanya akan didanai jika mencapai target atau telah
            melewati batas waktu yang ditentukan.
          </p>
        </div>
      </div>
    </section>
  </main>

  <footer>
    <h2>
      Kirimkan dukunganmu segera. Setiap rupiah yang kamu berikan itu sangat
      berarti bagi mereka :
    </h2>
  </footer>
</body>

</html>