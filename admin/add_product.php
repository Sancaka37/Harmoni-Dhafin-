<?php
include '../koneksi.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $category = $_POST['category'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0;
    $stock = $_POST['stock'] ?? 0;

    if (empty($name) || empty($category) || empty($price)) {
        echo json_encode(['error' => 'Data tidak lengkap']);
        exit;
    }

    $imagePath = '';

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'avif', 'gif'];
        $fileName = $_FILES['image']['name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        if (in_array($fileExt, $allowedExts)) {
            $newFileName = uniqid() . '-' . str_replace(' ', '-', $fileName);
            $destPath = '../image/' . $newFileName;
            
            // Buat folder jika belum ada
            if (!file_exists('../image')) {
                mkdir('../image', 0777, true);
            }

            if (move_uploaded_file($_FILES['image']['tmp_name'], $destPath)) {
                $imagePath = '../image/' . $newFileName;
            } else {
                echo json_encode(['error' => 'Gagal mengupload gambar']);
                exit;
            }
        } else {
            echo json_encode(['error' => 'Format gambar tidak valid. Gunakan JPG/PNG/WEBP.']);
            exit;
        }
    } else {
        echo json_encode(['error' => 'Gambar wajib diupload']);
        exit;
    }

    try {
        $stmt = $conn->prepare("INSERT INTO produk (nama_produk, id_kategori, deskripsi, harga, stok, url_gambar) VALUES (:nama, :kategori, :deskripsi, :harga, :stok, :gambar)");
        $stmt->execute([
            ':nama' => $name,
            ':kategori' => $category,
            ':deskripsi' => $description,
            ':harga' => $price,
            ':stok' => $stock,
            ':gambar' => $imagePath
        ]);

        echo json_encode(['success' => true]);
    } catch(PDOException $e) {
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
