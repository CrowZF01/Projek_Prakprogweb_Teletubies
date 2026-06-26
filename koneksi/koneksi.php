<?php
$host = "db"; // Menghubungkan ke service database 'db' di docker-compose
$user = "root";
$password = "rootpassword"; // Harus sama dengan MYSQL_ROOT_PASSWORD di docker-compose
$database = "crowdfunding";

$koneksi = mysqli_connect($host, $user, $password, $database);
if (!$koneksi) {
    die("Gagal terhubung". mysqli_connect_error());
}

?>
