<?php
include 'koneksi.php';

try {
    $email = 'admin@harmoni.com';
    $password = 'admin123';
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $nama = 'Administrator';
    $telepon = '0800000000';
    $alamat = 'Kantor Pusat Harmoni';
    $role = 'admin';

    // Cek apakah admin sudah ada
    $stmtCheck = $conn->prepare("SELECT * FROM pelanggan WHERE email = :email");
    $stmtCheck->execute([':email' => $email]);
    
    if($stmtCheck->rowCount() > 0) {
        echo "Akun admin sudah ada!";
    } else {
        $stmt = $conn->prepare("INSERT INTO pelanggan (username, email, password, no_telepon, alamat_utama, role) VALUES (:nama, :email, :password, :telepon, :alamat, :role)");
        $stmt->execute([
            ':nama' => $nama,
            ':email' => $email,
            ':password' => $hashed_password,
            ':telepon' => $telepon,
            ':alamat' => $alamat,
            ':role' => $role
        ]);
        echo "Akun admin berhasil dibuat!";
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
