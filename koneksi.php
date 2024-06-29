<?php
// konfigurasi database
session_start();
$host       =   "localhost";
$user       =   "root";
$password   =   "";
$database   =   "umn-bus-baru";

// perintah php untuk akses ke database
$koneksi = mysqli_connect($host, $user, $password, $database);

// cek koneksi
if (mysqli_connect_errno()) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>

