<?php
session_start();
include 'conn.php';

$active_page = "transaksi_masuk";

if (isset($_POST['tambah_barang_masuk'])) {
    $id_user = (int) $_SESSION['id_user'];
    $tanggal = date('Y-m-d'); // Sesuai tipe data DATE di database Anda
    $status_transaksi = 'masuk';

    $namas  = $_POST['nama'];
    $stoks  = $_POST['stok']; // Jumlah yang masuk
    $hargas = $_POST['harga'];

    $count = 0;

    foreach ($namas as $i => $val) {
        $nama       = mysqli_real_escape_string($conn, $namas[$i]);
        $input_stok = (int) $stoks[$i];
        $harga      = (int) $hargas[$i];

        if ($input_stok <= 0 || empty($nama)) continue;

        // Cek apakah barang sudah ada
        $cek = $conn->query("SELECT id_barang FROM barang WHERE nama = '$nama' AND harga = '$harga' LIMIT 1");

        if ($cek->num_rows > 0) {
            $data_barang = $cek->fetch_assoc();
            $id_barang_final = $data_barang['id_barang'];
            // Update stok total dan stok_baik sesuai struktur tabel Anda
            $conn->query("UPDATE barang SET stok = stok + $input_stok, stok_baik = stok_baik + $input_stok WHERE id_barang = '$id_barang_final'");
        } else {
            // Insert barang baru
            $conn->query("INSERT INTO barang (nama, stok, harga, stok_baik, stok_rusak) VALUES ('$nama', '$input_stok', '$harga', '$input_stok', 0)");
            $id_barang_final = $conn->insert_id;
        }

        // Simpan ke tabel transaksi (kolom 'jumlah' sangat penting untuk dashboard)
        $sql_transaksi = "INSERT INTO transaksi (id_user, id_barang, tanggal_transaksi, status, jumlah) 
                          VALUES ('$id_user', '$id_barang_final', '$tanggal', '$status_transaksi', '$input_stok')";
        if ($conn->query($sql_transaksi)) $count++;
    }

    header("location: transaksi_masuk.php?pesan=berhasil");
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Transaksi Masuk | F-ZONE COMPANY</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap');

        :root {
            --primary-green: #10b981;
            --dark-obsidian: #0f172a;
            --sidebar-bg: #1e293b;
            --soft-bg: #f8fafc;
            --border-color: #e2e8f0;
        }

        body {
            background-color: var(--soft-bg);
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #334155;
        }

        .main-sidebar {
            width: 260px;
            height: 100vh;
            position: fixed;
            background: var(--sidebar-bg);
            padding: 25px 15px;
            border-right: 1px solid rgba(255, 255, 255, 0.05);
        }

        .nav-link {
            color: #94a3b8 !important;
            padding: 12px 18px;
            border-radius: 12px;
            margin-bottom: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.05);
            color: #fff !important;
            transform: translateX(4px);
        }

        .nav-link.active {
            background: linear-gradient(135deg, var(--primary-green), #059669) !important;
            color: #fff !important;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.25);
        }

        .content-wrapper {
            margin-left: 260px;
            padding: 40px;
        }

        .card {
            border: none;
            border-radius: 20px;
            background: #ffffff;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }

        .btn-remove {
            color: #ef4444;
            cursor: pointer;
            opacity: 0.7;
        }

        .btn-remove:hover {
            opacity: 1;
            transform: scale(1.2);
        }

        .form-control {
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            padding: 10px 15px;
        }
    </style>
</head>

<body>

    <aside class="main-sidebar p-3 shadow">
        <div class="d-flex align-items-center mb-4 px-2">
            <div class="bg-primary p-2 rounded-3 me-2 text-white"><i class="fas fa-boxes-stacked fa-lg"></i></div>
            <h5 class="text-white mb-0 fw-bold">INVENTARIS <span class="text-primary">GUDANG</span></h5>
        </div>
        <nav class="nav flex-column">
            <a href="dashboard.php" class="nav-link <?= ($active_page == 'dashboard') ? 'active' : ''; ?>"><i class="fas fa-th-large me-2"></i> Dashboard</a>
            <a href="barang.php" class="nav-link <?= ($active_page == 'data_barang') ? 'active' : ''; ?>"> <i class="fas fa-clipboard-check me-2"></i> Data Barang</a>
            <a href="transaksi_masuk.php" class="nav-link <?= ($active_page == 'transaksi_masuk') ? 'active' : ''; ?>"><i class="fas fa-file-import me-2"></i> Transaksi Masuk</a>
            <a href="transaksi_keluar.php" class="nav-link <?= ($active_page == 'transaksi_keluar') ? 'active' : ''; ?>"><i class="fas fa-file-export me-2"></i> Transaksi Keluar</a>
            <hr class="text-secondary">
            <a href="admin.php" class="nav-link <?= ($active_page == 'admin') ? 'active' : ''; ?>"><i class="fas fa-users-cog me-2"></i> Manajemen Akun</a>
            <a href="javascript:void(0)" onclick="confirmLogout()" class="nav-link text-danger mt-5" id="logout-sidebar">
                <i class="fas fa-power-off me-2"></i> Logout
            </a>
        </nav>
    </aside>

    <div class="content-wrapper">
        <h2 class="fw-bold">Transaksi Barang Masuk</h2>
        <p class="text-secondary">Input stok baru untuk menambah inventaris.</p>

        <div class="card mt-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="m-0 fw-bold text-success"><i class="fas fa-plus-circle me-1"></i> Form Tambah Stok</h5>
                <button type="button" id="addBtn" class="btn btn-success btn-sm rounded-pill px-3">
                    <i class="fas fa-plus me-1"></i> Tambah Baris
                </button>
            </div>
            <div class="card-body">
                <form method="post">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama Barang</th>
                                    <th width="250">Harga Satuan (Rp)</th>
                                    <th width="150">Jumlah</th>
                                    <th width="50" class="text-center">Hapus</th>
                                </tr>
                            </thead>
                            <tbody id="formBody">
                                <tr>
                                    <td><input type="text" name="nama[]" class="form-control" required placeholder="Nama Barang"></td>
                                    <td><input type="number" name="harga[]" class="form-control" required min="1" placeholder="0"></td>
                                    <td><input type="number" name="stok[]" class="form-control" required min="1" placeholder="0"></td>
                                    <td class="text-center"><i class="fas fa-times-circle btn-remove removeRow"></i></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 border-top pt-3 text-end">
                        <button type="reset" class="btn btn-light px-4 me-2">Reset</button>
                        <button type="submit" name="tambah_barang_masuk" class="btn btn-success px-5 shadow-sm">
                            <i class="fas fa-save me-2"></i>Simpan Transaksi
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mt-5 border-0 shadow-sm overflow-hidden">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
                <h6 class="mb-0 fw-bold"><i class="fas fa-history me-2 text-success"></i> Riwayat Masuk Terbaru</h6>
                <a href="laporan_masuk.php" class="btn btn-outline-light btn-sm">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3" width="50">#</th>
                                <th>INFORMASI BARANG</th>
                                <th width="200">WAKTU MASUK</th>
                                <th class="text-center" width="100">QTY</th>
                                <th class="text-end pe-3" width="180">TOTAL NILAI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Query mengambil kolom jumlah dari tabel transaksi
                            $queryRiwayat = "SELECT t.*, b.nama, b.harga FROM transaksi t JOIN barang b ON t.id_barang = b.id_barang WHERE t.status = 'masuk' ORDER BY t.id_transaksi DESC LIMIT 10";
                            $dataRiwayat = $conn->query($queryRiwayat);
                            $no = 1;
                            if ($dataRiwayat && $dataRiwayat->num_rows > 0):
                                while ($row = $dataRiwayat->fetch_assoc()):
                                    $jml = $row['jumlah']; // Sekarang mengambil dari t.jumlah
                                    $total_nilai = $row['harga'] * $jml;
                                    $waktu = date('d-m-Y H:i', strtotime($row['tanggal_transaksi']));
                            ?>
                                    <tr style="cursor: pointer;" onclick="bukaModal('<?= addslashes($row['nama']); ?>', '<?= $waktu; ?>', '<?= $jml; ?>', '<?= number_format($total_nilai, 0, ',', '.'); ?>')">
                                        <td class="ps-3 text-muted"><?= $no++; ?></td>
                                        <td><strong><?= $row['nama']; ?></strong><br><small class="text-muted">Rp <?= number_format($row['harga'], 0, ',', '.'); ?></small></td>
                                        <td><?= $waktu; ?> WIB</td>
                                        <td class="text-center"><span class="badge bg-success">+ <?= $jml; ?></span></td>
                                        <td class="text-end pe-3 fw-bold text-primary">Rp <?= number_format($total_nilai, 0, ',', '.'); ?></td>
                                    </tr>
                                <?php endwhile;
                            else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Belum ada data transaksi masuk.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDetailBarang" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-file-invoice me-2"></i> Detail Transaksi</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" id="modalContent">
                    <p>Barang: <span id="popNama" class="fw-bold"></span></p>
                    <p>Waktu: <span id="popWaktu"></span></p>
                    <p>Jumlah: <span id="popJumlah" class="badge bg-success"></span></p>
                    <hr>
                    <h5 class="text-end">Total: <span id="popHarga" class="text-success"></span></h5>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Tambah Baris
            $("#addBtn").click(function() {
                var row = `<tr>
                    <td><input type="text" name="nama[]" class="form-control" required placeholder="Nama Barang"></td>
                    <td><input type="number" name="harga[]" class="form-control" required min="1" placeholder="0"></td>
                    <td><input type="number" name="stok[]" class="form-control" required min="1" placeholder="0"></td>
                    <td class="text-center"><i class="fas fa-times-circle btn-remove removeRow"></i></td>
                </tr>`;
                $("#formBody").append(row);
            });

            $(document).on('click', '.removeRow', function() {
                if ($('#formBody tr').length > 1) $(this).closest('tr').remove();
            });
        });

        function bukaModal(nama, waktu, jumlah, harga) {
            $('#popNama').text(nama);
            $('#popWaktu').text(waktu);
            $('#popJumlah').text(jumlah + " Unit");
            $('#popHarga').text("Rp " + harga);
            new bootstrap.Modal($('#modalDetailBarang')).show();
        }

        // FUNGSI KONFIRMASI LOGOUT
        function confirmLogout() {
            Swal.fire({
                title: 'Yakin ingin keluar?',
                text: "Anda Anda Yakin Ingin Keluar?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Keluar!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'logout.php';
                }
            });
        }
        // Cek jika ada parameter 'pesan' di URL
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('pesan') === 'berhasil') {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Data transaksi barang masuk telah disimpan.',
                timer: 2000, // Alert hilang otomatis dalam 2 detik
                showConfirmButton: false
            }).then(() => {
                // Bersihkan parameter di URL agar alert tidak muncul lagi saat refresh
                window.history.replaceState({}, document.title, window.location.pathname);
            });
        }
    </script>

</body>

</html>