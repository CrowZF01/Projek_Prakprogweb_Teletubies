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
            SELECT donasi.nominal_donasi, donasi.status 
            FROM donasi
            JOIN campaign ON donasi.campaign_id = campaign.id
            WHERE donasi.id_donasi = $id_donasi 
            AND campaign.id = $id_campaign 
            AND campaign.penyelenggara = '$nama_pengelola'
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
            SELECT donasi.status 
            FROM donasi
            JOIN campaign ON donasi.campaign_id = campaign.id
            WHERE donasi.id_donasi = $id_donasi 
            AND campaign.id = $id_campaign 
            AND campaign.penyelenggara = '$nama_pengelola'
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

    // 3. BUAT KAMPANYE BARU
    if (isset($_POST['create_campaign'])) {
        $judul = mysqli_real_escape_string($koneksi, $_POST['judul']);
        $sub_judul = mysqli_real_escape_string($koneksi, $_POST['sub_judul']);
        $kategori = mysqli_real_escape_string($koneksi, $_POST['kategori']);
        $lokasi = mysqli_real_escape_string($koneksi, $_POST['lokasi']);
        $deskripsi_lengkap = mysqli_real_escape_string($koneksi, $_POST['deskripsi_lengkap']);
        $target_dana = (int)$_POST['target_dana'];
        $deadline = mysqli_real_escape_string($koneksi, $_POST['deadline']);
        
        $path_database = "";
        
        // Handle upload
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $nama_file = $_FILES['gambar']['name'];
            $temp_file = $_FILES['gambar']['tmp_name'];
            $ekstensi = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            
            if (in_array($ekstensi, $allowed)) {
                $nama_file_baru = time() . "_" . $nama_file;
                $folder_upload = "uploads/";
                
                if (!is_dir($folder_upload)) {
                    mkdir($folder_upload, 0777, true);
                }
                
                if (move_uploaded_file($temp_file, $folder_upload . $nama_file_baru)) {
                    $path_database = $folder_upload . $nama_file_baru;
                } else {
                    $_SESSION['msg_error'] = "Gagal memindahkan file gambar yang diunggah.";
                }
            } else {
                $_SESSION['msg_error'] = "Format gambar tidak didukung! Harus berupa JPG, JPEG, PNG, atau WEBP.";
            }
        } else {
            $_SESSION['msg_error'] = "Anda wajib mengunggah gambar cover kampanye.";
        }
        
        if (!isset($_SESSION['msg_error'])) {
            // Mengambil 150 karakter pertama dari deskripsi lengkap
            // dijadikan deskripsi ringkas yang akan ditampilkan di card halaman utama
            $deskripsi_pendek = mysqli_real_escape_string($koneksi, substr(strip_tags($_POST['deskripsi_lengkap']), 0, 150));
            
            $sql_insert = "INSERT INTO campaign (judul, sub_judul, kategori, lokasi, deskripsi, deskripsi_lengkap, target_dana, penyelenggara, deadline, gambar, dana_terkumpul) 
                           VALUES ('$judul', '$sub_judul', '$kategori', '$lokasi', '$deskripsi_pendek', '$deskripsi_lengkap', '$target_dana', '$nama_pengelola', '$deadline', '$path_database', 0)";
            
            if (mysqli_query($koneksi, $sql_insert)) {
                $new_id = mysqli_insert_id($koneksi);
                $_SESSION['msg_success'] = "Kampanye baru berhasil dibuat!";
                header("Location: halPengelola.php?id=" . $new_id);
                exit();
            } else {
                $_SESSION['msg_error'] = "Gagal membuat kampanye: " . mysqli_error($koneksi);
            }
        }
        
        // Jika gagal, kembali ke form tambah dengan pesan error
        header("Location: halPengelola.php?action=tambah");
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
                <a href="halPengelola.php?action=tambah" class="btn-back" style="margin-bottom: 0; margin-left: auto; white-space: nowrap;">➕ Buat Kampanye Baru</a>
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

            <?php elseif (isset($_GET['action']) && $_GET['action'] === 'tambah'): ?>
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="campaign-title">
                        <h1>
                            <input type="text" name="judul" class="edit-judul" placeholder="Tulis Judul Kampanye Menarik Di Sini..." required>
                        </h1>
                        <textarea name="sub_judul" class="edit-subtitle" placeholder="Tulis sub-judul singkat yang menjelaskan kampanye Anda..." required></textarea>
                    </div>

                    <div class="detail-content">
                        <div class="poster">
                            <div class="poster-edit-container">
                                <div class="file-upload" style="display: block; width: 100%; box-sizing: border-box; text-align: center; padding: 40px 20px;">
                                    <span style="display: block; margin-bottom: 15px; font-family: 'Fredoka One', cursive; font-size: 1.2rem;">📸 Unggah Gambar Cover</span>
                                    <input type="file" name="gambar" required style="font-family: 'Nunito', sans-serif;">
                                </div>
                            </div>

                            <div class="tags edit-mode-tags" style="border-bottom: none; padding-bottom: 0; margin-top: 25px;">
                                <span class="tag">🔖 
                                    <select name="kategori" required style="border: none; background: transparent; font-family: 'Nunito', sans-serif; font-weight: 800; font-size: 0.85rem; color: var(--green-dark); outline: none; border-bottom: 2px dashed var(--green); cursor: pointer;">
                                        <option value="Pendidikan">Pendidikan</option>
                                        <option value="Kesehatan">Kesehatan</option>
                                        <option value="Lingkungan">Lingkungan</option>
                                        <option value="Kemanusiaan">Kemanusiaan</option>
                                    </select>
                                </span>
                                <span class="tag">📍 
                                    <input type="text" name="lokasi" placeholder="Lokasi (Contoh: Yogyakarta)" required style="border: none; background: transparent; font-family: 'Nunito', sans-serif; font-weight: 800; font-size: 0.85rem; color: var(--green-dark); width: 180px; outline: none; border-bottom: 2px dashed var(--green); padding: 2px;">
                                </span>
                            </div>

                            <div class="desc-edit-area" style="margin-top: 25px;">
                                <label>Deskripsi Lengkap Kampanye:</label>
                                <textarea name="deskripsi_lengkap" placeholder="Ceritakan kisah di balik penggalangan dana ini secara lengkap..." required></textarea>
                            </div>
                        </div>

                        <div class="info">
                            <label class="label-manage">Target Dana (Rp)</label>
                            <input type="number" name="target_dana" class="edit-number" placeholder="Contoh: 50000000" min="10000" required style="box-sizing: border-box;">

                            <div class="stats-box manage">
                                <label>Penyelenggara (Otomatis):</label>
                                <input type="text" value="<?php echo htmlspecialchars($nama); ?>" disabled style="background-color: #f0f0f0; cursor: not-allowed; box-shadow: none; border-color: #ccc; box-sizing: border-box;">

                                <label style="margin-top: 15px;">Deadline Penggalangan Dana:</label>
                                <input type="date" name="deadline" required min="<?php echo date('Y-m-d'); ?>" style="box-sizing: border-box;">
                            </div>

                            <button type="submit" name="create_campaign" class="btn" style="background-color: var(--green); color: var(--white); border: var(--border); border-radius: var(--radius-sm); box-shadow: var(--shadow-ink); font-family: 'Fredoka One', cursive; font-size: 1.15rem; padding: 14px; margin-top: 15px; width: 100%; transition: all 0.15s ease;">🚀 Publikasikan Kampanye Baru</button>
                            <a href="halPengelola.php" class="btn-back" style="display: block; text-align: center; margin-top: 15px; margin-bottom: 0;">Batal</a>
                        </div>
                    </div>
                </form>
            <?php else: ?>
                <div class="welcome-manager">
                    <p>Silahkan pilih kampanye yang ingin Anda kelola melalui menu dropdown di atas.</p>
                    <p style="margin-top: 15px; font-size: 1rem;">atau</p>
                    <a href="halPengelola.php?action=tambah" class="btn-back" style="margin-top: 15px; display: inline-block; margin-bottom: 0;">➕ Buat Kampanye Baru</a>
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