<?php
require 'koneksi.php';
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Metode request tidak diizinkan.']);
    exit();
}

$id_pelanggan = isset($_POST['id_pelanggan']) ? intval($_POST['id_pelanggan']) : 0;
if (!$id_pelanggan) {
    echo json_encode(['error' => 'ID Pelanggan tidak ditemukan.']);
    exit();
}

if (!isset($_FILES['foto_profil']) || $_FILES['foto_profil']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['error' => 'Gagal mengunggah gambar atau gambar tidak ditemukan.']);
    exit();
}

$file = $_FILES['foto_profil'];
$fileSize = $file['size'];
$tmpName = $file['tmp_name'];
$fileName = $file['name'];

// Check size (max 2MB)
if ($fileSize > 2097152) {
    echo json_encode(['error' => 'Ukuran gambar maksimal 2MB.']);
    exit();
}

// Check extension
$validExtensions = ['jpg', 'jpeg', 'png', 'gif'];
$fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
if (!in_array($fileExtension, $validExtensions)) {
    echo json_encode(['error' => 'Format file harus JPG, JPEG, PNG, atau GIF.']);
    exit();
}

// Generate new unique name and save
$newFileName = 'profile_' . $id_pelanggan . '_' . time() . '.' . $fileExtension;

$supabaseUrl = getenv('SUPABASE_URL');
$supabaseKey = getenv('SUPABASE_KEY');
$isSupabase = ($supabaseUrl && $supabaseKey);

$uploadSuccess = false;
$finalFileUrl = $newFileName; // Default fallback local filename

if ($isSupabase) {
    // Upload to Supabase Storage Bucket 'profil'
    $bucketUrl = rtrim($supabaseUrl, '/') . '/storage/v1/object/profil/' . $newFileName;
    $fileData = file_get_contents($tmpName);
    
    $ch = curl_init($bucketUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fileData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer " . $supabaseKey,
        "apikey: " . $supabaseKey,
        "Content-Type: " . mime_content_type($tmpName)
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        $uploadSuccess = true;
        // Save the full public URL to database
        $finalFileUrl = rtrim($supabaseUrl, '/') . '/storage/v1/object/public/profil/' . $newFileName;
    } else {
        echo json_encode(['error' => 'Gagal mengunggah ke Supabase Storage: ' . $response]);
        exit();
    }
} else {
    // Local upload fallback
    $uploadDir = 'image/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    $destination = $uploadDir . $newFileName;
    if (move_uploaded_file($tmpName, $destination)) {
        $uploadSuccess = true;
    } else {
        echo json_encode(['error' => 'Gagal menyimpan file ke server lokal.']);
        exit();
    }
}

if ($uploadSuccess) {
    try {
        // Get old photo to delete it later if it's not the default
        $stmtCheck = $conn->prepare("SELECT foto_profil FROM pelanggan WHERE id_pelanggan = :id");
        $stmtCheck->bindParam(':id', $id_pelanggan);
        $stmtCheck->execute();
        $oldPhoto = $stmtCheck->fetchColumn();

        // Update database
        $stmtUpdate = $conn->prepare("UPDATE pelanggan SET foto_profil = :foto WHERE id_pelanggan = :id");
        $stmtUpdate->bindParam(':foto', $finalFileUrl);
        $stmtUpdate->bindParam(':id', $id_pelanggan);
        $stmtUpdate->execute();
        
        // Remove old file if it exists, is not default, and is a local file (doesn't start with http)
        if (!$isSupabase && $oldPhoto && $oldPhoto !== 'profile.png' && strpos($oldPhoto, 'http') !== 0 && file_exists('image/uploads/' . $oldPhoto)) {
            unlink('image/uploads/' . $oldPhoto);
        }

        echo json_encode(['success' => true, 'foto_profil' => $finalFileUrl]);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Gagal mengupdate database: ' . $e->getMessage()]);
    }
}
?>
