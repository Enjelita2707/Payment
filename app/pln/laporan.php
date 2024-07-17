<?php
// Periksa jika sesi belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_GET['menu'])) {
    header('location:hal_utama.php?menu=laporan');
    exit(); // Tambahkan exit untuk menghentikan eksekusi skrip
}

$bulanini = $_POST['bulan'] ?? ''; // Gunakan operator penggabungan null untuk kode yang lebih aman
$tahunini = $_POST['tahun'] ?? '';

// Periksa apakah 'id_agen' ada dalam array $_SESSION
if (isset($_SESSION['id_agen'])) {
    $id_agen = $_SESSION['id_agen'];
    $cari = "WHERE MONTH(tgl_bayar) = '$bulanini' AND YEAR(tgl_bayar) ='$tahunini' AND id_agen = '$id_agen'";
} else {
    $cari = "";
    echo "ID Agen tidak ditemukan.";
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>LAPORAN</title>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">

        <!-- LAPORAN TARIF -->
        <?php  
        if (isset($_GET['tarif'])) { 
            $table = "tarif";
            $cari = "";
            $link_print = "print.php?tarif";
            $link_excel = "print.php?excel&tarif";
            $judul = "LAPORAN DAFTAR TARIF";
        ?>
            <div class="panel panel-default">
                <div class="panel-heading">
                    LAPORAN DAFTAR TARIF
                    <div class="pull-right">
                        <a href="<?php echo $link_print ?>" target="_blank"><div class="glyphicon glyphicon-print"></div>&nbsp;&nbsp;<label>CETAK</label></a>
                        &nbsp;&nbsp;
                        <a href="<?php echo $link_excel ?>" target="_blank"><div class="glyphicon glyphicon-floppy-save"></div>&nbsp;&nbsp;<label>EXPORT EXCEL</label></a>
                    </div>
                </div>
                <div class="panel-body">
                    <center><label><?php echo @$judul; ?></label></center>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <th><center>No.</center></th>
                                <th><center>Kode Tarif</center></th>
                                <th><center>Golongan</center></th>
                                <th><center>Daya</center></th>
                                <th><center>Tarif/KWh</center></th>
                            </thead>
                            <tbody>
                                <?php  
                                $no = 0;
                                $data = $aksi->tampil($table, $cari, "ORDER BY golongan ASC");
                                if ($data == "") {
                                    $aksi->no_record(5);
                                } else {
                                    foreach ($data as $r) {
                                        $no++;
                                ?>
                                <tr>
                                    <td align="center"><?php echo $no; ?>.</td>
                                    <td align="center"><?php echo $r['kode_tarif'] ?></td>
                                    <td align="center"><?php echo $r['golongan'] ?></td>
                                    <td align="center"><?php echo $r['daya'] ?></td>
                                    <td align="right"><?php $aksi->rupiah($r['tarif_perkwh']) ?></td>
                                </tr>
                                <?php 
                                    } 
                                } 
                                ?>
                             </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php 
        } elseif (isset($_GET['pelanggan'])) { 
            $table = "pelanggan";
            $cari = "";
            $link_print = "print.php?pelanggan";
            $link_excel = "print.php?excel&pelanggan";
            $judul = "LAPORAN DAFTAR PELANGGAN";
        ?>
            <div class="panel panel-default">
                <div class="panel-heading">
                    LAPORAN DAFTAR PELANGGAN
                    <div class="pull-right">
                        <a href="<?php echo $link_print ?>" target="_blank"><div class="glyphicon glyphicon-print"></div>&nbsp;&nbsp;<label>CETAK</label></a>
                        &nbsp;&nbsp;
                        <a href="<?php echo $link_excel ?>" target="_blank"><div class="glyphicon glyphicon-floppy-save"></div>&nbsp;&nbsp;<label>EXPORT EXCEL</label></a>
                    </div>
                </div>
                <div class="panel-body">
                    <center><label><?php echo @$judul; ?></label></center>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <th><center>No.</center></th>
                                <th><center>ID Pelanggan</center></th>
                                <th><center>No.Meter</center></th>
                                <th><center>Nama</center></th>
                                <th><center>Alamat</center></th>
                                <th><center>Tenggang</center></th>
                                <th><center>Kode Tarif</center></th>
                            </thead>
                            <tbody>
                                <?php  
                                $no = 0;
                                $data = $aksi->tampil($table, $cari, "ORDER BY id_pelanggan");
                                if ($data == "") {
                                    $aksi->no_record(9);
                                } else {
                                    foreach ($data as $r) {
                                        $a = $aksi->caridata("tarif WHERE id_tarif = '$r[id_tarif]'");
                                        $no++;
                                ?>
                                <tr>
                                    <td align="center"><?php echo $no; ?>.</td>
                                    <td align="center"><?php echo $r['id_pelanggan'] ?></td>
                                    <td align="center"><?php echo $r['no_meter'] ?></td>
                                    <td><?php echo $r['nama'] ?></td>
                                    <td><?php echo $r['alamat'] ?></td>
                                    <td align="center"><?php echo $r['tenggang'] ?></td>
                                    <td align="center"><?php echo $a['kode_tarif'] ?></td>
                                </tr>
                                <?php 
                                    } 
                                } 
                                ?>
                             </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php 
        } elseif (isset($_GET['agen'])) { 
            $table = "agen";
            $cari = "";
            $link_print = "print.php?agen";
            $link_excel = "print.php?excel&agen";
            $judul = "LAPORAN DAFTAR AGEN";
        ?>
            <div class="panel panel-default">
                <div class="panel-heading">
                    LAPORAN DAFTAR AGEN
                    <div class="pull-right">
                        <a href="<?php echo $link_print ?>" target="_blank"><div class="glyphicon glyphicon-print"></div>&nbsp;&nbsp;<label>CETAK</label></a>
                        &nbsp;&nbsp;
                        <a href="<?php echo $link_excel ?>" target="_blank"><div class="glyphicon glyphicon-floppy-save"></div>&nbsp;&nbsp;<label>EXPORT EXCEL</label></a>
                    </div>
                </div>
                <div class="panel-body">
                    <center><label><?php echo @$judul; ?></label></center>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <th width="5%"><center>No.</center></th>
                                <th width="13%"><center>ID Agen</center></th>
                                <th width="20%"><center>Nama</center></th>
                                <th width="12%"><center>No.Telepon</center></th>
                                <th><center>Alamat</center></th>
                                <th width="12%"><center>Biaya Admin</center></th>
                            </thead>
                            <tbody>
                                <?php  
                                $no = 0;
                                $a = $aksi->tampil($table, $cari, "ORDER BY id_agen DESC");
                                if ($a == "") {
                                    $aksi->no_record(7);
                                } else {
                                    foreach ($a as $r) {
                                        $cek = $aksi->cekdata("pembayaran WHERE id_agen = '$r[id_agen]'");
                                        $no++;
                                ?>
                                <tr>
                                    <td align="center"><?php echo $no; ?>.</td>
                                    <td align="center"><?php echo $r['id_agen']; ?></td>
                                    <td><?php echo $r['nama']; ?></td>
                                    <td align="center"><?php echo $r['no_telepon']; ?></td>
                                    <td><?php echo $r['alamat']; ?></td>
                                    <td align="right"><?php echo $aksi->rupiah($r['biaya_admin']) ?></td>
                                </tr>
                                <?php 
                                    } 
                                } 
                                ?>
                             </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php 
        } elseif (isset($_GET['tagihan'])) { 
        ?>
            <div class="panel panel-default">
                <div class="panel-heading">
                    LAPORAN TAGIHAN BULANAN
                    <div class="pull-right">
                        <a href="?menu=laporan&tagihan" style="color:black;text-decoration:none;"><div class="glyphicon glyphicon-refresh"></div>&nbsp;&nbsp;<label>REFRESH</label></a>
                    </div>
                </div>
                <div class="panel-body">
                    <form method="post">
                        <table>
                            <tr>
                                <td width="10%"><label>Bulan :</label></td>
                                <td width="15%">
                                    <select name="bulan" class="form-control">
                                        <option value="">--Pilih Bulan--</option>
                                        <?php  
                                            $bulan = array("01" => "JANUARI", "02" => "FEBRUARI", "03" => "MARET", "04" => "APRIL", "05" => "MEI", "06" => "JUNI", "07" => "JULI", "08" => "AGUSTUS", "09" => "SEPTEMBER", "10" => "OKTOBER", "11" => "NOVEMBER", "12" => "DESEMBER");
                                            foreach ($bulan as $key => $value) {
                                                if ($key == $bulanini) {
                                                    echo "<option value='$key' selected>$value</option>";
                                                } else {
                                                    echo "<option value='$key'>$value</option>";
                                                }
                                            }
                                        ?>
                                    </select>
                                </td>
                                <td width="10%"><label>Tahun :</label></td>
                                <td width="15%">
                                    <select name="tahun" class="form-control">
                                        <option value="">--Pilih Tahun--</option>
                                        <?php  
                                            for ($i = 2020; $i <= date('Y'); $i++) { 
                                                if ($i == $tahunini) {
                                                    echo "<option value='$i' selected>$i</option>";
                                                } else {
                                                    echo "<option value='$i'>$i</option>";
                                                }
                                            }
                                        ?>
                                    </select>
                                </td>
                                <td width="5%"><button name="bcari" class="btn btn-primary"><div class="glyphicon glyphicon-search"></div>&nbsp;&nbsp;Cari</button></td>
                                <td width="5%"><a href="?menu=laporan&tagihan"><button type="button" class="btn btn-success"><div class="glyphicon glyphicon-refresh"></div>&nbsp;&nbsp;Refresh</button></a></td>
                            </tr>
                        </table>
                    </form>
                    <br>
                    <div class="table-responsive">
                        <?php
                        $data = "";
                        if (isset($_POST['bcari'])) {
                            if ($_POST['bulan'] == "" || $_POST['tahun'] == "") {
                                $data = "kosong";
                            } else {
                                $_SESSION['bulan'] = $_POST['bulan'];
                                $_SESSION['tahun'] = $_POST['tahun'];
                                $cari = "WHERE MONTH(bulan) = '$_SESSION[bulan]' AND YEAR(bulan) = '$_SESSION[tahun]' AND status = 'BELUM BAYAR'";
                                $data = $aksi->tampil_sum("tagihan", "id_pelanggan, bulan, tahun, MONTH(bulan) AS bulan_tagih", $cari, "GROUP BY id_pelanggan, MONTH(bulan), YEAR(bulan)");
                            }
                        }
                        ?>
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th><center>No.</center></th>
                                    <th><center>ID Pelanggan</center></th>
                                    <th><center>Bulan</center></th>
                                    <th><center>Tahun</center></th>
                                    <th><center>Jumlah Tagihan</center></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php  
                                if ($data == "kosong") {
                                    echo "<tr><td colspan='5' align='center'>Silakan pilih bulan dan tahun terlebih dahulu!</td></tr>";
                                } elseif ($data != "") {
                                    $no = 1;
                                    foreach ($data as $r) {
                                        echo "
                                        <tr>
                                            <td align='center'>$no</td>
                                            <td align='center'>$r[id_pelanggan]</td>
                                            <td align='center'>".$bulan[$r['bulan_tagih']]."</td>
                                            <td align='center'>$r[tahun]</td>
                                            <td align='right'>".$aksi->rupiah($r['total'])."</td>
                                        </tr>
                                        ";
                                        $no++;
                                    }
                                } else {
                                    echo "<tr><td colspan='5' align='center'>Data tidak ditemukan!</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php 
        } 
        ?>
        </div>
    </div>
</div>
</body>
</html>
