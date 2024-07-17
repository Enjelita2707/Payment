<?php
if (!isset($_GET['menu'])) {
    header('location:hal_utama.php?menu=penggunaan');
}

// Database connection
$host = 'localhost'; // host database
$user = 'root'; // username database
$pass = ''; // password database
$db   = 'db_pln'; // nama database

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('Koneksi gagal: ' . $conn->connect_error);
}

// Dasar
$table = "penggunaan";
$id = $_GET['id'] ?? null;
$where = " id_penggunaan = ?";
$redirect = "?menu=penggunaan";

$id_pel = $_POST['id_pelanggan'] ?? null;
$id_petugas = $_SESSION['id_petugas'] ?? null;

if ($id_pel) {
    $stmt = $conn->prepare("SELECT * FROM penggunaan WHERE id_pelanggan = ? AND meter_akhir = '0'");
    $stmt->bind_param('s', $id_pel);
    $stmt->execute();
    $result = $stmt->get_result();
    $penggunaan = $result->fetch_assoc();
    if (!$penggunaan) {
        $aksi->pesan('Data Bulan ini sudah diinput');
    }
} elseif (isset($_GET['hapus']) || isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM penggunaan WHERE id_penggunaan = ?");
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $penggunaan = $result->fetch_assoc();
    $id_pel = $penggunaan['id_pelanggan'];
}

$pelanggan = null;
$tarif = null;
$tarif_perkwh = null;
$id_guna = $penggunaan['id_penggunaan'] ?? null;
$mawal = $penggunaan['meter_awal'] ?? null;
$bulan = $penggunaan['bulan'] ?? null;
$tahun = $penggunaan['tahun'] ?? null;

if ($id_pel) {
    $stmt = $conn->prepare("SELECT * FROM pelanggan WHERE id_pelanggan = ?");
    $stmt->bind_param('s', $id_pel);
    $stmt->execute();
    $result = $stmt->get_result();
    $pelanggan = $result->fetch_assoc();

    if ($pelanggan) {
        $stmt = $conn->prepare("SELECT * FROM tarif WHERE id_tarif = ?");
        $stmt->bind_param('s', $pelanggan['id_tarif']);
        $stmt->execute();
        $result = $stmt->get_result();
        $tarif = $result->fetch_assoc();
        $tarif_perkwh = $tarif['tarif_perkwh'];
    }
}

if ($bulan == 12) {
    $next_bulan = str_pad($bulan + 1, 2, '0', STR_PAD_LEFT);
    $next_tahun = $tahun + 1;
} else {
    $next_bulan = str_pad($bulan + 1, 2, '0', STR_PAD_LEFT);
    $next_tahun = $tahun;
}

$id_pelanggan = $_POST['id_pelanggan'] ?? null;
$meter_akhir = $_POST['meter_akhir'] ?? null;
$meter_awal = $_POST['meter_awal'] ?? null;
$tgl_cek = $_POST['tgl_cek'] ?? null;
$jumlah_meter = $meter_akhir - $mawal;
$jumlah_bayar = $jumlah_meter * $tarif_perkwh;
$id_penggunaan_next = $id_pel . $next_bulan . $next_tahun;

$field_next = [
    'id_penggunaan' => $id_penggunaan_next,
    'id_pelanggan' => $id_pelanggan,
    'bulan' => $next_bulan,
    'tahun' => $next_tahun,
    'meter_awal' => $meter_akhir,
];

$field = [
    'meter_akhir' => $meter_akhir,
    'tgl_cek' => $tgl_cek,
    'id_petugas' => $id_petugas,
];

$field_update = ['meter_awal' => $meter_akhir];

$field_tagihan = [
    'id_pelanggan' => $id_pelanggan,
    'bulan' => $bulan,
    'tahun' => $tahun,
    'jumlah_meter' => $jumlah_meter,
    'tarif_perkwh' => $tarif_perkwh,
    'jumlah_bayar' => $jumlah_bayar,
    'status' => "Belum Bayar",
    'id_petugas' => $id_petugas,
];

$field_tagihan_update = [
    'jumlah_meter' => $jumlah_meter,
    'tarif_perkwh' => $tarif_perkwh,
    'jumlah_bayar' => $jumlah_bayar,
    'status' => "Belum Bayar",
    'id_petugas' => $id_petugas,
];

