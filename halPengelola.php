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

// --- PROSES ACTION FORM ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. TERIMA DONASI
    if (isset($_POST['accept_donation'])) {
        $id_donasi = (int)$_POST['id_donasi'];
        $id_campaign = (int)$_POST['id'];

        // Verify ownership & donation status PENDING
        $don_check = mysqli_query($koneksi, "
            SELECT d.nominal_donasi, d.status 
            FROM donasi d
            JOIN campaign c ON d.campaign_id = c.id
            WHERE d.id_donasi = $id_donasi 
            AND c.id = $id_campaign 
            AND c.penyelenggara = '$nama_pengelola'
        ");
        $don_data = mysqli_fetch_assoc($don_check);

        if ($don_data && $don_data['status'] === 'PENDING') {
            $nominal = $don_data['nominal_donasi'];

            // Update status to BERHASIL
            $update_don = mysqli_query($koneksi, "UPDATE donasi SET status = 'BERHASIL' WHERE id_donasi = $id_donasi");
            // Add to campaign's dana_terkumpul
            $update_camp = mysqli_query($koneksi, "UPDATE campaign SET dana_terkumpul = dana_terkumpul + $nominal WHERE id = $id_campaign");

            if ($update_don && $update_camp) {
                $_SESSION['msg_success'] = "Donasi sebesar Rp" . number_format($nominal) . " berhasil diterima!";
            } else {
                $_SESSION['msg_error'] = "Gagal memproses penerimaan donasi.";
            }
        } else {
            $_SESSION['msg_error'] = "Donasi tidak valid atau sudah diproses.";
        }

        header("Location: halPengelola.php?id=" . $id_campaign);
        exit();
    }

    // 2. TOLAK DONASI
    if (isset($_POST['reject_donation'])) {
        $id_donasi = (int)$_POST['id_donasi'];
        $id_campaign = (int)$_POST['id'];

        // Verify ownership & donation status PENDING
        $don_check = mysqli_query($koneksi, "
            SELECT d.status 
            FROM donasi d
            JOIN campaign c ON d.campaign_id = c.id
            WHERE d.id_donasi = $id_donasi 
            AND c.id = $id_campaign 
            AND c.penyelenggara = '$nama_pengelola'
        ");
        $don_data = mysqli_fetch_assoc($don_check);

        if ($don_data && $don_data['status'] === 'PENDING') {
            // Update status to DITOLAK
            $update_don = mysqli_query($koneksi, "UPDATE donasi SET status = 'DITOLAK' WHERE id_donasi = $id_donasi");

            if ($update_don) {
                $_SESSION['msg_success'] = "Donasi telah ditolak.";
            } else {
                $_SESSION['msg_error'] = "Gagal memproses penolakan donasi.";
            }
        } else {
            $_SESSION['msg_error'] = "Donasi tidak valid atau sudah diproses.";
        }

        header("Location: halPengelola.php?id=" . $id_campaign);
        exit();
    }
}

$query_list = mysqli_query($koneksi, "
    SELECT id, judul 
    FROM campaign 
    WHERE penyelenggara = '$nama_pengelola'
");

if (isset($_GET['id'])) {
    $id_dipilih = (int)$_GET['id'];
} else {
    $id_dipilih = 0;
}

$data = null;
$progress = 0;
$dana_pending = 0;

if ($id_dipilih > 0) {
    $query_detail = mysqli_query($koneksi, "
        SELECT * FROM campaign 
        WHERE id = $id_dipilih 
        AND penyelenggara = '$nama_pengelola'
    ");
    $data = mysqli_fetch_assoc($query_detail);

    if ($data) {
        if ($data['target_dana'] > 0) {
            $progress = ($data['dana_terkumpul'] / $data['target_dana']) * 100;
            if ($progress > 100) {
                $progress = 100;
            }
        }

        // Hitung total donasi pending
        $query_pending = mysqli_query($koneksi, "
            SELECT SUM(nominal_donasi) AS total_pending 
            FROM donasi 
            WHERE campaign_id = $id_dipilih 
            AND status = 'PENDING'
        ");
        $row_pending = mysqli_fetch_assoc($query_pending);
        $dana_pending = $row_pending['total_pending'] ? (float)$row_pending['total_pending'] : 0.0;
    }
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Panel Pengelola - Crowdfunding</title>
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
        <?php echo "<p class='datang'>Panel Pengelola: $nama</p>"; ?>
        <nav class="links">
            <!-- <a href="halUtama.php"> Home</a> -->
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <main>
        <section class="detail">
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

            <div class="selector-container">
                <label for="campaign_select">Pilih Kampanye Anda:</label>
                <select id="campaign_select" onchange="window.location.href='halPengelola.php?id=' + this.value">
                    <option value="">-- Pilih Kampanye untuk Dikelola --</option>
                    <?php while ($list = mysqli_fetch_assoc($query_list)): ?>
                        <option value="<?php echo $list['id']; ?>" <?php echo ($id_dipilih == $list['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($list['judul']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <?php if ($data): ?>
                <div class="campaign-title">
                    <h1><?php echo htmlspecialchars($data['judul']); ?></h1>
                    <p class="campaign-subtitle-preview" style="font-size: 1.1rem; color: var(--ink-light); margin-top: 10px; font-weight: 600; text-align: center; max-width: 800px; margin-left: auto; margin-right: auto; line-height: 1.6;"><?php echo htmlspecialchars($data['sub_judul']); ?></p>
                </div>

                <div class="detail-content">
                    <div class="poster">
                        <div class="poster-edit-container">
                            <img src="<?php echo $data['gambar']; ?>" alt="detail kampanye" />
                        </div>

                        <div class="tags edit-mode-tags" style="border-bottom: none; padding-bottom: 0;">
                            <span class="tag"><?php echo htmlspecialchars($data['kategori']); ?></span>
                            <span class="tag"><?php echo htmlspecialchars($data['lokasi']); ?></span>
                        </div>

                        <div class="desc-edit-area" style="margin-top: 20px;">
                            <label>Deskripsi Lengkap:</label>
                            <div class="desc-content-preview" style="background: var(--white); border: var(--border); border-radius: var(--radius-md); padding: 20px; box-shadow: var(--shadow-ink); line-height: 1.7; font-family: 'Nunito', sans-serif; font-weight: 600; color: var(--ink-light); min-height: 150px; overflow-y: auto;">
                                <?php echo nl2br(htmlspecialchars($data['deskripsi_lengkap'])); ?>
                            </div>
                        </div>
                    </div>

                    <div class="info">
                        <div class="progress-container">
                            <div class="progress-bar-fill" style="width: <?php echo $progress; ?>%"></div>
                        </div>

                        <label class="label-manage">Dana Terkumpul (Berhasil)</label>
                        <h2 class="dana-terkumpul">
                            Rp<?php echo number_format($data['dana_terkumpul']); ?>
                        </h2>

                        <label class="label-manage">Dana Pending (Belum Diverifikasi)</label>
                        <h2 class="dana-pending">
                            Rp<?php echo number_format($dana_pending); ?>
                        </h2>

                        <label class="label-manage">Target Dana</label>
                        <h2 class="dana-target" style="font-family: 'Fredoka One', cursive; font-size: 2.2rem; color: var(--ink); text-shadow: 2px 2px 0px var(--ink); margin-bottom: 15px;">
                            Rp<?php echo number_format($data['target_dana']); ?>
                        </h2>

                        <div class="stats-box manage">
                            <label>Penyelenggara:</label>
                            <p style="font-family: 'Nunito', sans-serif; font-weight: 700; font-size: 1.1rem; color: var(--ink); margin-top: 5px; margin-bottom: 15px;"><strong><?php echo htmlspecialchars($data['penyelenggara']); ?></strong></p>

                            <label>Deadline:</label>
                            <p style="font-family: 'Nunito', sans-serif; font-weight: 700; font-size: 1.1rem; color: var(--ink); margin-top: 5px; margin-bottom: 5px;"><strong><?php echo date('d M Y', strtotime($data['deadline'])); ?></strong></p>
                        </div>

                        <a href="editKampanye.php?id=<?php echo $data['id']; ?>" class="btn" style="text-decoration: none; display: block; text-align: center; font-family: 'Fredoka One', cursive; font-size: 1.15rem; padding: 14px; margin-top: 15px; background-color: var(--yellow); color: var(--ink); border: var(--border); border-radius: var(--radius-sm); box-shadow: var(--shadow-ink); box-sizing: border-box; width: 100%; transition: all 0.15s ease;">⚙️ Edit Kampanye</a>
                    </div>
                </div>
                <div class="bukti-section">
                    <h3>Bukti Donatur Masuk</h3>
                    <div class="scroll-wrapper">
                        <?php
                        $q_donasi = mysqli_query($koneksi, "SELECT donasi.*, user.nama_lengkap, user.email FROM donasi JOIN user ON donasi.user_id = user.id WHERE donasi.campaign_id = $id_dipilih ORDER BY donasi.tgl_donasi DESC");
                        if (mysqli_num_rows($q_donasi) > 0):
                            while ($donasi = mysqli_fetch_assoc($q_donasi)):
                        ?>
                                <div class="bukti-card">
                                    <div class="donatur-profile">
                                        <div class="avatar"><?php echo strtoupper(substr($donasi['nama_lengkap'], 0, 1)); ?></div>
                                        <div class="donatur-details">
                                            <h4 class="donatur-nama"><?php echo htmlspecialchars($donasi['nama_lengkap']); ?></h4>
                                            <span class="donatur-email"><?php echo htmlspecialchars($donasi['email']); ?></span>
                                        </div>
                                    </div>
                                    <div class="bukti-info">
                                        <p><strong>Rp<?php echo number_format($donasi['nominal_donasi']); ?></strong></p>
                                        <p class="metode-info"><?php echo htmlspecialchars($donasi['metode_pembayaran']); ?></p>
                                        <p class="status-donasi status-<?php echo strtolower($donasi['status']); ?>"><?php echo $donasi['status']; ?></p>
                                        <p class="pesan">"<?php echo htmlspecialchars($donasi['pesan_dukungan']); ?>"</p>
                                        <small><?php echo date("d M Y", strtotime($donasi['tgl_donasi'])); ?></small>

                                        <button type="button" class="btn-lihat-bukti" onclick="openBuktiModal('bukti_transfer/<?php echo $donasi['bukti_transfer']; ?>', '<?php echo htmlspecialchars($donasi['nama_lengkap'], ENT_QUOTES); ?>')">🔍 Lihat Bukti</button>

                                        <?php if ($donasi['status'] === 'PENDING'): ?>
                                            <div class="donation-actions">
                                                <form action="" method="POST">
                                                    <input type="hidden" name="id_donasi" value="<?php echo $donasi['id_donasi']; ?>">
                                                    <input type="hidden" name="id" value="<?php echo $id_dipilih; ?>">
                                                    <button type="submit" name="accept_donation" class="btn-terima" onclick="return confirm('Terima donasi ini?')">Terima</button>
                                                    <button type="submit" name="reject_donation" class="btn-tolak" onclick="return confirm('Tolak donasi ini?')">Tolak</button>
                                                </form>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                        <?php
                            endwhile;
                        else:
                            echo "<p class='no-data'>Belum ada donasi yang masuk.</p>";
                        endif;
                        ?>
                    </div>
                </div>

            <?php else: ?>
                <div class="welcome-manager">
                    <p>Silahkan pilih kampanye yang ingin Anda kelola melalui menu dropdown di atas.</p>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <!-- Modal Bukti Transfer -->
    <div id="buktiModal" class="modal-overlay" onclick="closeBuktiModal()">
        <div class="modal-content" onclick="event.stopPropagation()">
            <span class="modal-close" onclick="closeBuktiModal()">&times;</span>
            <div class="modal-header">
                <h4>Bukti Transfer: <span id="modalDonaturNama"></span></h4>
            </div>
            <div class="modal-body">
                <img id="modalBuktiImg" src="" alt="Bukti Transfer Full">
            </div>
        </div>
    </div>

    <script>
        function openBuktiModal(imgSrc, donaturNama) {
            document.getElementById('modalBuktiImg').src = imgSrc;
            document.getElementById('modalDonaturNama').textContent = donaturNama;
            document.getElementById('buktiModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeBuktiModal() {
            document.getElementById('buktiModal').classList.remove('active');
            document.getElementById('modalBuktiImg').src = '';
            document.body.style.overflow = '';
        }
    </script>

    <footer>

    </footer>
</body>

</html>