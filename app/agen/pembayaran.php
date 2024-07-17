<?php
include '../config/koneksi.php'; // untuk koneksi ke database
include '../library/fungsi.php'; // untuk memasukan library

session_start(); // untuk menampung session
date_default_timezone_set("Asia/Jakarta"); // untuk mengatur zona waktu

$aksi = new oop(); // untuk memanggil class di library

// tampung us & pw agar dibaca string bukan syntax
@$username = mysqli_real_escape_string($koneksi, $_POST['username']);
@$password = mysqli_real_escape_string($koneksi, $_POST['password']);

// jika session username petugas tidak kosong, pindah ke halaman utama
if (@$_SESSION['username_agen'] != "") {
    $aksi->redirect("hal_utama.php?menu=home");
}

// jika tekan login maka menjalankan fungsi login dari library 
if (isset($_POST['login'])) {
    $aksi->login("agen", $username, $password, "hal_utama.php?menu=home");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>FORM LOGIN SETROOM PAYMENT</title>
    <link rel="stylesheet" type="text/css" href="../css/bootstrap.css">
</head>
<body style="background:url('../images/bg_agen.jpg');">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="col-md-3"></div>
                <div class="col-md-6">
                    <div class="panel panel-default">
                        <!-- judul -->
                        <div class="panel-heading">
                            <div style="margin-top: 5px;margin-bottom: 5px;">
                                <img src="../images/logo_setroom.png" alt="logo" class="logo" width="90px">
                            </div>
                            <div style="margin-left: 110px; margin-top: -90px; font-size: 120%;">
                                A P L I K A S I &nbsp; P E M B A Y A R A N &nbsp; 
                                <br>
                                L I S T R I K &nbsp; P A S C A B A Y A R
                            </div>
                            <div style="margin-left: 110px; font-size: 200%;">
                                <strong>FORM LOGIN</strong>
                            </div>
                        </div>
                        <!-- end judul -->

                        <!-- isi -->
                        <div class="panel-body">
                            <div class="col-md-12">
                                <form method="post">
                                    <div class="form-group">
                                        <label>USERNAME</label>
                                        <input type="text" name="username" class="form-control" placeholder="Masukan username Anda..." required maxlength="30" autocomplete="off">
                                    </div>
                                    <div class="form-group">
                                        <label>PASSWORD</label>
                                        <input type="password" name="password" class="form-control" placeholder="Masukan password Anda..." required maxlength="30" autocomplete="off">
                                    </div>
                                    <div class="form-group">
                                        <input type="submit" name="login" class="btn btn-default btn-block btn-lg" value="LOGIN">
                                    </div>
                                </form>
                            </div>
                        </div>
                        <!-- end isi -->

                        <!-- footer -->
                        <div class="panel-footer">
                            <center>&copy;<?php echo date("Y"); ?> -  Defri</center>
                        </div>
                        <!-- end footer -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<?php  
if (!isset($_GET['menu'])) {
    header('location:hal_utama.php?menu=pembayaran');
}

// dasar
$table = "pembayaran";
$id = @$_GET['id'];
$where = " id_pembayaran = '$id'";
$redirect = "?menu=pembayaran";

// kode otomatis
$hari_ini = date("Ymd");
$sql = mysqli_query($koneksi, "SELECT id_pembayaran FROM pembayaran WHERE id_pembayaran LIKE '%$hari_ini%' order by id_pembayaran DESC");
$cek = mysqli_fetch_array($sql);
if (empty($cek)) {
    $id_pembayaran = "BYR".$hari_ini."0001";
} else {
    $kode = substr($cek['id_pembayaran'], 12, 4) + 1;
    if ($kode < 10) {
        $id_pembayaran = "BYR".$hari_ini."000".$kode;
    } elseif ($kode < 100) {
        $id_pembayaran = "BYR".$hari_ini."00".$kode;
    } elseif ($kode < 1000) {
        $id_pembayaran = "BYR".$hari_ini."0".$kode;
    } else {
        $id_pembayaran = "BYR".$hari_ini."".$kode;
    }
}
// end kode otomatis

@$id_pelanggan = $_POST['id_pelanggan'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>PEMBAYARAN</title>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="col-md-3"></div>
                <div class="col-md-6">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">INPUT PEMBAYARAN - <?php echo $id_pembayaran; ?></div>
                            <div class="panel-body">
                                <form method="post">
                                    <label>ID PELANGGAN</label>
                                    <div class="input-group">
                                        <input type="text" name="id_pelanggan" class="form-control" value="<?php if(@$_GET['id_pelanggan']==""){echo @$id_pelanggan; }else{echo $_GET['id_pelanggan'];}?>" placeholder="Masukan ID Pelanggan ...." onkeypress='return event.charCode >=48 && event.charCode <=57' list="list">
                                        <datalist id="list">
                                            <?php  
                                            $a = mysqli_query($koneksi, "SELECT * FROM pelanggan");
                                            while ($b = mysqli_fetch_array($a)) { ?>
                                                <option value="<?php echo $b['id_pelanggan'] ?>"><?php echo $b['nama']; ?></option>
                                            <?php } ?>
                                        </datalist>
                                        <div class="input-group-btn">
                                            <button type="submit" name="bcari_id" class="btn btn-primary"><span class="glyphicon glyphicon-search"></span>&nbsp;CARI</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php  
            if (isset($_POST['bcari_id'])) {
                $pelanggan = $aksi->caridata("pelanggan WHERE id_pelanggan = '$id_pelanggan'");    
                $tagihan = $aksi->cekdata("tagihan WHERE id_pelanggan = '$id_pelanggan' AND status ='Belum Bayar'");    
                $tarif = $aksi->caridata("tarif WHERE id_tarif = '$pelanggan[id_tarif]'");

                if ($pelanggan == "") {
                    echo "<div class='col-md-12'><center><h2>ID PELANGGAN TIDAK DITEMUKAN</h2></center></div>";
                } elseif ($tagihan == 0) {
                    $aksi->pesan("ID Pelanggan Tidak Memiliki Tunggakan Tagihan");
                } else {
            ?>
            
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <center>DETAIL TAGIHAN -  <?php echo $id_pelanggan." - ".$pelanggan['nama']; ?> </div>
                    <div class="panel-body">
                        <div class="col-md-12">
                            <div class="col-md-3">
                                <center><label>Detail Pelanggan -  <?php echo $pelanggan['nama'] ; ?></label></center>
                                <table class="table table-striped table-hover" align="center">
                                    <tr>
                                        <td align="right">ID Pelanggan</td>
                                        <td width="5%">:</td>
                                        <td align="left"><?php echo $pelanggan['id_pelanggan']; ?></td>
                                    </tr>
                                    <tr>
                                        <td align="right">Nama</td>
                                        <td width="5%">:</td>
                                        <td align="left"><?php echo $pelanggan['nama']; ?></td>
                                    </tr>
                                    <tr>
                                        <td align="right">No.Meter</td>
                                        <td width="5%">:</td>
                                        <td align="left"><?php echo $pelanggan['no_meter']; ?></td>
                                    </tr>
                                    <tr>
                                        <td align="right">Alamat</td>
                                        <td width="5%">:</td>
                                        <td align="left"><?php echo $pelanggan['alamat']; ?></td>
                                    </tr>
                                    <tr>
                                        <td align="right">Tarif</td>
                                        <td width="5%">:</td>
                                        <td align="left"><?php echo $tarif['kode_tarif']."<br>"; $aksi->rupiah($tarif['tarif_perkwh']); ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-9">
                                <table class="table table-bordered table-striped table-hover">
                                    <tr>
                                        <th><center>No</center></th>
                                        <th><center>ID Tagihan</center></th>
                                        <th><center>Bulan Tagihan</center></th>
                                        <th><center>Meter Awal</center></th>
                                        <th><center>Meter Akhir</center></th>
                                        <th><center>Jumlah Meter Pemakaian</center></th>
                                        <th><center>Total Bayar</center></th>
                                        <th><center>Status</center></th>
                                    </tr>
                                    <?php  
                                    $no = 0;
                                    $cari = mysqli_query($koneksi, "SELECT * FROM tagihan WHERE id_pelanggan = '$id_pelanggan' AND status = 'Belum Bayar'");
                                    while ($data = mysqli_fetch_array($cari)) {
                                        $jumlah_meter = $data['meter_akhir'] - $data['meter_awal'];
                                        $total_bayar = $jumlah_meter * $tarif['tarif_perkwh'];
                                        $no++;
                                    ?>
                                    <tr>
                                        <td align="center"><?php echo $no; ?></td>
                                        <td align="center"><?php echo $data['id_tagihan']; ?></td>
                                        <td align="center"><?php echo $data['bulan']."-".$data['tahun']; ?></td>
                                        <td align="center"><?php echo $data['meter_awal']; ?></td>
                                        <td align="center"><?php echo $data['meter_akhir']; ?></td>
                                        <td align="center"><?php echo $jumlah_meter; ?></td>
                                        <td align="right"><?php echo $aksi->rupiah($total_bayar); ?></td>
                                        <td align="center"><?php echo $data['status']; ?></td>
                                    </tr>
                                    <?php } ?>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php } }?>
        </div>
    </div>
</body>
</html>
