<?php
$conn= mysqli_connect("localhost", "root", "", "fzone_team");

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
