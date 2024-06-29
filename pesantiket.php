<!DOCTYPE HTML>
<html>
<head>
    <title>U-Bus | Pesan Tiket</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <style>
        body {
            background: rgb(43, 159, 220);
            background: radial-gradient(circle, rgba(43, 159, 220, 1) 0%, rgba(0, 179, 237, 1) 93%, rgba(0, 212, 255, 1) 100%);
            font-family: 'Josefin Sans', sans-serif;
        }

        .top {
            margin-top: 10px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-md col-sm-3 text-left mt-4">
            <h2>Silahkan Pilih Rute</h2>
        </div>
        <div class="col-md col-sm-3 mt-4 text-right">
            <p><button type="button" class="btn btn-info" onclick="location.href='beranda.php'">< Kembali ke beranda</button></p>
        </div>
    </div>
    <ul class="nav nav-tabs">
        <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#today">Hari ini</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#tomorrow">Besok</a>
        </li>
    </ul>
    <div class="tab-content">
        <div id="today" class="tab-pane fade show active">
            <h4>Jadwal Bis Hari Ini:</h4>
            <?php
            createTable("CURDATE()");
            ?>
        </div>
        <div id="tomorrow" class="tab-pane fade">
            <h4>Jadwal Bis Besok:</h4>
            <?php
            createTable("CURDATE() + INTERVAL 1 DAY");
            ?>
        </div>
    </div>
</div>

<?php
function createTable($tanggalQuery)
{
    require 'koneksi.php';
    $u = $_SESSION['penggunaID'];
    $sql = "SELECT kategoriID FROM pengguna WHERE penggunaID='$u';";
    $result = $koneksi->query($sql);
    $row = $result->fetch_assoc();
    $userType = $row['kategoriID'];
    $sql_instance = "SELECT * FROM bus JOIN rute ON rute.ruteID=bus.ruteID WHERE tglBerangkat = $tanggalQuery ORDER BY bus.wktBerangkat ASC;";
    $result = $koneksi->query($sql_instance);
    if (!$result) {
        trigger_error('Invalid query: ' . $koneksi->error);
    }
    if ($result->num_rows > 0) {
        echo '<div class="table-responsive-sm">
                <table class="table">
                    <thead>
                      <tr>
                        <th>Jumlah Kursi</th>
                        <th>Tanggal Berangkat</th>
                        <th>Waktu Keberangkatan</th>
                        <th>Asal</th>
                        <th>Tujuan</th>
                        <th>Waktu Tiba</th>
                        <th>Pesan Tiket</th>
                      </tr>
                    </thead>
                    <tbody>';
        // output data per baris
        while ($row = $result->fetch_assoc()) {
            echo '<tr>
                    <td>' . $row["jumlah_kursi"] . '</td>
                    <td>' . $row["tglBerangkat"] . '</td>
                    <td>' . $row["wktBerangkat"] . '</td>
                    <td>' . $row["asal"] . '</td>
                    <td>' . $row["tujuan"] . '</td>
                    <td>' . $row["wktTiba"] . '</td>';
            if ($row['jumlah_kursi'] > 15) {
                echo '<td><a href="simpan-tiket.php?bis=' . $row["busID"] . '" class="btn btn-success" role="button">Pesan</a></td>
                  </tr>';
            } elseif ($row['jumlah_kursi'] > 0) {
                echo '<td><a href="simpan-tiket.php?bis=' . $row["busID"] . '" class="btn btn-warning" role="button">Pesan</a></td>
                  </tr>';
            } else {
                echo '<td><button class="btn btn-danger disabled" disabled>Habis</button></td>
                  </tr>';
            }
        }
        echo '</tbody> </table> </div>';
    } else {
        echo '<p>Tidak ada jadwal untuk hari ini.</p>';
    }
}
?>
</body>
</html>
