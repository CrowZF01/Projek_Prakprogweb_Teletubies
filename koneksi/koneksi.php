<?php
$host = "localhost:3310";
$user = "root";
$password = "admin123";
$database = "crowdfunding";

$koneksi = mysqli_connect($host, $user, $password, $database);
if (!$koneksi) {
    die("Gagal terhubung". mysqli_connect_error());
}

?>