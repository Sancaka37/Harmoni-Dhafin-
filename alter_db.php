<?php
include 'koneksi.php';

try {
    $conn->exec("ALTER TABLE produk ADD COLUMN terjual INT DEFAULT 0");
    echo "Column 'terjual' added successfully to 'produk' table.<br>";
} catch(PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column 'terjual' already exists.<br>";
    } else {
        echo "Error: " . $e->getMessage() . "<br>";
    }
}
?>
