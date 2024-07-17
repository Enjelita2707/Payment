<?php
include '../config/koneksi.php';
include '../library/fungsi.php';

date_default_timezone_set("Asia/Jakarta");
session_start();

$aksi = new oop();

// Function to sanitize output for Excel file
function sanitizeForExcel($str) {
    return htmlspecialchars_decode(strip_tags($str));
}

if (isset($_GET['tarif'])) {
    $table = "tarif";
    $cari = "";
    $judul = "LAPORAN DAFTAR TARIF";
    $filename = $judul;

    if (isset($_GET['excel'])) {
        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=" . sanitizeForExcel($filename) . ".xls");
    }
} elseif (isset($_GET['pelanggan'])) {
    $table = "pelanggan";
    $cari = "";
    $judul = "LAPORAN DAFTAR PELANGGAN";
    $filename = $judul;

    if (isset($_GET['excel'])) {
        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=" . sanitizeForExcel($filename) . ".xls");
    }
} elseif (isset($_GET['agen'])) {
    $table = "agen";
    $cari = "";
    $judul = "LAPORAN DAFTAR AGEN";
    $filename = $judul;

    if (isset($_GET['excel'])) {
        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=" . sanitizeForExcel($filename) . ".xls");
    }
} elseif (isset($_GET['tagihan_bulan'])) {
    $status = $_GET['status'];
    $bulan = $_GET['bulan'];
    $tahun = $_GET['tahun'];
    $table = "qw_tagihan";
    $cari = "WHERE status = ? AND bulan = ? AND tahun = ?";
    $params = array($status, $bulan, $tahun);
    $judul = "LAPORAN TAGIHAN " . strtoupper($status) . " BULAN $bulan TAHUN $tahun";
    $filename = $judul;

    if (isset($_GET['excel'])) {
        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=" . sanitizeForExcel($filename) . ".xls");
    }
} elseif (isset($_GET['tunggakan'])) {
    $table = "pelanggan";
    $cari = "";
    $judul = "LAPORAN PELANGGAN YANG MEMILIKI TUNGGAKAN LEBIH DARI 3 BULAN";
    $filename = $judul;

    if (isset($_GET['excel'])) {
        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=" . sanitizeForExcel($filename) . ".xls");
    }
} elseif (isset($_GET['riwayat_penggunaan'])) {
    $table = "qw_tagihan";
    $id_pelanggan = $_GET['id_pelanggan'];
    $tahun = $_GET['tahun'];
    $pelanggan = $aksi->caridata("pelanggan WHERE id_pelanggan = ?", array($id_pelanggan));

    $cari = "WHERE id_pelanggan = ? AND tahun = ?";
    $params = array($id_pelanggan, $tahun);

    $judul = "LAPORAN RIWAYAT PENGGUNNAN " . strtoupper($pelanggan['nama']) . " ($id_pelanggan) PADA TAHUN $tahun";
    $filename = $judul;

    if (isset($_GET['excel'])) {
        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=" . sanitizeForExcel($filename) . ".xls");
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>PRINT LAPORAN</title>
    <style type="text/css">
        #footer {
            position: absolute;
            bottom: 1px;
            padding-right: 100px;
            padding-left: 20px;
            width: 100%;
            font-weight: bold;
            color: black;
            font: 13px Arial;
        }
    </style>
</head>
<body style="font-family: 'Arial', 'Lucida Grande', 'Lucida Sans Unicode', 'Lucida Sans', Tahoma, sans-serif;padding: 10px 10px;">
<!-- INI BAGIAN HEADER LAPORAN -->
<table width="100%" border="0" cellspacing="0">
    <tr>
        <?php if (!isset($_GET['excel'])) { ?>
            <td style="margin-top: -20px;" width="15%" valign="top">
                <img src="../images/logo_pln.png" width="90px" height="90px">
            </td>
        <?php } ?>
        <td>
            <h4 style="margin-top: 10px;margin-left: -10px;">PERUSAHAAN LISTRIK MILIK NEGARA</h4>
            <h1 style="margin-top: -20px;margin-left: -10px;">PT. PLN PERSERO</h1>
            <h5 style="margin-top: -20px;margin-left: -10px;">Kota Baru, Kota Jambi</h5>
        </td>
    </tr>
    <tr>
        <td colspan="3"><hr style="border: 2px solid black;"></td>
    </tr>
    <tr>
        <td colspan="3">
            <center>
                <strong>
                    <h3>
                        <?php
                        if (isset($_GET['tagihan_bulan'])) {
                            echo "LAPORAN TAGIHAN " . strtoupper($status) . " BULAN ";
                            $aksi->bulan($bulan);
                            echo " TAHUN $tahun";
                        } else {
                            echo @$judul;
                        }
                        ?>
                    </h3>
                </strong>
            </center>
        </td>
    </tr>
</table>
<!-- INI END BAGIAN HEADER LAPORAN -->

<!-- INI ISI LAPORAN -->
<?php if (isset($_GET['tarif']) || isset($_GET['pelanggan']) || isset($_GET['agen']) || isset($_GET['tagihan_bulan']) || isset($_GET['tunggakan']) || isset($_GET['riwayat_penggunaan'])) { ?>
    <table width="100%" border="1" cellspacing="0" cellpadding="3">
        <thead>
        <?php if (isset($_GET['tarif'])) { ?>
            <th><center>No.</center></th>
            <th><center>Kode Tarif</center></th>
            <th><center>Golongan</center></th>
            <th><center>Daya</center></th>
            <th><center>Tarif/KWh</center></th>
        <?php } elseif (isset($_GET['pelanggan'])) { ?>
            <th><center>No.</center></th>
            <th><center>ID Pelanggan</center></th>
            <th><center>No.Meter</center></th>
            <th><center>Nama</center></th>
            <th><center>Alamat</center></th>
            <th><center>Tenggang</center></th>
            <th><center>Kode Tarif</center></th>
        <?php } elseif (isset($_GET['agen'])) { ?>
            <th width="5%"><center>No.</center></th>
            <th width="13%"><center>ID Agen</center></th>
            <th width="20%"><center>Nama</center></th>
            <th width="12%"><center>No.Telepon</center></th>
            <th><center>Alamat</center></th>
            <th width="12%"><center>Biaya Admin</center></th>
        <?php } elseif (isset($_GET['tagihan_bulan'])) { ?>
            <th><center>No.</center></th>
            <th><center>ID Pelanggan</center></th>
            <th><center>Nama Pelanggan</center></th>
            <th><center>Bulan</center></th>
            <th><center>Jumlah Meter</center></th>
            <th><center>Jumlah Bayar</center></th>
            <th><center>Status</center></th>
            <th><center>Petugas</center></th>
        <?php } elseif (isset($_GET['tunggakan'])) { ?>
            <th><center>No.</center></th>
            <th><center>ID Pelanggan</center></th>
            <th><center>Nama Pelanggan</center></th>
            <th><center>Alamat</center></th>
            <th><center>Banyak Tunggakan</center></th>
            <th><center>Bulan</center></th>
            <th><center>Total Meter</center></th>
            <th><center>Tarif/Kwh</center></th>
            <th><center>Total Tunggakan</center></th>
        <?php } elseif (isset($_GET['riwayat_penggunaan'])) { ?>
            <th><center>No.</center></th>
            <th><center>Bulan</center></th>
            <th><center>Tahun</center></th>
            <th><center>Jumlah Meter</center></th>
            <th><center>Jumlah Bayar</center></th>
            <th><center>Status</center></th>
        <?php } ?>
        </thead>
        <tbody>
        <?php
        $no = 1;
        $data = $aksi->tampil($table, $cari, $params);
        foreach ($data as $row) {
            ?>
            <tr>
                <?php if (isset($_GET['tarif'])) { ?>
                    <td><center><?= $no++ ?></center></td>
                    <td><center><?= $row['id_tarif'] ?></center></td>
                    <td><center><?= $row['golongan'] ?></center></td>
                    <td><center><?= $row['daya'] ?></center></td>
                    <td><center><?= number_format($row['tarif_perkwh']) ?></center></td>
                <?php } elseif (isset($_GET['pelanggan'])) { ?>
                    <td><center><?= $no++ ?></center></td>
                    <td><center><?= $row['id_pelanggan'] ?></center></td>
                    <td><center><?= $row['no_meter'] ?></center></td>
                    <td><center><?= $row['nama'] ?></center></td>
                    <td><center><?= $row['alamat'] ?></center></td>
                    <td><center><?= $row['tenggang'] ?></center></td>
                    <td><center><?= $row['id_tarif'] ?></center></td>
                <?php } elseif (isset($_GET['agen'])) { ?>
                    <td><center><?= $no++ ?></center></td>
                    <td><center><?= $row['id_agen'] ?></center></td>
                    <td><center><?= $row['nama'] ?></center></td>
                    <td><center><?= $row['no_telp'] ?></center></td>
                    <td><center><?= $row['alamat'] ?></center></td>
                    <td><center><?= number_format($row['biaya_admin']) ?></center></td>
                <?php } elseif (isset($_GET['tagihan_bulan'])) { ?>
                    <td><center><?= $no++ ?></center></td>
                    <td><center><?= $row['id_pelanggan'] ?></center></td>
                    <td><center><?= $row['nama'] ?></center></td>
                    <td><center><?= $row['bulan'] ?></center></td>
                    <td><center><?= number_format($row['jumlah_meter']) ?></center></td>
                    <td><center><?= number_format($row['jumlah_bayar']) ?></center></td>
                    <td><center><?= $row['status'] ?></center></td>
                    <td><center><?= $row['nama_petugas'] ?></center></td>
                <?php } elseif (isset($_GET['tunggakan'])) { ?>
                    <td><center><?= $no++ ?></center></td>
                    <td><center><?= $row['id_pelanggan'] ?></center></td>
                    <td><center><?= $row['nama'] ?></center></td>
                    <td><center><?= $row['alamat'] ?></center></td>
                    <td><center><?= $row['banyak_tunggakan'] ?></center></td>
                    <td><center><?= $row['bulan'] ?></center></td>
                    <td><center><?= number_format($row['total_meter']) ?></center></td>
                    <td><center><?= number_format($row['tarif_perkwh']) ?></center></td>
                    <td><center><?= number_format($row['total_tunggakan']) ?></center></td>
                <?php } elseif (isset($_GET['riwayat_penggunaan'])) { ?>
                    <td><center><?= $no++ ?></center></td>
                    <td><center><?= $row['bulan'] ?></center></td>
                    <td><center><?= $row['tahun'] ?></center></td>
                    <td><center><?= number_format($row['jumlah_meter']) ?></center></td>
                    <td><center><?= number_format($row['jumlah_bayar']) ?></center></td>
                    <td><center><?= $row['status'] ?></center></td>
                <?php } ?>
            </tr>
        <?php } ?>
        </tbody>
    </table>
<?php } ?>
<!-- INI END ISI LAPORAN -->

<!-- INI BAGIAN FOOTER LAPORAN -->
<div id="footer">
    <table width="100%" border="0" cellspacing="0">
        <tr>
            <td width="20%">
                <br>
                Jambi <?= date('d-m-Y') ?><br>
                <br>
                <br>
                <br>
                <br>
                <br>
                <u><?= $_SESSION['username'] ?></u><br>
                (Petugas)
            </td>
            <td width="80%">
                <table width="100%" border="0" cellspacing="0">
                    <tr>
                        <td align="center" colspan="2"><b>Disclaimer :</b></td>
                    </tr>
                    <tr>
                        <td align="center" colspan="2">
                            <p><i>Laporan ini hanya sebagai bukti transaksi</i></p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>
<!-- INI END BAGIAN FOOTER LAPORAN -->
</body>
</html>
