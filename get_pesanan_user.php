<?php
include 'koneksi.php';

if (isset($_GET['id_pelanggan'])) {
    $id = intval($_GET['id_pelanggan']);
    try {
        $stmtOrder = $conn->prepare("SELECT p.id_pesanan, p.tanggal_pesanan, p.total_harga, p.status_pesanan,
                                     pm.metode_pembayaran
                                     FROM pesanan p
                                     LEFT JOIN pembayaran pm ON p.id_pesanan = pm.id_pesanan
                                     WHERE p.id_pelanggan = :id 
                                     ORDER BY p.tanggal_pesanan DESC");
        $stmtOrder->bindParam(':id', $id);
        $stmtOrder->execute();
        $orders = $stmtOrder->fetchAll(PDO::FETCH_ASSOC);
        
        $stmtDetail = $conn->prepare("SELECT dp.kuantitas, pr.nama_produk 
                                      FROM detail_pesanan dp
                                      JOIN produk pr ON dp.id_produk = pr.id_produk
                                      WHERE dp.id_pesanan = :id_pesanan");
                                      
        foreach ($orders as &$order) {
            $stmtDetail->execute([':id_pesanan' => $order['id_pesanan']]);
            $order['details'] = $stmtDetail->fetchAll(PDO::FETCH_ASSOC);
        }
        
        echo json_encode($orders);
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Gagal mengambil data: " . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(["error" => "ID pelanggan tidak diberikan."]);
}
?>
