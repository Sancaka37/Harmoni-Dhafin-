<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include 'koneksi.php';

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->fullname) && !empty($data->email) && !empty($data->password)) {
    
    // Check if email already exists
    $checkQuery = "SELECT id_pelanggan FROM pelanggan WHERE email = :email";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bindParam(":email", $data->email);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() > 0) {
        http_response_code(400);
        echo json_encode(["message" => "Email sudah terdaftar."]);
        exit();
    }
    
    $query = "INSERT INTO pelanggan (username, email, password, no_telepon, role) VALUES (:username, :email, :password, :phone, :role)";
    $stmt = $conn->prepare($query);
    
    $username = htmlspecialchars(strip_tags($data->fullname));
    $email = htmlspecialchars(strip_tags($data->email));
    $phone = isset($data->phone) ? htmlspecialchars(strip_tags($data->phone)) : '';
    $role = 'pembeli'; // Supaya yang bisa daftar di register cuma sebagai customer
    
    // Hash password
    $password_hash = password_hash($data->password, PASSWORD_BCRYPT);
    
    $stmt->bindParam(":username", $username);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":password", $password_hash);
    $stmt->bindParam(":phone", $phone);
    $stmt->bindParam(":role", $role);
    
    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode(["message" => "Akun berhasil dibuat."]);
    } else {
        http_response_code(503);
        echo json_encode(["message" => "Gagal membuat akun."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Data tidak lengkap."]);
}
?>
