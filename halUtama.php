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

if (isset($_GET['waktu'])) {
  $waktu_dipilih = $_GET['waktu'];
} else {
  $waktu_dipilih = '';
}

if (isset($_GET['keyword'])) {
  $keyword = $_GET['keyword'];
} else {
  $keyword = '';
}

if (isset($_GET['sort'])) {
  $sort_dipilih = $_GET['sort'];
} else {
  $sort_dipilih = 'deadline';
}
if ($sort_dipilih !== 'target' && $sort_dipilih !== 'deadline') {
  $sort_dipilih = 'deadline';
}

// === LOGIKA PAGINATION ===
$limit = 8; // Batasi maksimal 8 campaign per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
  $page = 1;
}
$offset = ($page - 1) * $limit;

// 1. Pastikan baris pendefinisian $sql_base ini ada dan tidak terhapus:
$sql_base = "SELECT * FROM campaign WHERE deadline >= CURDATE()";

if ($kategori_dipilih != '') {
  $kategori_aman = mysqli_real_escape_string($koneksi, $kategori_dipilih);
  $sql_base = $sql_base . " AND kategori = '$kategori_aman'";
}

if ($waktu_dipilih != '') {
  if ($waktu_dipilih === 'over30') {
    $sql_base = $sql_base . " AND deadline > DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
  } else {
    $waktu_aman = (int)$waktu_dipilih;
    $sql_base = $sql_base . " AND deadline <= DATE_ADD(CURDATE(), INTERVAL $waktu_aman DAY)";
  }
}

if ($keyword != '') {
  $keyword_aman = mysqli_real_escape_string($koneksi, $keyword);
  $sql_base = $sql_base . " AND (judul LIKE '%$keyword_aman%' OR kategori LIKE '%$keyword_aman%' OR deskripsi LIKE '%$keyword_aman%' OR lokasi LIKE '%$keyword_aman%' OR penyelenggara LIKE '%$keyword_aman%')";
}

// 2. Hitung total campaign setelah difilter
$query_total = mysqli_query($koneksi, $sql_base);
$total_records = mysqli_num_rows($query_total);
$total_pages = ceil($total_records / $limit);

// 3. Jalankan query utama dengan menyertakan ORDER BY (deadline & target_dana), LIMIT, dan OFFSET
if ($sort_dipilih === 'target') {
  $order_clause = "target_dana ASC, deadline ASC";
} else {
  $order_clause = "deadline ASC, target_dana ASC";
}
$sql = $sql_base . " ORDER BY $order_clause LIMIT $limit OFFSET $offset";
$query = mysqli_query($koneksi, $sql);

// === QUERY RIWAYAT DONASI UNTUK SLIDE PANE ===
$is_donor = (isset($_SESSION['role']) && $_SESSION['role'] != 'guest' && isset($_SESSION['id']));
$donasi_summary = [];
$donasi_history = [];

