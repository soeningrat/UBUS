<?php
require 'koneksi.php';
require 'components/header.php';

// Pastikan session dimulai sebelum memanipulasi $_SESSION
session_start();

if (!isset($_SESSION['penggunaID'])) {
    // Jika tidak ada session penggunaID, lakukan redirect atau berikan pesan kesalahan
    header("Location: login.php");
    exit;
}

$userID = $_SESSION['penggunaID'];
$bid = isset($_GET['bis']) ? $_GET['bis'] : null;
$seat = isset($_GET['seat']) ? $_GET['seat'] : null;

if (!$bid || !$seat) {
    echo "Parameter tidak lengkap.";
    exit;
}

// Mulai transaksi
$koneksi->autocommit(FALSE); // Matikan autocommit

// Update tabel tiket untuk membatalkan tiket
$sql_instance = "UPDATE tiket SET penggunaID = NULL WHERE penggunaID = ? AND busID = ? AND noKursi = ?";
$stmt_instance = $koneksi->prepare($sql_instance);
$stmt_instance->bind_param("iii", $userID, $bid, $seat);
$update_tiket_success = $stmt_instance->execute();
$stmt_instance->close();

// Update jumlah kursi di bus
$sql_seat = "UPDATE bus SET jumlah_kursi = jumlah_kursi + 1 WHERE busID = ?";
$stmt_seat = $koneksi->prepare($sql_seat);
$stmt_seat->bind_param("i", $bid);
$update_seat_success = $stmt_seat->execute();
$stmt_seat->close();

if ($update_tiket_success && $update_seat_success) {
    $koneksi->commit(); // Commit transaksi jika kedua update berhasil
    header('Location: pesantiket.php');
    exit;
} else {
    $koneksi->rollback(); // Rollback jika ada kesalahan
    echo "Gagal membatalkan tiket.";
}
?>
