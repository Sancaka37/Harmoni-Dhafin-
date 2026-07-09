<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include 'koneksi.php';

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->email) && !empty($data->password)) {
    $email = htmlspecialchars(strip_tags(trim($data->email)));
    
    $query = "SELECT * FROM pelanggan WHERE email = :email";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":email", $email);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $password_hash = $row['password'];
        
        if (password_verify($data->password, $password_hash)) {
            // Password correct
            http_response_code(200);
            $role = isset($row['role']) ? $row['role'] : 'pembeli';
            echo json_encode([
                "message" => "Login berhasil.",
                "user" => [
                    "id_pelanggan" => $row['id_pelanggan'],
                    "username" => $row['username'],
                    "email" => $row['email'],
                    "role" => $role,
                    "foto_profil" => $row['foto_profil'] ?? 'profile.png'
                ]
            ]);
        } else {
            http_response_code(401);
            echo json_encode(["message" => "Password salah."]);
        }
    } else {
        http_response_code(404);
        echo json_encode(["message" => "Email tidak ditemukan."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Email dan password harus diisi."]);
}
?>