if ($is_donor) {
  $user_id = (int)$_SESSION['id'];

  // Ringkasan per status
  $q_summary = mysqli_query($koneksi, "
    SELECT status, COUNT(*) as jumlah, SUM(nominal_donasi) as total
    FROM donasi
    WHERE user_id = $user_id
    GROUP BY status
  ");
  while ($s = mysqli_fetch_assoc($q_summary)) {
    $donasi_summary[$s['status']] = [
      'jumlah' => (int)$s['jumlah'],
      'total' => (float)$s['total']
    ];
  }

  // Riwayat detail
  $q_history = mysqli_query($koneksi, "
    SELECT d.*, c.judul AS judul_kampanye
    FROM donasi d
    JOIN campaign c ON d.campaign_id = c.id
    WHERE d.user_id = $user_id
    ORDER BY d.tgl_donasi DESC
  ");
  while ($h = mysqli_fetch_assoc($q_history)) {
    $donasi_history[] = $h;
  }
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Beranda - Teletubies</title>
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
    echo "<p class='datang'>Halo, $nama!</p>";
    ?>

    <nav class="links">
      <a href="halUtama.php" class="active">Home</a>
      <?php if (isset($_SESSION["role"]) && $_SESSION["role"] == "guest"): ?>
        <a href="halLogin.php">Login</a>
      <?php else: ?>
        <button id="btn-riwayat" class="btn-riwayat-toggle" onclick="toggleSlidePane()">Riwayat</button>
        <a href="logout.php">Logout</a>
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
        <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort_dipilih); ?>">
        <div class="search-container">
          <select class="category-select" name="kategori" onchange="this.form.submit()">
            <option value="">Semua Kategori</option>
            <option value="Pendidikan" <?php if ($kategori_dipilih == 'Pendidikan') {
                                          echo 'selected';
                                        } ?>>Pendidikan</option>
            <option value="Kesehatan" <?php if ($kategori_dipilih == 'Kesehatan') {
                                        echo 'selected';
                                      } ?>>Kesehatan</option>
            <option value="Lingkungan" <?php if ($kategori_dipilih == 'Lingkungan') {
                                          echo 'selected';
                                        } ?>>Lingkungan</option>
            <option value="Kemanusiaan" <?php if ($kategori_dipilih == 'Kemanusiaan') {
                                          echo 'selected';
                                        } ?>>Kemanusiaan</option>
          </select>
          <select class="category-select deadline-select" name="waktu" onchange="this.form.submit()">
            <option value="">Semua Waktu</option>
            <option value="3" <?php if ($waktu_dipilih == '3') {
                                echo 'selected';
                              } ?>>Urgent (≤ 3 Hari)</option>
            <option value="7" <?php if ($waktu_dipilih == '7') {
                                echo 'selected';
                              } ?>>Mendesak (≤ 7 Hari)</option>
            <option value="30" <?php if ($waktu_dipilih == '30') {
                                 echo 'selected';
                               } ?>>Bulan Ini (≤ 30 Hari)</option>
            <option value="over30" <?php if ($waktu_dipilih == 'over30') {
                                     echo 'selected';
                                   } ?>>Lebih dari 30 Hari</option>
          </select>
          <div class="divider"></div>
          <input type="text" name="keyword" value="<?php echo htmlspecialchars($keyword); ?>" placeholder="Cari judul, lokasi, atau pengelola..." />
          <button type="submit" style="display: none;"></button>
        </div>
      </form>
    </section>

    <hr class="garis" />

    <div class="section-label">
      <h2>Kampanye Aktif</h2>
      <span class="pill-count">Terbuka untuk donasi</span>
      <div class="sort-container">
        <span class="sort-label">Urutan:</span>
        <a href="?sort=deadline&kategori=<?php echo urlencode($kategori_dipilih); ?>&waktu=<?php echo urlencode($waktu_dipilih); ?>&keyword=<?php echo urlencode($keyword); ?>" class="sort-btn <?php echo $sort_dipilih === 'deadline' ? 'active' : ''; ?>">📅 Tanggal Terdekat</a>
        <a href="?sort=target&kategori=<?php echo urlencode($kategori_dipilih); ?>&waktu=<?php echo urlencode($waktu_dipilih); ?>&keyword=<?php echo urlencode($keyword); ?>" class="sort-btn <?php echo $sort_dipilih === 'target' ? 'active' : ''; ?>">💰 Dana Terkecil</a>
      </div>
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
                <span>👤 <?php echo htmlspecialchars($row['penyelenggara']); ?></span>
                <span>📍 <?php echo htmlspecialchars($row['lokasi']); ?></span>
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
          <p>Belum ada kampanye aktif yang sesuai.</p>
        </div>
      <?php endif; ?>

    </section>

    <!-- UI Pagination -->
    <?php if ($total_pages > 1): ?>
      <div class="pagination">
        <!-- Tombol Prev -->
        <?php if ($page > 1): ?>
          <a href="?page=<?php echo $page - 1; ?>&kategori=<?php echo urlencode($kategori_dipilih); ?>&waktu=<?php echo urlencode($waktu_dipilih); ?>&keyword=<?php echo urlencode($keyword); ?>&sort=<?php echo urlencode($sort_dipilih); ?>" class="page-btn prev-btn">&laquo; Prev</a>
        <?php endif; ?>

        <!-- Angka Halaman -->
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
          <a href="?page=<?php echo $i; ?>&kategori=<?php echo urlencode($kategori_dipilih); ?>&waktu=<?php echo urlencode($waktu_dipilih); ?>&keyword=<?php echo urlencode($keyword); ?>&sort=<?php echo urlencode($sort_dipilih); ?>" class="page-btn <?php echo ($i == $page) ? 'active' : ''; ?>">
            <?php echo $i; ?>
          </a>
        <?php endfor; ?>

        <!-- Tombol Next -->
        <?php if ($page < $total_pages): ?>
          <a href="?page=<?php echo $page + 1; ?>&kategori=<?php echo urlencode($kategori_dipilih); ?>&waktu=<?php echo urlencode($waktu_dipilih); ?>&keyword=<?php echo urlencode($keyword); ?>&sort=<?php echo urlencode($sort_dipilih); ?>" class="page-btn next-btn">Next &raquo;</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>

  </main>

  <footer>
    <h2>Kirimkan dukunganmu segera — setiap rupiah yang kamu berikan sangat berarti bagi mereka 💚</h2>
  </footer>

  <!-- SLIDE PANE OVERLAY -->
  <?php if ($is_donor): ?>
    <div id="slide-overlay" class="slide-overlay" onclick="toggleSlidePane()"></div>
    <aside id="slide-pane" class="slide-pane">
      <div class="slide-pane-header">
        <h2>Riwayat Donasi Saya</h2>
        <button class="slide-close" onclick="toggleSlidePane()">&times;</button>
      </div>

      <!-- RINGKASAN -->
      <div class="slide-summary">
        <div class="summary-card summary-berhasil">
          <span class="summary-icon">✅</span>
          <div class="summary-detail">
            <span class="summary-label">Verified</span>
            <span class="summary-amount">Rp<?php echo number_format($donasi_summary['BERHASIL']['total'] ?? 0); ?></span>
            <span class="summary-count"><?php echo ($donasi_summary['BERHASIL']['jumlah'] ?? 0); ?> donasi</span>
          </div>
        </div>
        <div class="summary-card summary-pending">
          <span class="summary-icon">⏳</span>
          <div class="summary-detail">
            <span class="summary-label">Pending</span>
            <span class="summary-amount">Rp<?php echo number_format($donasi_summary['PENDING']['total'] ?? 0); ?></span>
            <span class="summary-count"><?php echo ($donasi_summary['PENDING']['jumlah'] ?? 0); ?> donasi</span>
          </div>
        </div>
        <div class="summary-card summary-ditolak">
          <span class="summary-icon">❌</span>
          <div class="summary-detail">
            <span class="summary-label">Ditolak</span>
            <span class="summary-amount">Rp<?php echo number_format($donasi_summary['DITOLAK']['total'] ?? 0); ?></span>
            <span class="summary-count"><?php echo ($donasi_summary['DITOLAK']['jumlah'] ?? 0); ?> donasi</span>
          </div>
        </div>
      </div>

      <!-- RIWAYAT DETAIL -->
      <div class="slide-history">
        <h3>Detail Riwayat</h3>
        <?php if (count($donasi_history) > 0): ?>
          <?php foreach ($donasi_history as $riwayat): ?>
            <div class="history-card history-<?php echo strtolower($riwayat['status']); ?>">
              <div class="history-status-bar"></div>
              <div class="history-body">
                <div class="history-top">
                  <span class="history-campaign"><?php echo htmlspecialchars($riwayat['judul_kampanye']); ?></span>
                  <span class="history-badge badge-<?php echo strtolower($riwayat['status']); ?>"><?php echo $riwayat['status']; ?></span>
                </div>
                <span class="history-nominal">Rp<?php echo number_format($riwayat['nominal_donasi']); ?></span>
                <div class="history-bottom">
                  <span class="history-metode"><?php echo $riwayat['metode_pembayaran']; ?></span>
                  <span class="history-date"><?php echo date("d M Y", strtotime($riwayat['tgl_donasi'])); ?></span>
                </div>
                <?php if (!empty($riwayat['pesan_dukungan'])): ?>
                  <p class="history-pesan">"<?php echo htmlspecialchars($riwayat['pesan_dukungan']); ?>"</p>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="history-empty">
            <p>Kamu belum pernah berdonasi.<br>Ayo mulai berdonasi sekarang!</p>
          </div>
        <?php endif; ?>
      </div>
    </aside>
  <?php endif; ?>

  <script>
    function toggleSlidePane() {
      const pane = document.getElementById('slide-pane');
      const overlay = document.getElementById('slide-overlay');
      pane.classList.toggle('open');
      overlay.classList.toggle('open');
      document.body.classList.toggle('slide-pane-active');
    }
  </script>

</body>

</html>