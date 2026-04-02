<?php
session_start();
include 'conn.php';

// Pastikan user sudah login
if (!isset($_SESSION['id_user'])) {
    header("location: login.php");
    exit;
}

$active_page = 'transaksi_keluar';

// Logika Filter
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$search = $_GET['search'] ?? '';

// Build Query
$querySql = "SELECT t.*, b.nama, b.harga, u.username 
             FROM transaksi t 
             JOIN barang b ON t.id_barang = b.id_barang 
             JOIN users u ON t.id_user = u.id_user 
             WHERE t.status = 'keluar'";

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
    <title>Laporan Barang Keluar | F-ZONE COMPANY</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap');

        :root {
            --primary-red: #e11d48;
        }

        body {
            background-color: #f1f5f9;
            font-family: 'Inter', sans-serif;
            color: #334155;
        }

        .navbar-custom {
            background: #1e293b;
            padding: 15px 0;
            border-bottom: 3px solid var(--primary-red);
        }

        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .table thead th {
            background-color: #f8fafc;
            color: #64748b;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            padding: 15px;
        }

        .badge-qty {
            background-color: #fff1f2;
            color: #e11d48;
            font-weight: 600;
            padding: 5px 12px;
            border-radius: 8px;
            border: 1px solid #ffe4e6;
        }

        .btn-primary {
            background-color: var(--primary-red);
            border: none;
        }

        .btn-primary:hover {
            background-color: #be123c;
        }

        @media print {

            .no-print,
            .navbar-custom,
            .filter-card {
                display: none !important;
            }

            body {
                background-color: white;
            }

            .card {
                box-shadow: none;
                border: 1px solid #e2e8f0;
            }

            .content-wrapper {
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-dark navbar-custom no-print mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center" href="transaksi_keluar.php">
                <i class="fas fa-arrow-left me-2"></i> KEMBALI
            </a>
            <span class="navbar-text text-white fw-bold">LAPORAN TRANSAKSI KELUAR</span>
        </div>
    </nav>

    <div class="container mb-5">
        <div class="card p-4 mb-4 no-print filter-card">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Dari Tanggal</label>
                    <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Sampai Tanggal</label>
                    <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted">Cari Barang / Petugas</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Nama barang..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">
                        <i class="fas fa-filter me-1"></i> FILTER
                    </button>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-file-export me-2 text-danger"></i>Data Pengeluaran Barang</h5>
                <button onclick="window.print()" class="btn btn-outline-dark btn-sm no-print fw-bold">
                    <i class="fas fa-print me-1"></i> CETAK PDF / PRINT
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4" width="60">No</th>
                                <th>Waktu Keluar</th>
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
                            if ($result && $result->num_rows > 0):
                                while ($row = $result->fetch_assoc()):
                                    $total_baris = $row['harga'] * $row['jumlah'];
                                    $grand_total += $total_baris;
                            ?>
                                    <tr>
                                        <td class="ps-4 text-muted"><?= $no++ ?></td>
                                        <td>
                                            <div class="fw-bold text-dark"><?= date('d M Y', strtotime($row['tanggal_transaksi'])) ?></div>
                                            <small class="text-muted"><?= date('H:i', strtotime($row['tanggal_transaksi'])) ?> WIB</small>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-dark"><?= $row['nama'] ?></span>
                                        </td>
                                        <td>
                                            <small class="d-block text-muted">Dicatat oleh:</small>
                                            <span class="badge bg-light text-dark border"><i class="far fa-user me-1"></i><?= $row['username'] ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge-qty">- <?= $row['jumlah'] ?></span>
                                        </td>
                                        <td class="text-end">Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                                        <td class="text-end pe-4 fw-bold text-danger">Rp <?= number_format($total_baris, 0, ',', '.') ?></td>
                                    </tr>
                                <?php endwhile; ?>
                                <tr class="table-light">
                                    <td colspan="6" class="text-end fw-bold py-3">TOTAL PENGELUARAN</td>
                                    <td class="text-end pe-4 fw-bold text-danger py-3" style="font-size: 1.1rem;">
                                        Rp <?= number_format($grand_total, 0, ',', '.') ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <i class="fas fa-folder-open fa-3x text-light mb-3 d-block"></i>
                                        <span class="text-muted">Tidak ada data transaksi keluar ditemukan.</span>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-4 d-none d-print-block">
            <div class="row">
                <div class="col-8"></div>
                <div class="col-4 text-center">
                    <p>Dicetak pada: <?= date('d/m/Y H:i') ?></p>
                    <br><br><br>
                    <p class="fw-bold border-top pt-2">Manajer Gudang</p>
                </div>
            </div>
        </div>
    </div>

</body>

</html>