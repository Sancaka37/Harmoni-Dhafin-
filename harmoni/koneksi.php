<?php
// Izinkan akses dari frontend
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$host = "127.0.0.1";
$username = "root"; // Username default Laragon
$password = "";     // Password default Laragon (kosong)
$database = "db_harmoni";

try {
    $conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    // Atur mode error PDO ke Exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(["error" => "Koneksi database gagal: " . $e->getMessage()]);
    exit();
}
?>