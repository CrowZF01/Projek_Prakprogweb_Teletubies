<?php 
session_start();
require "koneksi/koneksi.php"; 


if(isset($_SESSION["id"])){
    header("location:halUtama.php");
    exit();
}

if(isset($_POST["user"]) && isset($_POST["pass"])){
  $username = $_POST["user"];
  $password = $_POST["pass"];
  $role = $_POST["role"];
  $query = mysqli_query($koneksi, "SELECT * FROM user WHERE username = '$username' AND password = '$password' AND role = '$role'");

  if(mysqli_num_rows($query) > 0){
    $data = mysqli_fetch_assoc($query);
    $_SESSION["id"] = $data["id"];
    $_SESSION["role"] = $data["role"];

    header("location:halUtama.php");
    exit();
  } else{
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
      <div class="logo">
        <a href="halUtama.php">
          <img src="img/T.png" alt="Klik gambar ini" />
        </a>
      </div>
    </header>

   <main> 
        <section class="login-container">
            <h1>Login</h1>
            <form action="" method="post">
                <p class="user">Username / Email
                    <input type="text" id="user" name="user" required placeholder="Masukkan username atau email">
                </p>

                <p class="pass">Password
                    <input type="password" id="pass" name="pass" required placeholder="Masukkan password">
                </p>
                    <legend>Jenis pengguna</legend>
                    <label><input type="radio" name="role" value="donor" checked> Donatur</label>
                    <label><input type="radio" name="role" value="manager"> Pengelola Kampanye</label>
                
                <button type="submit">Masuk</button>

                <?php if(isset($error)) { 
                  echo "<p class='eror'>$error</p>";
                  } 
                ?>
                
            </form>
        </section>
    </main> 

    <footer>
    </footer>
  </body>
</html>