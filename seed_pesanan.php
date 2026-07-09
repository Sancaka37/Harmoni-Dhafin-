<?php
include 'koneksi.php';

try {
    // Check if we already have a lot of orders
    $stmt = $conn->query("SELECT COUNT(*) FROM pesanan");
    $count = $stmt->fetchColumn();

    if ($count < 50) {
        // Find a customer
        $stmtPel = $conn->query("SELECT id_pelanggan FROM pelanggan WHERE role != 'admin' LIMIT 1");
        $id_pelanggan = $stmtPel->fetchColumn();

        if (!$id_pelanggan) {
            echo "Buat satu akun pelanggan biasa terlebih dahulu!";
            exit;
        }

        // Generate orders for the last 6 months
        $months = [1, 2, 3, 4, 5, 6]; // Jan to Jun
        $year = date('Y');
        
        $baseTotals = [
            1 => 850000,
            2 => 1200000,
            3 => 950000,
            4 => 1500000,
            5 => 2100000,
            6 => 500000
        ]; // Base total target for each month to create a nice curve

        $conn->beginTransaction();

        foreach ($months as $m) {
            $target = $baseTotals[$m];
            $currentTotal = 0;
            
            while ($currentTotal < $target) {
                // Random day
                $day = rand(1, 28);
                $dateStr = sprintf("%04d-%02d-%02d %02d:%02d:%02d", $year, $m, $day, rand(8, 20), rand(0, 59), rand(0, 59));
                
                $orderTotal = rand(50000, 300000);
                
                $stmt = $conn->prepare("INSERT INTO pesanan (id_pelanggan, tanggal_pesanan, total_harga, status_pesanan) VALUES (?, ?, ?, 'Selesai')");
                $stmt->execute([$id_pelanggan, $dateStr, $orderTotal]);
                
                $currentTotal += $orderTotal;
            }
        }

        $conn->commit();
        echo "Data riwayat pesanan (dummy) berhasil di-generate agar grafik tampil bagus!<br>";
    } else {
        echo "Data pesanan sudah cukup banyak, tidak perlu di-seed lagi.<br>";
    }
} catch(PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "Error: " . $e->getMessage();
}
?>
