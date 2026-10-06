<?php
require 'config.php';

// Hitung sisa stok nyata dari pesanan yang belum dibatalkan
$sisa = $PRODUK['stok'];
try {
    $terjual = (int) db()->query("SELECT COALESCE(SUM(jumlah),0) FROM orders WHERE status <> 'batal'")->fetchColumn();
    $sisa = max(0, $PRODUK['stok'] - $terjual);
} catch (Throwable $ex) { /* database belum dibuat: tampilkan stok awal */ }

$errors = $_SESSION['errors'] ?? [];
$old    = $_SESSION['old'] ?? [];
unset($_SESSION['errors'], $_SESSION['old']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($PRODUK['nama']) ?> | <?= e(NAMA_TOKO) ?></title>
<meta name="description" content="<?= e($PRODUK['nama']) ?>, <?= e($PRODUK['satuan']) ?>, <?= rupiah($PRODUK['harga']) ?>. Bayar di tempat atau transfer.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@600;800&family=Source+Sans+3:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>

<header class="top">
  <strong><?= e(NAMA_TOKO) ?></strong>
  <a href="#pesan" class="btn btn-small">Pesan</a>
</header>

<main>
  <section class="hero">
    <div class="gallery">
      <?php foreach ($PRODUK['foto'] as $f): ?>
        <?php if (file_exists(__DIR__ . '/' . $f)): ?>
          <img src="<?= e($f) ?>" alt="<?= e($PRODUK['nama']) ?>" loading="lazy">
        <?php else: ?>
          <div class="ph">Taruh foto di <code><?= e($f) ?></code></div>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>

    <div class="info">
      <h1><?= e($PRODUK['nama']) ?></h1>
      <p class="harga"><?= rupiah($PRODUK['harga']) ?> <span>/ <?= e($PRODUK['satuan']) ?></span></p>
      <p class="stok <?= $sisa <= 5 ? 'tipis' : '' ?>">
        <?= $sisa > 0 ? 'Sisa stok: ' . $sisa . ' paket' : 'Stok habis' ?>
      </p>
      <ul class="fitur">
        <?php foreach ($PRODUK['deskripsi'] as $d): ?><li><?= e($d) ?></li><?php endforeach; ?>
      </ul>
    </div>
  </section>

  <?php if ($ULASAN): ?>
  <section class="ulasan">
    <h2>Kata pembeli</h2>
    <?php foreach ($ULASAN as $u): ?>
      <blockquote>
        <p><?= e($u['isi']) ?></p>
        <footer><?= e($u['nama']) ?>, <?= e($u['kota']) ?></footer>
      </blockquote>
    <?php endforeach; ?>
  </section>
  <?php endif; ?>

  <section class="pesan" id="pesan">
    <h2>Isi data pengiriman</h2>

    <?php if ($errors): ?>
      <div class="alert" role="alert">
        <?php foreach ($errors as $er): ?><p><?= e($er) ?></p><?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if ($sisa > 0): ?>
    <form action="submit.php" method="post" id="form-order" novalidate>
      <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
      <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">

      <label>Nama penerima
        <input type="text" name="nama" required maxlength="100" value="<?= e($old['nama'] ?? '') ?>" autocomplete="name">
      </label>
      <label>Nomor WhatsApp
        <input type="tel" name="whatsapp" required placeholder="08123456789" value="<?= e($old['whatsapp'] ?? '') ?>" autocomplete="tel">
      </label>
      <label>Kota / Kabupaten
        <input type="text" name="kota" required maxlength="100" value="<?= e($old['kota'] ?? '') ?>">
      </label>
      <label>Alamat lengkap
        <textarea name="alamat" rows="3" required><?= e($old['alamat'] ?? '') ?></textarea>
      </label>
      <label>Jumlah paket
        <input type="number" name="jumlah" id="jumlah" min="1" max="<?= min(10, $sisa) ?>" value="<?= (int)($old['jumlah'] ?? 1) ?>" data-harga="<?= $PRODUK['harga'] ?>">
      </label>

      <fieldset>
        <legend>Cara bayar</legend>
        <label class="radio"><input type="radio" name="metode" value="cod" <?= ($old['metode'] ?? 'cod') === 'cod' ? 'checked' : '' ?>> Bayar di tempat (COD)</label>
        <label class="radio"><input type="radio" name="metode" value="transfer" <?= ($old['metode'] ?? '') === 'transfer' ? 'checked' : '' ?>> Transfer bank</label>
      </fieldset>

      <label>Catatan (opsional)
        <input type="text" name="catatan" maxlength="255" value="<?= e($old['catatan'] ?? '') ?>">
      </label>

      <div class="ringkas">
        <span>Subtotal</span> <strong id="subtotal"><?= rupiah($PRODUK['harga']) ?></strong>
        <small>Ongkir dikonfirmasi admin lewat WhatsApp sesuai kota tujuan.</small>
      </div>

      <button type="submit" class="btn btn-besar">Pesan lewat WhatsApp</button>
    </form>
    <?php else: ?>
      <p>Stok sedang habis. Hubungi kami lewat WhatsApp untuk jadwal restock.</p>
    <?php endif; ?>
  </section>
</main>

<footer class="foot">
  <p><strong><?= e(NAMA_TOKO) ?></strong> · <?= e(ALAMAT_TOKO) ?></p>
  <p><a href="https://wa.me/<?= e(WA_NUMBER) ?>">Chat WhatsApp</a></p>
  <p>Jika bibit rusak saat tiba, kirim video unboxing dalam 24 jam untuk penggantian.</p>
</footer>

<script src="assets/script.js"></script>
</body>
</html>
