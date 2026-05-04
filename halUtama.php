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
    echo "<p class='datang'>👋 Halo, $nama!</p>";
    ?>

    <nav class="links">
      <a href="halUtama.php" class="active">🏠 Home</a>
      <?php if (isset($_SESSION["role"]) && $_SESSION["role"] == "guest"): ?>
        <a href="halLogin.php">🔑 Login</a>
      <?php else: ?>
        <a href="logout.php">👋 Logout</a>
      <?php endif; ?>
    </nav>
  </header>

  <main>
    <div class="intro">
      <h1>Langkah Kecil, <span>Dampak Besar</span></h1>
      <p class="subhead">
        Temukan berbagai kampanye sosial dan dukung para penggalang dana untuk
        menciptakan perubahan positif di sekitar kita.
      </p>
    </div>

    <section class="search-bar">
      <form>
        <div class="search-container">
          <select class="category-select">
            <option value="">✦ Semua Kategori</option>
            <option value="education">📚 Pendidikan</option>
            <option value="health">❤️ Kesehatan</option>
            <option value="environment">🌿 Lingkungan</option>
          </select>
          <div class="divider"></div>
          <input type="text" placeholder="🔍  Cari judul kampanye..." />
        </div>
      </form>
    </section>

    <hr class="garis" />

    <div class="section-label">
      <h2>Kampanye Aktif</h2>
      <span class="pill-count">Terbuka untuk donasi</span>
    </div>

    <section class="campaigns">

      <?php
      $count = 0;
      while ($row = mysqli_fetch_assoc($query)) {
        $count++;

        $terkumpul  = $row['dana_terkumpul'];
        $target     = $row['target_dana'];
        $persen     = ($target > 0) ? min(100, round(($terkumpul / $target) * 100)) : 0;
      ?>

        <article class="card">
          <div class="card-image">
            <img src="img/<?php echo $row['gambar']; ?>" alt="<?php echo htmlspecialchars($row['judul']); ?>">
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

            <div class="progress-wrap">
              <div class="progress-bar-bg">
                <div class="progress-bar-fill" style="width: <?php echo $persen; ?>%"></div>
              </div>
            </div>

            <div class="card-footer">
              <div class="stat">
                <span class="label">Terkumpul</span>
                <span class="value">Rp<?php echo number_format($terkumpul); ?></span>
              </div>
              <div class="stat">
                <span class="label">Target &nbsp; <?php echo $persen; ?>%</span>
                <span class="value">Rp<?php echo number_format($target); ?></span>
              </div>
            </div>
          </div>
        </article>

      <?php } ?>

      <?php if ($count === 0): ?>
        <div class="empty-state">
          <p>😔 Belum ada kampanye aktif saat ini.</p>
        </div>
      <?php endif; ?>

    </section>
  </main>

  <footer>
    <h2>Kirimkan dukunganmu segera — setiap rupiah yang kamu berikan sangat berarti bagi mereka 💚</h2>
  </footer>

</body>

</html>