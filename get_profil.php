<?php
require 'koneksi.php';
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"));
if (!isset($data->id_pelanggan)) {
    echo json_encode(['error' => 'ID pelanggan tidak ditemukan.']);
    exit();
}

try {
    $stmt = $conn->prepare("SELECT id_pelanggan, username, email, no_telepon, alamat_utama, foto_profil FROM pelanggan WHERE id_pelanggan = :id");
    $stmt->bindParam(':id', $data->id_pelanggan, PDO::PARAM_INT);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        echo json_encode(['success' => true, 'data' => $user]);
    } else {
        echo json_encode(['error' => 'Pengguna tidak ditemukan.']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Gagal mengambil data profil: ' . $e->getMessage()]);
}
?>
