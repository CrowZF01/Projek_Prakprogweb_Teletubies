<?php
$host = "127.0.0.1";
$user = "root";
$password = "";
$database = "crowdfunding";

$koneksi = mysqli_connect($host, $user, $password, $database);
if (!$koneksi) {
    die("Gagal terhubung". mysqli_connect_error());
}

?>