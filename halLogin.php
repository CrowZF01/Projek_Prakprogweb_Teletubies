<?php
session_start();
require "koneksi/koneksi.php";

if (isset($_SESSION["id"]) && $_SESSION["role"] != "guest") {
  // Jika sudah login, cek rolenya untuk redirect ke halaman yg tepat
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

if (isset($_POST["user"]) && isset($_POST["pass"])) {
  $username = $_POST["user"];
  $password = $_POST["pass"];
  $role = $_POST["role"];
  $query = mysqli_query($koneksi, "SELECT * FROM user WHERE username = '$username' AND password = '$password' AND role = '$role'");

  if (mysqli_num_rows($query) > 0) {
    $data = mysqli_fetch_assoc($query);
    $_SESSION["id"] = $data["id"];
    $_SESSION["role"] = $data["role"];
    $_SESSION["nama_user"] = $data["username"];

    // Redirect berdasarkan role
    if ($_SESSION["role"] == "manager") {
      header("location:halPengelola.php");
    } else {
      header("location:halUtama.php");
    }
    exit();
  } else {
    $error = "Username atau Password salah";
  }
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login - Crowdfunding</title>
  <link rel="stylesheet" href="styles/styleLogin.css" />
</head>

<body>
  <header>
    <div class="logo-container">
      <a href="halUtama.php">
        <img src="img/T.png" alt="Logo" class="main-logo">
      </a>
    </div>
  </header>

  <main>
    <section class="login-card">
      <div class="login-header">
        <h1>Selamat Datang</h1>
        <p>Silahkan masuk ke akun Anda</p>
      </div>

      <form action="" method="post">
        <div class="input-group">
          <label for="user">Username / Email</label>
          <input type="text" id="user" name="user" required placeholder="Masukkan username atau email">
        </div>

        <div class="input-group">
          <label for="pass">Password</label>
          <input type="password" id="pass" name="pass" required placeholder="Masukkan password">
        </div>

        <div class="role-selection">
          <p class="label-text">Masuk Sebagai:</p>
          <div class="radio-group">
            <label class="radio-container">
              <input type="radio" name="role" value="donor" checked>
              <span class="custom-radio">Donatur</span>
            </label>
            <label class="radio-container">
              <input type="radio" name="role" value="manager">
              <span class="custom-radio">Pengelola</span>
            </label>
          </div>
        </div>

        <button type="submit" name="login" class="btn-login">Masuk</button>

        <?php if (isset($error)) {
          echo "<div class='error-msg'>$error</div>";
        }
        ?>

        <div class="guest-wrapper">
          <button type="submit" name="guest" formnovalidate class="btn-guest">
            Hanya mau lihat-lihat? <span>Masuk sebagai Guest</span>
          </button>
        </div>
      </form>
    </section>
  </main>

  <footer>
    <p>&copy; <?php echo date("Y"); ?> Crowdfunding Platform</p>
  </footer>
</body>

</html>