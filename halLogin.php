<?php
session_start();
// Pastikan path koneksi ini benar sesuai folder kamu
require "koneksi/koneksi.php";

if (isset($_SESSION["id"]) && $_SESSION["role"] != "guest") {
  if ($_SESSION["role"] == "manager") {
    header("location:halPengelola.php");
  } else {
    header("location:halUtama.php");
  }
  exit();
}

if (isset($_POST["guest"])) {
  $_SESSION["role"] = "guest";
  $_SESSION["id"] = "0";
  $_SESSION["nama_user"] = "Guest";
  header("location:halUtama.php");
  exit();
}

if (isset($_POST["login"])) {
  $username = mysqli_real_escape_string($koneksi, $_POST["user"]);
  $password = mysqli_real_escape_string($koneksi, $_POST["pass"]);
  $role = $_POST["role"];

  $query = mysqli_query($koneksi, "SELECT * FROM user WHERE username = '$username' AND password = '$password' AND role = '$role'");

  if (mysqli_num_rows($query) > 0) {
    $data = mysqli_fetch_assoc($query);
    $_SESSION["id"] = $data["id"];
    $_SESSION["role"] = $data["role"];
    $_SESSION["nama_user"] = $data["username"];

    if ($_SESSION["role"] == "manager") {
      header("location:halPengelola.php");
    } else {
      header("location:halUtama.php");
    }
    exit();
  } else {
    $error = "Username atau Password salah!";
  }
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login - Teletubies</title>
  <!-- Font Playful -->
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles/styleLogin.css">

</head>

<body>

  <header>
    <a href="halUtama.php" class="logo-box">
      <!-- Pastikan path img/T.png ini benar -->
      <img src="logo/T.png" alt="T" class="logo-img">
    </a>
  </header>

  <main>
    <section class="login-card">
      <h1>Selamat Datang</h1>
      <p>Silahkan masuk ke akun Anda</p>

      <?php if (isset($error)): ?>
        <div class="error-box"><?php echo $error; ?></div>
      <?php endif; ?>

      <form action="" method="post">
        <div class="form-group">
          <label>Username / Email</label>
          <input type="text" name="user" required placeholder="Contoh: budi123">
        </div>

        <div class="form-group">
          <label>Password</label>
          <input type="password" name="pass" required placeholder="••••••••">
        </div>

        <div class="form-group">
          <label>Masuk Sebagai:</label>
          <div class="role-container">
            <div class="role-item">
              <input type="radio" name="role" value="donor" id="donor" checked>
              <label for="donor" class="role-label">Donatur</label>
            </div>
            <div class="role-item">
              <input type="radio" name="role" value="manager" id="manager">
              <label for="manager" class="role-label">Pengelola</label>
            </div>
          </div>
        </div>

        <button type="submit" name="login" class="btn-login">Masuk Sekarang</button>

        <div class="divider">
          <span>atau</span>
        </div>

        <button type="submit" name="guest" formnovalidate class="btn-guest">
          Masuk sebagai GUEST
        </button>
      </form>
    </section>
  </main>

  <footer>
    &copy; <?php echo date("Y"); ?> Teletubies Inc.
  </footer>

</body>

</html>