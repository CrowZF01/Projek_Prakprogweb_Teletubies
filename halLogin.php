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
  <title>Login - Teletu</title>
  <!-- Font Playful -->
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;700;800&display=swap" rel="stylesheet">

  <style>
    :root {
      --primary-green: #25754a;
      --accent-yellow: #fbd24b;
      --dark: #1a1a1a;
      --bg: #f9f9f1;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background-color: var(--bg);
      /* Efek titik-titik (dot pattern) */
      background-image: radial-gradient(var(--dark) 1px, transparent 0);
      background-size: 24px 24px;
      background-attachment: fixed;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    /* HEADER SESUAI GAMBAR */
    header {
      background-color: var(--primary-green);
      height: 85px;
      display: flex;
      align-items: center;
      padding: 0 5%;
      border-bottom: 4px solid var(--dark);
    }

    .logo-box {
      background-color: var(--accent-yellow);
      border: 3px solid var(--dark);
      border-radius: 10px;
      padding: 8px 20px;
      display: flex;
      align-items: center;
      text-decoration: none;
      box-shadow: 4px 4px 0px var(--dark);
    }

    .logo-img {
      height: 30px;
      /* Atur ukuran gambar T.png kamu */
      width: auto;
    }

    /* MAIN CONTENT */
    main {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px 20px;
    }

    .login-card {
      width: 100%;
      max-width: 400px;
      background: white;
      padding: 40px;
      border-radius: 25px;
      border: 4px solid var(--dark);
      box-shadow: 12px 12px 0px var(--dark);
      text-align: center;
    }

    .login-card h1 {
      font-weight: 800;
      font-size: 28px;
      margin-bottom: 10px;
      color: var(--dark);
    }

    .login-card p {
      font-weight: 600;
      color: #666;
      margin-bottom: 30px;
    }

    /* FORM STYLING */
    .form-group {
      text-align: left;
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      font-weight: 800;
      margin-bottom: 8px;
      font-size: 14px;
    }

    .form-group input {
      width: 100%;
      padding: 15px;
      border: 3px solid var(--dark);
      border-radius: 12px;
      font-family: inherit;
      font-weight: 700;
      outline: none;
      transition: 0.2s;
    }

    .form-group input:focus {
      background-color: #f0fdf4;
      box-shadow: 4px 4px 0px var(--primary-green);
    }

    /* RADIO BUTTON (Pill style) */
    .role-container {
      display: flex;
      gap: 10px;
      margin-bottom: 25px;
    }

    .role-item {
      flex: 1;
      position: relative;
    }

    .role-item input {
      display: none;
    }

    .role-label {
      display: block;
      padding: 12px;
      border: 3px solid var(--dark);
      border-radius: 50px;
      font-weight: 800;
      cursor: pointer;
      background: white;
      transition: 0.2s;
      font-size: 14px;
    }

    .role-item input:checked+.role-label {
      background-color: var(--primary-green);
      color: white;
      box-shadow: 4px 4px 0px var(--dark);
      transform: translateY(-2px);
    }

    /* BUTTONS */
    .btn-login {
      width: 100%;
      background-color: var(--primary-green);
      color: white;
      padding: 16px;
      border: 3px solid var(--dark);
      border-radius: 12px;
      font-size: 16px;
      font-weight: 800;
      cursor: pointer;
      box-shadow: 5px 5px 0px var(--dark);
      margin-bottom: 20px;
      font-family: inherit;
    }

    .btn-login:active {
      transform: translate(3px, 3px);
      box-shadow: 2px 2px 0px var(--dark);
    }

    .divider {
      margin: 20px 0;
      position: relative;
      border-top: 2px solid #ddd;
    }

    .divider span {
      position: absolute;
      top: -12px;
      left: 50%;
      transform: translateX(-50%);
      background: white;
      padding: 0 10px;
      font-size: 12px;
      font-weight: 800;
      color: #aaa;
      text-transform: uppercase;
    }

    .btn-guest {
      width: 100%;
      background-color: var(--accent-yellow);
      border: 3px solid var(--dark);
      border-radius: 12px;
      padding: 14px;
      font-family: inherit;
      font-weight: 800;
      cursor: pointer;
      box-shadow: 4px 4px 0px var(--dark);
    }

    .error-box {
      background: #fee2e2;
      border: 3px solid #b91c1c;
      padding: 10px;
      border-radius: 10px;
      margin-bottom: 20px;
      color: #b91c1c;
      font-weight: 800;
      font-size: 13px;
    }

    footer {
      padding: 20px;
      text-align: center;
      font-weight: 700;
      color: #888;
    }
  </style>
</head>

<body>

  <header>
    <a href="halUtama.php" class="logo-box">
      <!-- Pastikan path img/T.png ini benar -->
      <img src="img/T.png" alt="T" class="logo-img">
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
    &copy; <?php echo date("Y"); ?> Crowdfunding Platform
  </footer>

</body>

</html>