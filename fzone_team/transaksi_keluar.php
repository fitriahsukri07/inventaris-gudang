<?php
session_start();
include 'conn.php';

$active_page = "transaksi_keluar";

/* ================== DATA BARANG ================== */
$barangList = $conn->query("
    SELECT id_barang, nama, stok_baik 
    FROM barang 
    WHERE stok_baik > 0
    ORDER BY nama ASC
");

/* ================== PROSES SIMPAN BARANG KELUAR ================== */
if (isset($_POST['tambah_barang_keluar'])) {
    // Pastikan session id_user ada (Gunakan 'id_user' sesuai kolom di tabel users)
    $id_user = (int) $_SESSION['id_user'];

    // PERBAIKAN: Gunakan format DATE (Y-m-d) agar cocok dengan tipe data DATE di SQL
    $tanggal = date('Y-m-d');
    $status  = 'keluar';

    $idBarangArr = $_POST['id_barang'];
    $stokArr     = $_POST['stok']; // Ini adalah jumlah unit yang dikeluarkan

    $count = 0;
    $errors = [];

    $conn->begin_transaction();

    try {
        foreach ($idBarangArr as $i => $id_barang) {
            $id_barang = (int)$id_barang;
            $qty_keluar = (int)$stokArr[$i];

            if ($id_barang <= 0 || $qty_keluar <= 0) continue;

            // Cek stok yang tersedia di database
            $cek = $conn->query("SELECT nama, stok_baik FROM barang WHERE id_barang = $id_barang LIMIT 1");

            if ($cek->num_rows == 0) {
                $errors[] = "Barang tidak ditemukan.";
                continue;
            }

            $row = $cek->fetch_assoc();

            // Validasi kecukupan stok_baik
            if ($row['stok_baik'] < $qty_keluar) {
                $errors[] = "Stok '" . $row['nama'] . "' tidak cukup! (Tersedia: " . $row['stok_baik'] . ")";
                continue;
            }

            // 1. UPDATE TABEL BARANG: Kurangi stok dan stok_baik sesuai struktur DB Anda
            $updateStok = $conn->query("UPDATE barang 
                                        SET stok = stok - $qty_keluar,
                                            stok_baik = stok_baik - $qty_keluar
                                        WHERE id_barang = $id_barang");

            // 2. INSERT TABEL TRANSAKSI: Pastikan kolom 'jumlah' terisi untuk Dashboard
            $insertTrans = $conn->query("INSERT INTO transaksi (id_user, id_barang, tanggal_transaksi, status, jumlah)
                                         VALUES ($id_user, $id_barang, '$tanggal', '$status', $qty_keluar)");

            if ($updateStok && $insertTrans) {
                $count++;
            }
        }

        if ($count > 0 && empty($errors)) {
            $conn->commit();
            // Dialihkan kembali ke halaman ini dengan parameter sukses
            header("Location: transaksi_keluar.php?pesan=berhasil&jml=" . $count);
        } else {
            $conn->rollback();
            $msg = !empty($errors) ? implode("\\n", $errors) : "Gagal memproses transaksi.";
            echo "<script>alert('Gagal: $msg'); window.history.back();</script>";
        }
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('Error: " . $e->getMessage() . "'); window.history.back();</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Transaksi Keluar | F-ZONE COMPANY</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- BOOTSTRAP & ICON -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        :root {
            --primary-red: #e11d48;
            --dark-slate: #0f172a;
            --sidebar-bg: #1e293b;
            --content-bg: #f1f5f9;
            --accent-red: #fb7185;
        }

        body {
            background-color: var(--content-bg);
            font-family: 'Inter', sans-serif;
            color: #334155;
        }

        /* SIDEBAR CUSTOMIZATION */
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
            border-radius: 10px;
            margin-bottom: 5px;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.05);
            color: #fff !important;
        }

        .nav-link.active {
            background: linear-gradient(135deg, var(--primary-red), #be123c) !important;
            color: #fff !important;
            box-shadow: 0 4px 12px rgba(225, 29, 72, 0.3);
        }

        /* CONTENT WRAPPER */
        .content-wrapper {
            margin-left: 260px;
            padding: 40px;
        }

        /* CARD MODERNIZATION */
        .card {
            border: none;
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.03);
            overflow: hidden;
        }

        .card-header {
            border-bottom: 1px solid #f1f5f9;
            padding: 20px 25px !important;
        }

        /* FORM & TABLE */
        .table thead th {
            background-color: #f8fafc;
            color: #64748b;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            border: none;
            padding: 15px;
        }

        .table tbody td {
            padding: 15px;
            border-bottom: 1px solid #f1f5f9;
        }

        .form-select,
        .form-control {
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            padding: 10px;
        }

        .form-select:focus,
        .form-control:focus {
            border-color: var(--primary-red);
            box-shadow: 0 0 0 3px rgba(225, 29, 72, 0.1);
        }

        /* BUTTONS */
        .btn-danger {
            background: var(--primary-red);
            border: none;
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-danger:hover {
            background: #be123c;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(225, 29, 72, 0.2);
        }

        .btn-remove {
            color: #fda4af;
            transition: 0.2s;
        }

        .btn-remove:hover {
            color: var(--primary-red);
            transform: scale(1.1);
        }

        /* MODAL STYLING */
        .modal-content {
            border: none;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .modal-header.bg-danger {
            background: linear-gradient(135deg, var(--primary-red), #be123c) !important;
            border: none;
            padding: 25px;
        }

        /* BADGE COLORS */
        .badge.bg-danger {
            background-color: #fff1f2 !important;
            color: #e11d48 !important;
            border: 1px solid #ffe4e6;
            padding: 6px 10px;
        }

        @media (max-width: 768px) {
            .content-wrapper {
                margin-left: 0;
                padding: 20px;
            }

            .main-sidebar {
                margin-left: -260px;
            }
        }
    </style>
</head>

<body>

    <!-- SIDEBAR -->

    <aside class="main-sidebar p-3 shadow">
        <div class="d-flex align-items-center mb-4 px-2">
            <div class="bg-primary p-2 rounded-3 me-2 text-white">
                <i class="fas fa-boxes-stacked fa-lg"></i>
            </div>
            <h5 class="text-white mb-0 fw-bold">INVENTARIS <span class="text-primary">GUDANG</span></h5>
        </div>

        <nav class="nav flex-column">
            <a href="dashboard.php" class="nav-link <?= ($active_page == 'dashboard') ? 'active' : ''; ?>">
                <i class="fas fa-th-large me-2"></i> Dashboard
            </a>

            <a href="barang.php" class="nav-link <?= ($active_page == 'data_barang') ? 'active' : ''; ?>">
                <i class="fas fa-clipboard-check me-2"></i> Data Barang
            </a>

            <a href="transaksi_masuk.php" class="nav-link <?= ($active_page == 'transaksi_masuk') ? 'active' : ''; ?>">
                <i class="fas fa-file-import me-2"></i> Transaksi Masuk
            </a>

            <a href="transaksi_keluar.php" class="nav-link <?= ($active_page == 'transaksi_keluar') ? 'active' : ''; ?>">
                <i class="fas fa-file-export me-2"></i> Transaksi Keluar
            </a>

            <hr class="text-secondary">

            <a href="admin.php" class="nav-link <?= ($active_page == 'admin') ? 'active' : ''; ?>">
                <i class="fas fa-users-cog me-2"></i>Manajemen Akun
            </a>

            <a href="logout.php" class="nav-link text-danger mt-5">
                <i class="fas fa-power-off me-2"></i> Logout
            </a>
        </nav>
    </aside>

    <!-- CONTENT -->
    <div class="content-wrapper">
        <h3 class="fw-bold mb-3">Transaksi Barang Keluar</h3>
        <p class="text-muted">Barang keluar akan mengurangi stok secara otomatis</p>

        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between">
                <h5 class="fw-bold text-danger">
                    <i class="fas fa-list"></i> Daftar Barang Keluar
                </h5>
                <button type="button" id="addBtn" class="btn btn-danger btn-sm">
                    <i class="fas fa-plus"></i> Tambah Baris
                </button>
            </div>

            <div class="card-body">
                <form method="post">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Barang</th>
                                <th width="200">Jumlah</th>
                                <th width="50" class="text-center">Hapus</th>
                            </tr>
                        </thead>
                        <tbody id="formBody">
                            <tr>
                                <td>
                                    <select name="id_barang[]" class="form-select" required>
                                        <option value="">-- Pilih Barang yang Disediakan --</option>
                                        <?php while ($b = $barangList->fetch_assoc()) { ?>
                                            <option value="<?= $b['id_barang']; ?>">
                                                <?= $b['nama']; ?> (stok: <?= $b['stok_baik']; ?>)
                                            </option>
                                        <?php } ?>
                                    </select>
                                </td>
                                <td><input type="number" name="stok[]" class="form-control" min="1" required></td>
                                <td class="text-center">
                                    <i class="fas fa-times-circle btn-remove removeRow"></i>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="text-end mt-3">
                        <button type="reset" class="btn btn-light">Reset</button>
                        <button type="submit" name="tambah_barang_keluar" class="btn btn-danger px-4">
                            <i class="fas fa-save"></i> Proses Transaksi
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php
        $dataTransaksi = $conn->query("
    SELECT 
        b.nama,
        b.harga,
        t.tanggal_transaksi
    FROM transaksi t
    JOIN barang b ON t.id_barang = b.id_barang
    WHERE t.status = 'keluar'
    ORDER BY t.tanggal_transaksi DESC
");
        ?>
        <div class="card mt-5 border-0 shadow-sm overflow-hidden">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
                <h6 class="mb-0 fw-bold"><i class="fas fa-history me-2 text-danger"></i> Riwayat Keluar Terbaru</h6>
                <a href="laporan_keluar.php" class="btn btn-outline-light btn-sm">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3" width="50">#</th>
                                <th>INFORMASI BARANG</th>
                                <th width="200">WAKTU KELUAR</th>
                                <th class="text-center" width="100">QTY</th>
                                <th class="text-end pe-3" width="180">TOTAL NILAI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Query Riwayat
                            $queryRiwayat = "SELECT t.*, b.nama, b.harga 
                 FROM transaksi t 
                 JOIN barang b ON t.id_barang = b.id_barang 
                 WHERE t.status = 'keluar' 
                 ORDER BY t.id_transaksi DESC 
                 LIMIT 10";

                            $dataRiwayat = $conn->query($queryRiwayat);
                            $no = 1;

                            if ($dataRiwayat && $dataRiwayat->num_rows > 0):
                                while ($row = $dataRiwayat->fetch_assoc()):
                                    // Pastikan kolom 'jumlah' ada di tabel transaksi Anda
                                    $jml = isset($row['jumlah']) ? $row['jumlah'] : 0;
                                    $total_nilai = $row['harga'] * $jml;
                                    $waktu = date('d-m-Y H:i', strtotime($row['tanggal_transaksi']));
                            ?>
                                    <tr style="cursor: pointer;" onclick="bukaModalKeluar('<?= addslashes($row['nama']); ?>', '<?= $waktu; ?>', '<?= $jml; ?>', '<?= number_format($total_nilai, 0, ',', '.'); ?>')">
                                        <td class="ps-3 text-muted"><?= $no++; ?></td>
                                        <td>
                                            <div class="fw-bold"><?= $row['nama']; ?></div>
                                            <small class="text-muted">Rp <?= number_format($row['harga'], 0, ',', '.'); ?> / unit</small>
                                        </td>
                                        <td><?= $waktu; ?> WIB</td>
                                        <td class="text-center">
                                            <span class="badge bg-danger">- <?= $jml; ?></span>
                                        </td>
                                        <td class="text-end pe-3 fw-bold text-primary">
                                            Rp <?= number_format($total_nilai, 0, ',', '.'); ?>
                                        </td>
                                    </tr>
                                <?php
                                endwhile; // Penutup while
                            else: // Jika data tidak ada
                                ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Belum ada data transaksi keluar.</td>
                                </tr>
                            <?php
                            endif; // Penutup if
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalDetailBarangKeluar" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title"><i class="fas fa-file-invoice me-2"></i> Detail Transaksi Keluar</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                            <span class="text-muted">Nama Barang</span>
                            <span class="fw-bold" id="popNamaKeluar"></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                            <span class="text-muted">Waktu Transaksi</span>
                            <span id="popWaktuKeluar"></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                            <span class="text-muted">Jumlah Keluar</span>
                            <span class="badge bg-danger" id="popJumlahKeluar"></span>
                        </div>
                        <div class="d-flex justify-content-between mb-0">
                            <span class="text-muted">Total Nilai Keluar</span>
                            <span class="fw-bold text-danger fs-5" id="popHargaKeluar"></span>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="button" class="btn btn-danger px-4" onclick="window.print()"><i class="fas fa-print me-2"></i> Cetak</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function bukaModalKeluar(nama, waktu, jumlah, harga) {
                document.getElementById('popNamaKeluar').innerText = nama;
                document.getElementById('popWaktuKeluar').innerText = waktu;
                document.getElementById('popJumlahKeluar').innerText = jumlah + " Unit";
                document.getElementById('popHargaKeluar').innerText = "Rp " + harga;
                var myModal = new bootstrap.Modal(document.getElementById('modalDetailBarangKeluar'));
                myModal.show();
            }
        </script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script>
            document.addEventListener('click', function(e) {
                // Mencari elemen <a> yang mengandung teks "Logout" atau memiliki class/link logout
                const logoutBtn = e.target.closest('a[href="logout.php"], .nav-link.text-danger');

                if (logoutBtn) {
                    e.preventDefault(); // Menghentikan redirect instan ke logout.php

                    Swal.fire({
                        title: 'Konfirmasi Keluar',
                        text: "Apakah Anda Yakin Ingin Keluar?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#e11d48', // Merah sesuai tema F-ZONE
                        cancelButtonColor: '#64748b',
                        confirmButtonText: ' Ya, Keluar',
                        cancelButtonText: 'Batal',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'logout.php';
                        }
                    });
                }
            });

            // SCRIPT TAMBAHAN: Agar tombol "Tambah Baris" juga berfungsi
            document.getElementById('addBtn').addEventListener('click', function() {
                const tableBody = document.getElementById('formBody');
                const firstRow = tableBody.querySelector('tr');
                const newRow = firstRow.cloneNode(true);
                newRow.querySelectorAll('input').forEach(i => i.value = '');
                tableBody.appendChild(newRow);
            });

            // SCRIPT TAMBAHAN: Agar tombol "Hapus Baris" berfungsi
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('removeRow')) {
                    const rows = document.querySelectorAll('#formBody tr');
                    if (rows.length > 1) {
                        e.target.closest('tr').remove();
                    }
                }
            });
        </script>
        <script>
            // Menunggu dokumen selesai dimuat
            document.addEventListener('DOMContentLoaded', function() {
                const urlParams = new URLSearchParams(window.location.search);

                // Alert jika BERHASIL
                if (urlParams.get('pesan') === 'berhasil') {
                    const jumlah = urlParams.get('jml') || 'beberapa';
                    Swal.fire({
                        icon: 'success',
                        title: 'Transaksi Berhasil!',
                        text: 'Berhasil mengeluarkan ' + jumlah + ' jenis barang.',
                        confirmButtonColor: '#e11d48', // Merah sesuai tema
                        timer: 3000,
                        timerProgressBar: true
                    }).then(() => {
                        // Menghapus parameter di URL agar tidak muncul lagi saat refresh
                        window.history.replaceState({}, document.title, window.location.pathname);
                    });
                }

                // Alert jika GAGAL (opsional jika Anda menambahkan ?pesan=gagal di PHP)
                if (urlParams.get('pesan') === 'gagal') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Transaksi Gagal',
                        text: 'Terjadi kesalahan saat memproses data.',
                        confirmButtonColor: '#e11d48'
                    }).then(() => {
                        window.history.replaceState({}, document.title, window.location.pathname);
                    });
                }
            });
        </script>