<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);
include '../koneksi.php';

try {
    // 1. Orders
    $stmtOrders = $conn->query("SELECT p.id_pesanan as id, pel.username as customer, pel.email, p.tanggal_pesanan, p.total_harga as total, p.status_pesanan as status, pm.metode_pembayaran as payment
                                FROM pesanan p
                                JOIN pelanggan pel ON p.id_pelanggan = pel.id_pelanggan
                                LEFT JOIN pembayaran pm ON p.id_pesanan = pm.id_pesanan
                                ORDER BY p.tanggal_pesanan DESC");
    $db_orders = $stmtOrders->fetchAll(PDO::FETCH_ASSOC);
    
    $orders = [];
    $totalPendapatan = 0;
    foreach ($db_orders as $o) {
        $timestamp = strtotime($o['tanggal_pesanan']);
        $orders[] = [
            "id" => "#HRM-" . str_pad($o['id'], 4, '0', STR_PAD_LEFT),
            "customer" => $o['customer'],
            "email" => $o['email'],
            "date" => date('d M Y', $timestamp),
            "time" => date('H:i', $timestamp),
            "payment" => $o['payment'] ? $o['payment'] : 'Transfer',
            "total" => intval($o['total']),
            "status" => $o['status']
        ];
    }
    $stmtRev = $conn->query("
        SELECT 
            SUM(total_harga) as all_time,
            SUM(CASE WHEN EXTRACT(MONTH FROM tanggal_pesanan) = EXTRACT(MONTH FROM CURRENT_DATE) AND EXTRACT(YEAR FROM tanggal_pesanan) = EXTRACT(YEAR FROM CURRENT_DATE) THEN total_harga ELSE 0 END) as this_month,
            SUM(CASE WHEN EXTRACT(MONTH FROM tanggal_pesanan) = EXTRACT(MONTH FROM CURRENT_DATE - INTERVAL '1 month') AND EXTRACT(YEAR FROM tanggal_pesanan) = EXTRACT(YEAR FROM CURRENT_DATE - INTERVAL '1 month') THEN total_harga ELSE 0 END) as last_month
        FROM pesanan
        WHERE status_pesanan != 'Dibatalkan'
    ");
    $revData = $stmtRev->fetch(PDO::FETCH_ASSOC);
    $totalPendapatan = intval($revData['all_time']);
    $thisMonthRevenue = intval($revData['this_month']);
    $lastMonthRevenue = intval($revData['last_month']);
    
    // 2. Products
    $stmtProd = $conn->query("SELECT p.id_produk as id, p.nama_produk as name, k.nama_kategori as category, p.harga as price, p.stok as stock, p.url_gambar as image, 
                                     (SELECT COALESCE(SUM(dp.kuantitas), 0) FROM detail_pesanan dp JOIN pesanan pes ON dp.id_pesanan = pes.id_pesanan WHERE dp.id_produk = p.id_produk AND pes.status_pesanan != 'Dibatalkan') as terjual
                              FROM produk p
                              LEFT JOIN kategori k ON p.id_kategori = k.id_kategori");
    $db_products = $stmtProd->fetchAll(PDO::FETCH_ASSOC);
    $products = [];
    foreach ($db_products as $p) {
        $status = 'in';
        if ($p['stock'] == 0) $status = 'out';
        else if ($p['stock'] < 5) $status = 'low';
        
        // Correct image path for admin dashboard
        $img = str_replace('../image', '../image', $p['image']); 
        
        $products[] = [
            "id" => $p['id'],
            "name" => $p['name'],
            "category" => $p['category'] ? ucfirst($p['category']) : 'Umum',
            "sku" => 'HRM-' . str_pad($p['id'], 3, '0', STR_PAD_LEFT),
            "price" => intval($p['price']),
            "stock" => intval($p['stock']),
            "status" => $status,
            "image" => $img,
            "terjual" => intval($p['terjual'])
        ];
    }
    
    // 3. Customers
    $stmtCust = $conn->query("SELECT pel.id_pelanggan, pel.username as name, pel.email, pel.role,
                                     (SELECT COUNT(*) FROM pesanan p WHERE p.id_pelanggan = pel.id_pelanggan) as orders
                              FROM pelanggan pel
                              WHERE pel.role != 'admin'");
    $db_cust = $stmtCust->fetchAll(PDO::FETCH_ASSOC);
    $customers = [];
    $colors = ['', 'a2', 'a3', 'a4'];
    foreach ($db_cust as $i => $c) {
        $initials = substr(strtoupper($c['name']), 0, 2);
        $customers[] = [
            "name" => $c['name'],
            "type" => 'Individu',
            "tag" => 'Pelanggan',
            "email" => $c['email'],
            "detail" => 'Terdaftar',
            "orders" => intval($c['orders']),
            "lastLogin" => 'Terbaru',
            "avatar" => $initials,
            "avatarClass" => $colors[$i % 4]
        ];
    }

    // 4. Chart Data
    $filter = isset($_GET['filter']) ? $_GET['filter'] : 'year';
    $chartData = [];
    
    if ($filter == 'month') {
        $stmtChart = $conn->query("
            SELECT EXTRACT(DAY FROM tanggal_pesanan) as d, SUM(total_harga) as total
            FROM pesanan
            WHERE status_pesanan != 'Dibatalkan' AND EXTRACT(MONTH FROM tanggal_pesanan) = EXTRACT(MONTH FROM CURRENT_DATE) AND EXTRACT(YEAR FROM tanggal_pesanan) = EXTRACT(YEAR FROM CURRENT_DATE)
            GROUP BY 1
        ");
        $db_chart = $stmtChart->fetchAll(PDO::FETCH_ASSOC);
        
        $weeks = [0, 0, 0, 0];
        foreach ($db_chart as $row) {
            $d = intval($row['d']);
            if ($d <= 7) $weeks[0] += intval($row['total']);
            else if ($d <= 14) $weeks[1] += intval($row['total']);
            else if ($d <= 21) $weeks[2] += intval($row['total']);
            else $weeks[3] += intval($row['total']);
        }
        
        $lastDay = date('t');
        $chartData = [
            ["month" => "Minggu 1 (1-7)", "total" => $weeks[0]],
            ["month" => "Minggu 2 (8-14)", "total" => $weeks[1]],
            ["month" => "Minggu 3 (15-21)", "total" => $weeks[2]],
            ["month" => "Minggu 4 (22-$lastDay)", "total" => $weeks[3]]
        ];
    } else {
        $stmtChart = $conn->query("
            SELECT EXTRACT(MONTH FROM tanggal_pesanan) as m, EXTRACT(YEAR FROM tanggal_pesanan) as y, SUM(total_harga) as total
            FROM pesanan
            WHERE status_pesanan != 'Dibatalkan' AND tanggal_pesanan >= CURRENT_DATE - INTERVAL '6 month'
            GROUP BY 2, 1
            ORDER BY 2 ASC, 1 ASC
        ");
        $db_chart = $stmtChart->fetchAll(PDO::FETCH_ASSOC);
        
        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        
        for ($i = 5; $i >= 0; $i--) {
            $time = strtotime("-$i months");
            $m = intval(date('n', $time));
            $y = intval(date('Y', $time));
            
            $total = 0;
            foreach ($db_chart as $row) {
                if (intval($row['m']) == $m && intval($row['y']) == $y) {
                    $total = intval($row['total']);
                    break;
                }
            }
            
            $chartData[] = [
                "month" => $monthNames[$m - 1],
                "total" => $total
            ];
        }
    }

    // 5. Categories
    $stmtCat = $conn->query("SELECT id_kategori, nama_kategori FROM kategori");
    $categories = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "orders" => $orders,
        "products" => $products,
        "customers" => $customers,
        "chart" => $chartData,
        "categories" => $categories,
        "stats" => [
            "totalRevenue" => $totalPendapatan,
            "thisMonthRevenue" => $thisMonthRevenue,
            "lastMonthRevenue" => $lastMonthRevenue,
            "totalOrders" => count($orders),
            "totalCustomers" => count($customers)
        ]
    ]);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>