if (isset($_POST['bsimpan'])) {
    if ($meter_akhir <= $meter_awal) {
        $aksi->pesan("Meter Akhir Tidak Mungkin Kurang dari Meter Awal");
    } else {
        $stmt = $conn->prepare("INSERT INTO tagihan (id_pelanggan, bulan, tahun, jumlah_meter, tarif_perkwh, jumlah_bayar, status, id_petugas) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssssssss', $id_pelanggan, $bulan, $tahun, $jumlah_meter, $tarif_perkwh, $jumlah_bayar, $field_tagihan['status'], $id_petugas);
        $stmt->execute();

        $stmt = $conn->prepare("UPDATE $table SET meter_akhir = ?, tgl_cek = ?, id_petugas = ? WHERE id_penggunaan = ?");
        $stmt->bind_param('ssss', $meter_akhir, $tgl_cek, $id_petugas, $id_guna);
        $stmt->execute();

        $stmt = $conn->prepare("INSERT INTO $table (id_penggunaan, id_pelanggan, bulan, tahun, meter_awal) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('sssss', $id_penggunaan_next, $id_pelanggan, $next_bulan, $next_tahun, $meter_akhir);
        $stmt->execute();

        $aksi->alert("Data Berhasil Disimpan", $redirect);
    }
}

if (isset($_POST['bubah'])) {
    $stmt = $conn->prepare("UPDATE $table SET meter_awal = ? WHERE id_penggunaan = ?");
    $stmt->bind_param('ss', $meter_akhir, $id_penggunaan_next);
    $stmt->execute();

    $stmt = $conn->prepare("UPDATE tagihan SET jumlah_meter = ?, tarif_perkwh = ?, jumlah_bayar = ?, status = ?, id_petugas = ? WHERE id_pelanggan = ? AND bulan = ? AND tahun = ?");
    $stmt->bind_param('ssssssss', $jumlah_meter, $tarif_perkwh, $jumlah_bayar, $field_tagihan_update['status'], $id_petugas, $id_pel, $bulan, $tahun);
    $stmt->execute();

    $stmt = $conn->prepare("UPDATE $table SET meter_akhir = ?, tgl_cek = ?, id_petugas = ? WHERE id_penggunaan = ?");
    $stmt->bind_param('ssss', $meter_akhir, $tgl_cek, $id_petugas, $id);
    $stmt->execute();

    $aksi->alert("Data Berhasil Diubah", $redirect);
}

if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM $table WHERE $where");
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit = $result->fetch_assoc();
}

if (isset($_GET['hapus'])) {
    $stmt = $conn->prepare("UPDATE penggunaan SET meter_akhir = 0, tgl_cek = '', id_petugas = '' WHERE id_penggunaan = ?");
    $stmt->bind_param('s', $id);
    $stmt->execute();

    $stmt = $conn->prepare("DELETE FROM penggunaan WHERE id_penggunaan = ?");
    $stmt->bind_param('s', $id_penggunaan_next);
    $stmt->execute();

    $stmt = $conn->prepare("DELETE FROM tagihan WHERE id_pelanggan = ? AND bulan = ? AND tahun = ?");
    $stmt->bind_param('sss', $id_pel, $bulan, $tahun);
    $stmt->execute();

    $aksi->alert("Data Berhasil Dihapus", $redirect);
}

$text = $_POST['tcari'] ?? '';
$cari = $text ? "WHERE id_pelanggan LIKE '%$text%' OR id_penggunaan LIKE '%$text%' OR meter_awal LIKE '%$text%' OR meter_akhir LIKE '%$text%' OR tahun LIKE '%$text%' OR nama_pelanggan LIKE '%$text%' OR nama_petugas LIKE '%$text%'" : "WHERE meter_akhir != 0";
?>
<!DOCTYPE html>
<html>
<head>
    <title>PELANGGAN</title>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="col-md-3"></div>
                <div class="col-md-6">
                    <div class="panel panel-default">
                        <?php if (!isset($_GET['id'])) { ?>
                            <div class="panel-heading">INPUT PENGGUNAAN</div>
                        <?php } else { ?>
                            <div class="panel-heading">UBAH PENGGUNAAN - <?php echo $id; ?></div>
                        <?php } ?>
                        <div class="panel-body">
                            <form method="post">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>ID PELANGGAN</label>&nbsp;&nbsp;<span style="color:blue;font-size: 10px;">[TEKAN TAB]</span>
                                        <input type="text" name="id_pelanggan" class="form-control" placeholder="Masukan ID Pelanggan" onchange="submit()" required value="<?php echo $id_pel ?? $edit['id_pelanggan'] ?? ''; ?>" list="id_pel" onkeypress='return event.charCode >= 48 && event.charCode <= 57' <?php echo isset($_GET['id']) ? "readonly" : ""; ?>>
                                        <datalist id="id_pel">
                                            <?php
                                            $result = $conn->query("SELECT * FROM pelanggan");
                                            while ($row = $result->fetch_assoc()) {
                                                echo '<option value="' . $row['id_pelanggan'] . '">' . $row['nama'] . '</option>';
                                            }
                                            ?>
                                        </datalist>
                                    </div>
                                    <div class="form-group">
                                        <label>BULAN PENGGUNAAN</label>
										<input type="text" name="no_meter" class="form-control" placeholder="Bulan penggunaan" required readonly value="<?php echo isset($bulan) && isset($tahun) ? $aksi->bulan($bulan) . ' ' . $tahun : (isset($edit['bulan']) && isset($edit['tahun']) ? $aksi->bulan($edit['bulan']) . ' ' . $edit['tahun'] : ''); ?>">
										</div>
                                    <div class="form-group">
                                        <label>METER AWAL</label>
                                        <input type="text" name="meter_awal" class="form-control" placeholder="Meter Awal" required readonly value="<?php echo $mawal ?? $edit['meter_awal'] ?? ''; ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>METER AKHIR</label>
                                        <input type="text" name="meter_akhir" class="form-control" placeholder="Masukan Meter Akhir" required value="<?php echo $edit['meter_akhir'] ?? ''; ?>" onkeypress='return event.charCode >= 48 && event.charCode <= 57'>
                                    </div>
                                    <div class="form-group">
                                        <label>TANGGAL PENGECEKAN</label>
                                        <input type="date" name="tgl_cek" class="form-control" placeholder="Masukan Nama" required value="<?php echo $edit['tgl_cek'] ?? ''; ?>">
                                    </div>

                                    <div class="form-group">
                                        <?php if (!isset($_GET['id'])) { ?>
                                            <input type="submit" name="bsimpan" class="btn btn-primary btn-lg btn-block" value="SIMPAN">
                                        <?php } else { ?>
                                            <input type="submit" name="bubah" class="btn btn-success btn-lg btn-block" value="UBAH">
                                        <?php } ?>

                                        <a href="?menu=penggunaan" class="btn btn-danger btn-lg btn-block">RESET</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="panel-footer">&nbsp;</div>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">DAFTAR PENGGUNAAN</div>
                    <div class="panel-body">
                        <div class="col-md-12">
                            <form method="post">
                                <div class="input-group">
                                    <input type="text" name="tcari" class="form-control" value="<?php echo $text; ?>" placeholder="Masukan Keyword Pencarian (Kode Penggunaan, ID Pelanggan, Bulan[contoh : 01,09,12], Tahun, Nama Pelanggan, Nama Petugas) ......">
                                    <div class="input-group-btn">
                                        <button type="submit" name="bcari" class="btn btn-primary"><span class="glyphicon glyphicon-search"></span>&nbsp;CARI</button>
                                        <button type="submit" name="brefresh" class="btn btn-success"><span class="glyphicon glyphicon-refresh"></span>&nbsp;REFRESH</button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover">
                                    <thead>
                                        <th><center>No.</center></th>
                                        <th>Kode Penggunaan</th>
                                        <th>ID Pelanggan</th>
                                        <th>Nama</th>
                                        <th>Bulan</th>
                                        <th>Meter Awal</th>
                                        <th>Meter Akhir</th>
                                        <th>Tanggal Cek</th>
                                        <th>Petugas</th>
                                        <th colspan="1"><center>AKSI</center></th>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $no = 0;
                                        $stmt = $conn->prepare("SELECT * FROM qw_penggunaan $cari ORDER BY tgl_cek DESC");
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        if ($result->num_rows == 0) {
                                            echo '<tr><td colspan="10">No records found</td></tr>';
                                        } else {
                                            while ($r = $result->fetch_assoc()) {
                                                $no++;
                                                $stmt2 = $conn->prepare("SELECT COUNT(*) FROM tagihan WHERE id_pelanggan = ? AND bulan = ? AND tahun = ? AND status = 'Belum Bayar'");
                                                $stmt2->bind_param('sss', $r['id_pelanggan'], $r['bulan'], $r['tahun']);
                                                $stmt2->execute();
                                                $result2 = $stmt2->get_result();
                                                $cek = $result2->fetch_array()[0];
                                        ?>
                                                <tr>
                                                    <td align="center"><?php echo $no; ?>.</td>
                                                    <td><?php echo $r['id_penggunaan'] ?></td>
                                                    <td><?php echo $r['id_pelanggan'] ?></td>
                                                    <td><?php echo $r['nama_pelanggan'] ?></td>
                                                    <td><?php $aksi->bulan($r['bulan']);
                                                        echo " " . $r['tahun']; ?></td>
                                                    <td><?php echo $r['meter_awal'] ?></td>
                                                    <td><?php echo $r['meter_akhir'] ?></td>
                                                    <td><?php $aksi->format_tanggal($r['tgl_cek']); ?></td>
                                                    <td><?php echo $r['nama_petugas'] ?></td>
                                                    <?php
                                                    if ($cek == 0) { ?>
                                                        <td colspan="2"></td>
                                                    <?php } else { ?>
                                                        <td align="center"><a href="?menu=penggunaan&hapus&id=<?php echo $r['id_penggunaan']; ?>"><span class="glyphicon glyphicon-trash"></span></a></td>
                                                    <?php } ?>
                                                </tr>
                                        <?php }
                                        } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="panel-footer">&nbsp;</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
