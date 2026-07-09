-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for db_harmoni
CREATE DATABASE IF NOT EXISTS `db_harmoni` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `db_harmoni`;

-- Dumping structure for table db_harmoni.detail_pesanan
CREATE TABLE IF NOT EXISTS `detail_pesanan` (
  `id_detail_pesanan` int NOT NULL AUTO_INCREMENT,
  `kuantitas` int NOT NULL,
  `harga_satuan` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `id_pesanan` int NOT NULL,
  `id_produk` int NOT NULL,
  PRIMARY KEY (`id_detail_pesanan`),
  KEY `id_pesanan` (`id_pesanan`),
  KEY `id_produk` (`id_produk`),
  CONSTRAINT `detail_pesanan_ibfk_1` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id_pesanan`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `detail_pesanan_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table db_harmoni.detail_pesanan: ~0 rows (approximately)

-- Dumping structure for table db_harmoni.kategori
CREATE TABLE IF NOT EXISTS `kategori` (
  `id_kategori` int NOT NULL AUTO_INCREMENT,
  `nama_kategori` varchar(100) NOT NULL,
  `deskripsi_kategori` text,
  PRIMARY KEY (`id_kategori`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table db_harmoni.kategori: ~0 rows (approximately)

-- Dumping structure for table db_harmoni.pelanggan
CREATE TABLE IF NOT EXISTS `pelanggan` (
  `id_pelanggan` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `no_telepon` varchar(20) DEFAULT NULL,
  `alamat_utama` text,
  `role` varchar(20) DEFAULT 'pembeli',
  PRIMARY KEY (`id_pelanggan`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table db_harmoni.pelanggan: ~0 rows (approximately)

-- Dumping structure for table db_harmoni.pembayaran
CREATE TABLE IF NOT EXISTS `pembayaran` (
  `id_pembayaran` int NOT NULL AUTO_INCREMENT,
  `tanggal_pembayaran` timestamp NOT NULL,
  `metode_pembayaran` varchar(50) NOT NULL,
  `jumlah_bayar` decimal(12,2) NOT NULL,
  `status_pembayaran` varchar(50) NOT NULL,
  `id_pesanan` int NOT NULL,
  PRIMARY KEY (`id_pembayaran`),
  UNIQUE KEY `id_pesanan` (`id_pesanan`),
  CONSTRAINT `pembayaran_ibfk_1` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id_pesanan`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table db_harmoni.pembayaran: ~0 rows (approximately)

-- Dumping structure for table db_harmoni.pengiriman
CREATE TABLE IF NOT EXISTS `pengiriman` (
  `id_pengiriman` int NOT NULL AUTO_INCREMENT,
  `alamat_pengiriman` text NOT NULL,
  `kurir_layanan` varchar(50) NOT NULL,
  `nomor_resi` varchar(100) DEFAULT NULL,
  `status_pengiriman` varchar(50) NOT NULL,
  `id_pesanan` int NOT NULL,
  PRIMARY KEY (`id_pengiriman`),
  UNIQUE KEY `id_pesanan` (`id_pesanan`),
  CONSTRAINT `pengiriman_ibfk_1` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id_pesanan`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table db_harmoni.pengiriman: ~0 rows (approximately)

-- Dumping structure for table db_harmoni.pesanan
CREATE TABLE IF NOT EXISTS `pesanan` (
  `id_pesanan` int NOT NULL AUTO_INCREMENT,
  `tanggal_pesanan` timestamp NOT NULL,
  `total_harga` decimal(12,2) NOT NULL,
  `status_pesanan` varchar(50) NOT NULL,
  `id_pelanggan` int NOT NULL,
  PRIMARY KEY (`id_pesanan`),
  KEY `id_pelanggan` (`id_pelanggan`),
  CONSTRAINT `pesanan_ibfk_1` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table db_harmoni.pesanan: ~0 rows (approximately)

-- Dumping structure for table db_harmoni.produk
CREATE TABLE IF NOT EXISTS `produk` (
  `id_produk` int NOT NULL AUTO_INCREMENT,
  `nama_produk` varchar(150) NOT NULL,
  `deskripsi` text,
  `harga` decimal(12,2) NOT NULL,
  `stok` int NOT NULL,
  `url_gambar` varchar(255) DEFAULT NULL,
  `id_kategori` int DEFAULT NULL,
  PRIMARY KEY (`id_produk`),
  KEY `id_kategori` (`id_kategori`),
  CONSTRAINT `produk_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table db_harmoni.produk: ~0 rows (approximately)

-- Dumping structure for table db_harmoni.ulasan
CREATE TABLE IF NOT EXISTS `ulasan` (
  `id_ulasan` int NOT NULL AUTO_INCREMENT,
  `rating` int DEFAULT NULL,
  `komentar` text,
  `tanggal_ulasan` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `id_pelanggan` int NOT NULL,
  `id_produk` int NOT NULL,
  PRIMARY KEY (`id_ulasan`),
  KEY `id_pelanggan` (`id_pelanggan`),
  KEY `id_produk` (`id_produk`),
  CONSTRAINT `ulasan_ibfk_1` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `ulasan_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `ulasan_chk_1` CHECK (((`rating` >= 1) and (`rating` <= 5)))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table db_harmoni.ulasan: ~0 rows (approximately)

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
