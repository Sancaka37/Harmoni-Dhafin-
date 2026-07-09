<?php
// Izinkan akses dari frontend
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$dbUrl = getenv("DATABASE_URL");

try {
    if ($dbUrl) {
        // Mode Produksi (Vercel + Supabase PostgreSQL)
        $dbopts = parse_url($dbUrl);
        $host = $dbopts["host"];
        $port = $dbopts["port"];
        $user = $dbopts["user"];
        $pass = $dbopts["pass"];
        $dbname = ltrim($dbopts["path"], '/');
        
        $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require", $user, $pass);
    } else {
        // Mode Lokal (MySQL fallback)
        $host = "127.0.0.1";
        $username = "root";
        $password = "";
        $database = "db_harmoni";
        $conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    }
    
    // Atur mode error PDO ke Exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(["error" => "Koneksi database gagal: " . $e->getMessage()]);
    exit();
}
?>