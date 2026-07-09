<?php
include 'koneksi.php';

try {
    // Query untuk mengambil semua produk dari database beserta kategorinya
    $query = "SELECT p.*, k.nama_kategori 
              FROM produk p 
              LEFT JOIN kategori k ON p.id_kategori = k.id_kategori";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    // Ambil semua data dalam bentuk array asosiatif
    $produk = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Kirim data ke frontend dalam format JSON
    echo json_encode($produk);
} catch(PDOException $e) {
    echo json_encode(["error" => "Gagal mengambil data: " . $e->getMessage()]);
}
?>