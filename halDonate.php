<?php
session_start();

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
            <?php if(isset($_SESSION["role"]) && $_SESSION["role"] == "guest"):?>
                <a href="halLogin.php">Login</a>
            <?php else:?>
                <a href="logout.php">Logout</a>
            <?php endif;?>
        </nav>
    </header>

    <main> 
        <a href="halDetail.php" class="back">&larr; Kembali ke Detail</a>
        <section class="donate">
            <h1>Formulir Donasi</h1>
            <p class="campaign-summary">Anda akan mendonasikan untuk <strong>Bantu Anak Sekolah di Pelosok Indonesia</strong> oleh Budi Doremi.</p>
            <form>
                <label for="nama">Nama Lengkap</label>
                <input type="text" id="nama" name="nama" required>
                <br>

                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
                <br>

                <label for="amount">Nominal Donasi</label>
                <input type="number" id="amount" name="amount" min="1" required>
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