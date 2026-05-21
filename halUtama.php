<?php
session_start();

require "koneksi/koneksi.php";

if (!isset($_SESSION["id"])) {
  header("location:halLogin.php");
  exit();
}

if (isset($_SESSION["nama_user"])) {
  $nama = $_SESSION["nama_user"];
} else {
  $nama = "user";
}


if (isset($_GET['kategori'])) {
  $kategori_dipilih = $_GET['kategori'];
} else {
  $kategori_dipilih = '';
}

if (isset($_GET['keyword'])) {
  $keyword = $_GET['keyword'];
} else {
  $keyword = '';
}

// === LOGIKA PAGINATION ===
$limit = 8; // Batasi maksimal 8 campaign per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
  $page = 1;
}
$offset = ($page - 1) * $limit;

// Query dasar (tanpa limit) untuk menghitung total data yang sesuai filter
$sql_base = "SELECT * FROM campaign WHERE deadline >= CURDATE()";

if ($kategori_dipilih != '') {
  $kategori_aman = mysqli_real_escape_string($koneksi, $kategori_dipilih);
  $sql_base = $sql_base . " AND kategori = '$kategori_aman'";
}

if ($keyword != '') {
  $keyword_aman = mysqli_real_escape_string($koneksi, $keyword);
  $sql_base = $sql_base . " AND (judul LIKE '%$keyword_aman%' OR kategori LIKE '%$keyword_aman%' OR deskripsi LIKE '%$keyword_aman%')";
}

// Hitung total campaign setelah difilter
$query_total = mysqli_query($koneksi, $sql_base);
$total_records = mysqli_num_rows($query_total);
$total_pages = ceil($total_records / $limit);

// Jalankan query utama dengan menyertakan ORDER BY, LIMIT, dan OFFSET
$sql = $sql_base . " ORDER BY deadline ASC LIMIT $limit OFFSET $offset";
$query = mysqli_query($koneksi, $sql);
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
        <img src="logo/T.png" alt="Klik gambar ini" />
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
      <form action="halUtama.php" method="GET">
        <div class="search-container">
          <select class="category-select" name="kategori" onchange="this.form.submit()">
            <option value="">✦ Semua Kategori</option>
            <option value="Pendidikan" <?php if ($kategori_dipilih == 'Pendidikan') {
                                          echo 'selected';
                                        } ?>>📚 Pendidikan</option>
            <option value="Kesehatan" <?php if ($kategori_dipilih == 'Kesehatan') {
                                        echo 'selected';
                                      } ?>>❤️ Kesehatan</option>
            <option value="Lingkungan" <?php if ($kategori_dipilih == 'Lingkungan') {
                                          echo 'selected';
                                        } ?>>🌿 Lingkungan</option>
            <option value="Kemanusiaan" <?php if ($kategori_dipilih == 'Kemanusiaan') {
                                          echo 'selected';
                                        } ?>>🤝 Kemanusiaan</option>
          </select>
          <div class="divider"></div>
          <input type="text" name="keyword" value="<?php echo htmlspecialchars($keyword); ?>" placeholder="🔍  Cari judul kampanye..." />
          <button type="submit" style="display: none;"></button>
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
      if ($query) {
        while ($row = mysqli_fetch_assoc($query)) {
          $count++;

          $terkumpul  = $row['dana_terkumpul'];
          $target     = $row['target_dana'];

          $persen = 0;
          if ($target > 0) {
            $persen = round(($terkumpul / $target) * 100);
            if ($persen > 100) {
              $persen = 100;
            }
          }
      ?>

          <article class="card">
            <div class="card-image">
              <img src="<?php echo $row['gambar']; ?>" alt="<?php echo htmlspecialchars($row['judul']); ?>">
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

      <?php
        }
      }
      ?>

      <?php if ($count === 0): ?>
        <div class="empty-state">
          <p>😔 Belum ada kampanye aktif yang sesuai.</p>
        </div>
      <?php endif; ?>

    </section>

    <!-- UI Pagination (Neo-Cartoonist) -->
    <?php if ($total_pages > 1): ?>
      <div class="pagination">
        <!-- Tombol Prev -->
        <?php if ($page > 1): ?>
          <a href="?page=<?php echo $page - 1; ?>&kategori=<?php echo urlencode($kategori_dipilih); ?>&keyword=<?php echo urlencode($keyword); ?>" class="page-btn prev-btn">&laquo; Prev</a>
        <?php endif; ?>

        <!-- Angka Halaman -->
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
          <a href="?page=<?php echo $i; ?>&kategori=<?php echo urlencode($kategori_dipilih); ?>&keyword=<?php echo urlencode($keyword); ?>" class="page-btn <?php echo ($i == $page) ? 'active' : ''; ?>">
            <?php echo $i; ?>
          </a>
        <?php endfor; ?>

        <!-- Tombol Next -->
        <?php if ($page < $total_pages): ?>
          <a href="?page=<?php echo $page + 1; ?>&kategori=<?php echo urlencode($kategori_dipilih); ?>&keyword=<?php echo urlencode($keyword); ?>" class="page-btn next-btn">Next &raquo;</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>

  </main>

  <footer>
    <h2>Kirimkan dukunganmu segera — setiap rupiah yang kamu berikan sangat berarti bagi mereka 💚</h2>
  </footer>

</body>

</html>