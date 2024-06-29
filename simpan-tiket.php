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
$bid = $_GET['bis'];

// Query untuk mengecek jumlah tiket yang sudah dipesan pada hari ini dan besok
$sql_check_tickets = "SELECT COUNT(*) AS jumlah_tiket FROM tiket WHERE penggunaID = ? AND (tglBerangkat = CURDATE() OR tglBerangkat = CURDATE() + INTERVAL 1 DAY)";
$stmt_check_tickets = $koneksi->prepare($sql_check_tickets);
$stmt_check_tickets->bind_param("i", $userID);
$stmt_check_tickets->execute();
$stmt_check_tickets->bind_result($jumlah_tiket);
$stmt_check_tickets->fetch();
$stmt_check_tickets->close();

// Batasi pemesanan tiket hanya jika jumlah tiket yang sudah dipesan lebih dari 3
if ($jumlah_tiket >= 4) {
    redirect("pesantiket.php?alert=0");
    Limit();
    exit;
}

// Ambil data kursi yang tersedia
$sql_select_seat = "SELECT noKursi FROM tiket WHERE busID = ? AND penggunaID IS NULL LIMIT 1";
$stmt_select_seat = $koneksi->prepare($sql_select_seat);
$stmt_select_seat->bind_param("i", $bid);
$stmt_select_seat->execute();
$stmt_select_seat->bind_result($noKursi);
$stmt_select_seat->fetch();
$stmt_select_seat->close();

// Jika tidak ada kursi yang tersedia, beri pesan kesalahan atau redirect ke halaman lain
if (empty($noKursi)) {
    echo "Maaf, tidak ada kursi kosong yang tersedia.";
    exit;
}

// Mulai transaksi
$koneksi->autocommit(FALSE); // Matikan autocommit

// Update tiket dengan penggunaID
$sql_update_ticket = "UPDATE tiket SET penggunaID = ? WHERE busID = ? AND noKursi = ?";
$stmt_update_ticket = $koneksi->prepare($sql_update_ticket);
$stmt_update_ticket->bind_param("iii", $userID, $bid, $noKursi);
$update_success = $stmt_update_ticket->execute();
$stmt_update_ticket->close();

// Update jumlah kursi di bus
$sql_update_seat = "UPDATE bus SET jumlah_kursi = jumlah_kursi - 1 WHERE busID = ?";
$stmt_update_seat = $koneksi->prepare($sql_update_seat);
$stmt_update_seat->bind_param("i", $bid);
$update_seat_success = $stmt_update_seat->execute();
$stmt_update_seat->close();

if ($update_success && $update_seat_success) {
    $koneksi->commit(); // Commit transaksi jika kedua update berhasil
    redirect("tiket.php?seat={$noKursi}&bis={$bid}");
} else {
    $koneksi->rollback(); // Rollback jika ada kesalahan
    echo "Maaf, terjadi masalah dalam pemesanan tiket.";
    Fail();
}

// Fungsi redirect
function redirect($url)
{
    header("Location: {$url}");
    exit;
}

// Fungsi untuk pesan kesalahan
function Fail()
{
    echo "Maaf, terjadi masalah dalam pemesanan tiket.";
}

// Fungsi batasan pemesanan tiket harian
function Limit()
{
    echo "Maaf, kamu telah mencapai batas pemesanan tiket harian!";
}
?>
