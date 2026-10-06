<?php
require 'config.php';

if (isset($_GET['logout'])) { unset($_SESSION['admin']); header('Location: admin.php'); exit; }

if (isset($_POST['password'])) {
    if (hash_equals(ADMIN_PASSWORD, $_POST['password'])) { $_SESSION['admin'] = true; }
    else { $gagal = 'Password salah.'; }
}
if (empty($_SESSION['admin'])): ?>
<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin</title><link rel="stylesheet" href="assets/style.css"></head><body>
<main class="pesan"><h2>Masuk admin</h2>
<?php if (!empty($gagal)): ?><div class="alert"><?= e($gagal) ?></div><?php endif; ?>
<form method="post"><label>Password<input type="password" name="password" required></label>
<button class="btn btn-besar">Masuk</button></form></main></body></html>
<?php exit; endif;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['status'])) {
    $ok = ['baru','diproses','dikirim','selesai','batal'];
    if (in_array($_POST['status'], $ok, true)) {
        db()->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute([$_POST['status'], (int)$_POST['id']]);
    }
    header('Location: admin.php'); exit;
}
$rows = db()->query('SELECT * FROM orders ORDER BY id DESC LIMIT 200')->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Daftar pesanan</title><link rel="stylesheet" href="assets/style.css"></head><body>
<main class="admin">
<h2>Daftar pesanan <a href="?logout=1" class="btn btn-small">Keluar</a></h2>
<div class="scroll"><table>
<tr><th>#</th><th>Waktu</th><th>Pembeli</th><th>Alamat</th><th>Jml</th><th>Total</th><th>Bayar</th><th>Status</th></tr>
<?php foreach ($rows as $r): ?>
<tr>
  <td><?= (int)$r['id'] ?></td>
  <td><?= e($r['created_at']) ?></td>
  <td><?= e($r['nama']) ?><br><a href="https://wa.me/<?= e($r['whatsapp']) ?>"><?= e($r['whatsapp']) ?></a></td>
  <td><?= e($r['kota']) ?><br><?= e($r['alamat']) ?><?= $r['catatan'] ? '<br><em>' . e($r['catatan']) . '</em>' : '' ?></td>
  <td><?= (int)$r['jumlah'] ?></td>
  <td><?= rupiah((int)$r['total']) ?></td>
  <td><?= e($r['metode']) ?></td>
  <td><form method="post"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
    <select name="status" onchange="this.form.submit()">
      <?php foreach (['baru','diproses','dikirim','selesai','batal'] as $s): ?>
        <option <?= $r['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
      <?php endforeach; ?>
    </select></form></td>
</tr>
<?php endforeach; ?>
</table></div></main></body></html>
