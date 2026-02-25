<?php
// Memulai session
session_start();

// Menghapus semua data session
session_unset();

// Menghancurkan session yang ada
session_destroy();

// Mengarahkan pengguna kembali ke halaman login dengan pesan sukses (opsional)
header("location: login.php?pesan=logout");
exit;
