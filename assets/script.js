(function () {
  var jumlah = document.getElementById('jumlah');
  var out = document.getElementById('subtotal');
  if (!jumlah || !out) return;
  var harga = parseInt(jumlah.dataset.harga, 10);
  function fmt(n) { return 'Rp' + n.toLocaleString('id-ID'); }
  function hitung() {
    var n = Math.max(1, Math.min(parseInt(jumlah.max, 10) || 10, parseInt(jumlah.value, 10) || 1));
    out.textContent = fmt(harga * n);
  }
  jumlah.addEventListener('input', hitung);
  hitung();
})();
