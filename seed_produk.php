<?php
include 'koneksi.php';

try {
    // Tambahkan kolom terjual dan tanggal_ditambahkan jika belum ada
    try {
        $conn->exec("ALTER TABLE produk ADD COLUMN terjual INT DEFAULT 0");
    } catch(PDOException $e) {}
    try {
        $conn->exec("ALTER TABLE produk ADD COLUMN tanggal_ditambahkan DATE DEFAULT (CURRENT_DATE)");
    } catch(PDOException $e) {}

    // Bersihkan tabel sebelum seeding
    $conn->exec("SET FOREIGN_KEY_CHECKS=0");
    $conn->exec("TRUNCATE TABLE produk");
    $conn->exec("TRUNCATE TABLE kategori");
    $conn->exec("SET FOREIGN_KEY_CHECKS=1");

    // Seed Kategori
    $kategori = ['crunchy', 'chewy', 'original'];
    $stmtCat = $conn->prepare("INSERT INTO kategori (nama_kategori) VALUES (:nama)");
    $kategoriMap = [];
    foreach ($kategori as $k) {
        $stmtCat->execute([':nama' => $k]);
        $kategoriMap[$k] = $conn->lastInsertId();
    }

    // Seed Produk
    $produk = [
        ['nama' => 'Kue Kacang Original', 'deskripsi' => 'Kue kacang renyah', 'harga' => 25000, 'stok' => 100, 'kategori' => 'crunchy', 'img' => '../image/Kue Kacang Original.jpg', 'terjual' => 765, 'tgl' => '2024-05-10'],
        ['nama' => 'Pia Krispy', 'deskripsi' => 'Pia garing isi manis', 'harga' => 27000, 'stok' => 50, 'kategori' => 'crunchy', 'img' => '../image/Pia Krispy.jpg', 'terjual' => 310, 'tgl' => '2024-06-01'],
        ['nama' => 'Edamame Organik', 'deskripsi' => 'Edamame sehat organik', 'harga' => 45000, 'stok' => 80, 'kategori' => 'crunchy', 'img' => '../image/Edamame.jpg', 'terjual' => 504, 'tgl' => '2024-05-15'],
        ['nama' => 'Kripik Ubi Madu', 'deskripsi' => 'Kripik ubi manis madu', 'harga' => 45000, 'stok' => 120, 'kategori' => 'crunchy', 'img' => '../image/Kripik Ubi Madu Harmoni.jpg', 'terjual' => 180, 'tgl' => '2024-06-05'],
        ['nama' => 'Suwar-suwir Multirasa', 'deskripsi' => 'Cemilan manis suwar-suwir', 'harga' => 78000, 'stok' => 200, 'kategori' => 'chewy', 'img' => '../image/Suwar-suwir multirasa Harmoni.jpg', 'terjual' => 600, 'tgl' => '2024-04-20'],
        ['nama' => 'Soes Ori', 'deskripsi' => 'Kue soes rasa original', 'harga' => 67000, 'stok' => 60, 'kategori' => 'original', 'img' => '../image/Soes Ori .jpeg', 'terjual' => 400, 'tgl' => '2024-06-10']
    ];

    $stmtProd = $conn->prepare("INSERT INTO produk (nama_produk, deskripsi, harga, stok, url_gambar, id_kategori, terjual, tanggal_ditambahkan) VALUES (:nama, :desc, :harga, :stok, :img, :id_cat, :terjual, :tgl)");

    foreach ($produk as $p) {
        $stmtProd->execute([
            ':nama' => $p['nama'],
            ':desc' => $p['deskripsi'],
            ':harga' => $p['harga'],
            ':stok' => $p['stok'],
            ':img' => $p['img'],
            ':id_cat' => $kategoriMap[$p['kategori']],
            ':terjual' => $p['terjual'],
            ':tgl' => $p['tgl']
        ]);
    }

    echo "Seeding berhasil!";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
