<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
    http_response_code(400);
    exit('Permintaan tidak valid. Kembali dan coba lagi.');
}
if (!empty($_POST['website'])) { exit; } // honeypot: bot terisi

$nama    = trim($_POST['nama'] ?? '');
$wa      = preg_replace('/\D/', '', $_POST['whatsapp'] ?? '');
$kota    = trim($_POST['kota'] ?? '');
$alamat  = trim($_POST['alamat'] ?? '');
$jumlah  = (int)($_POST['jumlah'] ?? 1);
$metode  = ($_POST['metode'] ?? '') === 'transfer' ? 'transfer' : 'cod';
$catatan = trim($_POST['catatan'] ?? '');

if (str_starts_with($wa, '0')) $wa = '62' . substr($wa, 1);

$errors = [];
if ($nama === '' || mb_strlen($nama) > 100)        $errors[] = 'Nama wajib diisi.';
if (!preg_match('/^62\d{8,13}$/', $wa))             $errors[] = 'Nomor WhatsApp tidak valid.';
if ($kota === '')                                   $errors[] = 'Kota wajib diisi.';
if (mb_strlen($alamat) < 10)                        $errors[] = 'Alamat terlalu pendek, tulis selengkap mungkin.';
if ($jumlah < 1 || $jumlah > 10)                    $errors[] = 'Jumlah harus 1 sampai 10 paket.';

if ($errors) {
    $_SESSION['errors'] = $errors;
    $_SESSION['old'] = compact('nama', 'kota', 'alamat', 'jumlah', 'metode', 'catatan') + ['whatsapp' => $_POST['whatsapp'] ?? ''];
    header('Location: index.php#pesan');
    exit;
}

$total = $PRODUK['harga'] * $jumlah; // dihitung ulang di server, bukan dari form

$stmt = db()->prepare(
    'INSERT INTO orders (nama, whatsapp, alamat, kota, jumlah, total, metode, catatan)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
);
$stmt->execute([$nama, $wa, $alamat, $kota, $jumlah, $total, $metode, $catatan ?: null]);
$id = db()->lastInsertId();

unset($_SESSION['csrf']);

$pesan = "Halo, saya mau konfirmasi pesanan #$id\n"
       . "Produk: {$PRODUK['nama']}\n"
       . "Jumlah: $jumlah paket\n"
       . "Subtotal: " . rupiah($total) . "\n"
       . "Bayar: " . strtoupper($metode) . "\n"
       . "Nama: $nama\nKota: $kota\nAlamat: $alamat"
       . ($catatan ? "\nCatatan: $catatan" : '');

header('Location: https://wa.me/' . WA_NUMBER . '?text=' . rawurlencode($pesan));
exit;
