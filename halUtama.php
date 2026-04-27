<?php
session_start();

require "koneksi/koneksi.php";

$query = mysqli_query(
  $koneksi,
  "SELECT * FROM campaign 
     WHERE deadline >= CURDATE()
     ORDER BY deadline ASC"
);

if (!isset($_SESSION["id"])) {
  header("location:halLogin.php");
  exit();
}

if (isset($_SESSION["nama_user"])) {
  $nama = $_SESSION["nama_user"];
} else {
  $nama = "user";
}
?>


<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Beranda - Crowdfunding</title>
  <link rel="stylesheet" href="styles/styleHalUtama.css" />
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
    <div class="intro">
      <h1>Langkah Kecil, <span> Dampak Besar</span></h1>
      <p class="subhead">
        Temukan berbagai kampanye sosial dan dukung para penggalang dana untuk
        menciptakan perubahan positif di sekitar kita.
      </p>
    </div>
    <section class="search-bar">
      <form>
        <div class="search-container">
          <select class="category-select">
            <option value="">Semua Kategori</option>
            <option value="education">Pendidikan</option>
            <option value="health">Kesehatan</option>
            <option value="environment">Lingkungan</option>
          </select>

          <div class="divider"></div>
          <input type="text" placeholder="Cari judul kampanye" />
        </div>
      </form>
    </section>

    <hr class="garis" />

    <section class="campaigns">
      <!-- Card 1 -->
      <!-- <article class="card">
          <div class="card-image">
            <img src="img/gambar-anak-sekolah.jpg" alt="Anak Sekolah" />
          </div>
          <div class="card-content">
            <div class="card-tags">Pendidikan • Sosial</div>
            <a href="halDetail.php">
              <h2 class="card-title">
                Bantu Anak Sekolah di Pelosok Indonesia
              </h2>
            </a>
            <div class="card-meta">
              <span class="author">👤 Budi Doremi</span>
              <span class="deadline">📅 30 Hari lagi</span>
            </div>
            <p class="card-description">
              Membantu anak-anak di pedalaman untuk mendapatkan akses alat tulis
              dan seragam yang layak.
            </p>
            <div class="card-footer">
              <div class="stat">
                <span class="label">Terkumpul</span>
                <span class="value">Rp1.200.000</span>
              </div>
              <div class="stat">
                <span class="label">Target</span>
                <span class="value text-right">Rp5.000.000</span>
              </div>
            </div>
          </div>
        </article> -->
        
      <?php while ($row = mysqli_fetch_assoc($query)) { ?>
        <article class="card">
          <div class="card-image">
            <img src="img/<?php echo $row['gambar']; ?>" alt="">
          </div>

          <div class="card-content">
            <div class="card-tags">
              <?php echo $row['kategori']; ?>
            </div>

            <a href="halDetail.php?id=<?php echo $row['id']; ?>">
              <h2 class="card-title">
                <?php echo $row['judul']; ?>
              </h2>
            </a>

            <div class="card-meta">
              <span>👤 <?php echo $row['penyelenggara']; ?></span>
              <span>📅 <?php echo $row['deadline']; ?></span>
            </div>

            <p class="card-description">
              <?php echo substr($row['deskripsi'], 0, 100); ?>...
            </p>

            <div class="card-footer">
              <div class="stat">
                <span class="label">Terkumpul</span>
                <span class="value">Rp<?php echo number_format($row['dana_terkumpul']); ?></span>
              </div>
              <div class="stat">
                <span class="label">Target</span>
                <span class="value">Rp<?php echo number_format($row['target_dana']); ?></span>
              </div>
            </div>
          </div>
        </article>
      <?php } 
      ?>

      <!-- Card 2 -->
    </section>
  </main>

  <footer>
    <h2>Kirimkan dukunganmu segera. Setiap rupiah yang kamu berikan itu sangat berarti bagi mereka :) </h2>
  </footer>
</body>

</html>