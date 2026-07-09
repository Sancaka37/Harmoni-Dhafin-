<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include 'koneksi.php';

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->id_pelanggan) && !empty($data->cart)) {
    try {
        $conn->beginTransaction();
        
        $id_pelanggan = intval($data->id_pelanggan);
        
        // Kalkulasi Total
        $subtotal = 0;
        foreach ($data->cart as $item) {
            $subtotal += ($item->price * $item->qty);
        }
        $shipping = intval($data->biaya_pengiriman);
        $tax = round($subtotal * 0.10);
        $total_harga = $subtotal + $shipping + $tax;
        
        // 1. Insert Pesanan
        $stmtOrder = $conn->prepare("INSERT INTO pesanan (tanggal_pesanan, total_harga, status_pesanan, id_pelanggan) VALUES (NOW(), :total, 'Menunggu Pembayaran', :id_pelanggan)");
        $stmtOrder->execute([
            ':total' => $total_harga,
            ':id_pelanggan' => $id_pelanggan
        ]);
        $id_pesanan = $conn->lastInsertId();
        
        // 2. Insert Detail Pesanan
        $stmtDetail = $conn->prepare("INSERT INTO detail_pesanan (kuantitas, harga_satuan, subtotal, id_pesanan, id_produk) VALUES (:qty, :harga, :subtotal, :id_pesanan, :id_produk)");
        $stmtUpdateStok = $conn->prepare("UPDATE produk SET stok = stok - :qty, terjual = terjual + :qty WHERE id_produk = :id_produk");
        
        foreach ($data->cart as $item) {
            $item_subtotal = $item->price * $item->qty;
            $stmtDetail->execute([
                ':qty' => $item->qty,
                ':harga' => $item->price,
                ':subtotal' => $item_subtotal,
                ':id_pesanan' => $id_pesanan,
                ':id_produk' => $item->id
            ]);
            
            // Kurangi stok & tambah terjual
            $stmtUpdateStok->execute([
                ':qty' => $item->qty,
                ':id_produk' => $item->id
            ]);
        }
        
        // 3. Insert Pengiriman
        $alamat_lengkap = $data->nama_penerima . " | " . $data->telepon . " | " . $data->alamat . ", " . $data->kota . " " . $data->kode_pos;
        $stmtPengiriman = $conn->prepare("INSERT INTO pengiriman (alamat_pengiriman, kurir_layanan, status_pengiriman, id_pesanan) VALUES (:alamat, :kurir, 'Pending', :id_pesanan)");
        $stmtPengiriman->execute([
            ':alamat' => $alamat_lengkap,
            ':kurir' => $data->metode_pengiriman,
            ':id_pesanan' => $id_pesanan
        ]);
        
        // 4. Insert Pembayaran
        $stmtPembayaran = $conn->prepare("INSERT INTO pembayaran (tanggal_pembayaran, metode_pembayaran, jumlah_bayar, status_pembayaran, id_pesanan) VALUES (NOW(), :metode, :jumlah, 'Pending', :id_pesanan)");
        $stmtPembayaran->execute([
            ':metode' => $data->metode_pembayaran,
            ':jumlah' => $total_harga,
            ':id_pesanan' => $id_pesanan
        ]);
        
        $conn->commit();
        
        http_response_code(201);
        echo json_encode(["message" => "Pesanan berhasil dibuat", "id_pesanan" => $id_pesanan]);
        
    } catch (Exception $e) {
        $conn->rollBack();
        http_response_code(500);
        echo json_encode(["message" => "Gagal memproses pesanan: " . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Data keranjang atau user tidak valid."]);
}
?>
