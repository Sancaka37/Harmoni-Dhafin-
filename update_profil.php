<?php
require 'koneksi.php';
session_start();

header('Content-Type: application/json');
$data = json_decode(file_get_contents("php://input"));

if (!isset($data->id_pelanggan) || !isset($data->username) || !isset($data->email)) {
    echo json_encode(['error' => 'Data tidak lengkap.']);
    exit();
}

try {
    $stmt = $conn->prepare("UPDATE pelanggan SET username = :username, email = :email, no_telepon = :telepon, alamat_utama = :alamat WHERE id_pelanggan = :id");
    
    // Binding parameters
    $stmt->bindParam(':username', $data->username);
    $stmt->bindParam(':email', $data->email);
    
    // Handle optional fields
    $telepon = isset($data->no_telepon) ? $data->no_telepon : null;
    $alamat = isset($data->alamat_utama) ? $data->alamat_utama : null;
    $stmt->bindParam(':telepon', $telepon);
    $stmt->bindParam(':alamat', $alamat);
    
    $stmt->bindParam(':id', $data->id_pelanggan, PDO::PARAM_INT);
    
    $stmt->execute();
    
    echo json_encode(['success' => true, 'message' => 'Profil berhasil diperbarui.']);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Gagal menyimpan profil: ' . $e->getMessage()]);
}
?>
