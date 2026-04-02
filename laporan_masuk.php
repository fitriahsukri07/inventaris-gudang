<?php
session_start();
include 'conn.php';

// Pastikan user sudah login
if (!isset($_SESSION['login'])) {
    header("location: login.php");
    exit;
}

$active_page = 'transaksi_masuk';

// Logika Filter
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$search = $_GET['search'] ?? '';

// Build Query
$querySql = "SELECT t.*, b.nama, b.harga, u.username 
             FROM transaksi t 
             JOIN barang b ON t.id_barang = b.id_barang 
             JOIN users u ON t.id_user = u.id_user 
             WHERE t.status = 'masuk'";

if (!empty($start_date) && !empty($end_date)) {
    $querySql .= " AND DATE(t.tanggal_transaksi) BETWEEN '$start_date' AND '$end_date'";
}

if (!empty($search)) {
    $querySql .= " AND (b.nama LIKE '%$search%' OR u.username LIKE '%$search%')";
}

$querySql .= " ORDER BY t.id_transaksi DESC";
$result = $conn->query($querySql);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Barang Masuk | F-ZONE COMPANY</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap');

        body {
            background-color: #f8fafc;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .navbar-custom {
            background: #1e293b;
            padding: 15px 0;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        }

        .table thead th {
            background-color: #f1f5f9;
            color: #475569;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            border: none;
        }

        .badge-qty {
            background-color: #dcfce7;
            color: #15803d;
            font-weight: 600;
            padding: 5px 12px;
            border-radius: 8px;
        }

        @media print {
            .no-print {
                display: none;
            }

            .card {
                box-shadow: none;
                border: 1px solid #ddd;
            }
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-dark navbar-custom no-print mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="transaksi_masuk.php">
                <i class="fas fa-arrow-left me-2"></i> KEMBALI
            </a>
            <span class="navbar-text text-white fw-bold">LAPORAN BARANG MASUK</span>
        </div>
    </nav>

    <div class="container mb-5">
        <div class="card p-4 mb-4 no-print">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Dari Tanggal</label>
                    <input type="date" name="start_date" class="form-control" value="<?= $start_date ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Sampai Tanggal</label>
                    <input type="date" name="end_date" class="form-control" value="<?= $end_date ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Cari Barang / Petugas</label>
                    <input type="text" name="search" class="form-control" placeholder="Ketik nama..." value="<?= $search ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> Filter</button>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark">Data Transaksi Masuk</h5>
                <button onclick="window.print()" class="btn btn-outline-dark btn-sm no-print">
                    <i class="fas fa-print me-1"></i> Cetak Laporan
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">No</th>
                                <th>Tanggal & Waktu</th>
                                <th>Nama Barang</th>
                                <th>Petugas</th>
                                <th class="text-center">Jumlah</th>
                                <th class="text-end">Harga Satuan</th>
                                <th class="text-end pe-4">Total Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $grand_total = 0;
                            if ($result->num_rows > 0):
                                while ($row = $result->fetch_assoc()):
                                    $total_baris = $row['harga'] * $row['jumlah'];
                                    $grand_total += $total_baris;
                            ?>
                                    <tr>
                                        <td class="ps-4 text-muted"><?= $no++ ?></td>
                                        <td>
                                            <div class="fw-bold"><?= date('d/m/Y', strtotime($row['tanggal_transaksi'])) ?></div>
                                            <small class="text-muted"><?= date('H:i', strtotime($row['tanggal_transaksi'])) ?> WIB</small>
                                        </td>
                                        <td><span class="fw-bold text-dark"><?= $row['nama'] ?></span></td>
                                        <td><i class="far fa-user-circle me-1"></i><?= $row['username'] ?></td>
                                        <td class="text-center">
                                            <span class="badge-qty">+ <?= $row['jumlah'] ?></span>
                                        </td>
                                        <td class="text-end">Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                                        <td class="text-end pe-4 fw-bold text-primary">Rp <?= number_format($total_baris, 0, ',', '.') ?></td>
                                    </tr>
                                <?php endwhile; ?>
                                <tr class="table-light">
                                    <td colspan="6" class="text-end fw-bold py-3">GRAND TOTAL</td>
                                    <td class="text-end pe-4 fw-bold text-success py-3" style="font-size: 1.1rem;">
                                        Rp <?= number_format($grand_total, 0, ',', '.') ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">Data tidak ditemukan.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-4 text-center text-muted small">
            <p>Dicetak pada: <?= date('d-m-Y H:i:s') ?> | F-ZONE COMPANY Inventory System</p>
        </div>
    </div>

</body>

</html>