<?php
include 'koneksi.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    try {
        $query = "SELECT p.*, k.nama_kategori 
                  FROM produk p 
                  LEFT JOIN kategori k ON p.id_kategori = k.id_kategori 
                  WHERE p.id_produk = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $produk = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($produk) {
            echo json_encode($produk);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Produk tidak ditemukan."]);
        }
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Gagal mengambil data: " . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(["error" => "ID produk tidak diberikan."]);
}
?>
