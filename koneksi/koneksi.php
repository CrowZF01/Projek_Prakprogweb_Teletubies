<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "crowdfunding";

$koneksi = mysqli_connect($host, $user, $password, $database);
if (!$koneksi) {
    die("Gagal terhubung". mysqli_connect_error());
}

?>