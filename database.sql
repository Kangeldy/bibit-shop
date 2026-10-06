CREATE DATABASE IF NOT EXISTS bibit_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bibit_shop;

CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  whatsapp VARCHAR(20) NOT NULL,
  alamat TEXT NOT NULL,
  kota VARCHAR(100) NOT NULL,
  jumlah INT NOT NULL DEFAULT 1,
  total INT NOT NULL,
  metode ENUM('cod','transfer') NOT NULL DEFAULT 'cod',
  catatan VARCHAR(255) NULL,
  status ENUM('baru','diproses','dikirim','selesai','batal') NOT NULL DEFAULT 'baru',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
