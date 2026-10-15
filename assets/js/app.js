(function () {
  'use strict';

  // Toggle menu (burger)
  document.querySelectorAll('[data-toggle]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var t = document.querySelector(btn.getAttribute('data-toggle'));
      if (!t) return;
      var open = t.classList.toggle('open');
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  });

  // Konfirmasi sebelum aksi berbahaya
  document.querySelectorAll('form[data-confirm]').forEach(function (f) {
    f.addEventListener('submit', function (e) {
      if (!window.confirm(f.getAttribute('data-confirm'))) e.preventDefault();
    });
  });
  document.querySelectorAll('[data-confirm-btn]').forEach(function (b) {
    b.addEventListener('click', function (e) {
      if (!window.confirm(b.getAttribute('data-confirm-btn'))) e.preventDefault();
    });
  });

  // Format angka ribuan pada input harga
  document.querySelectorAll('input[data-money]').forEach(function (inp) {
    function fmt() {
      var d = inp.value.replace(/\D/g, '');
      inp.value = d === '' ? '' : d.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }
    inp.addEventListener('input', fmt);
    fmt();
  });

  // Pratinjau gambar sebelum upload
  document.querySelectorAll('input[data-preview]').forEach(function (inp) {
    inp.addEventListener('change', function () {
      var img = document.querySelector(inp.getAttribute('data-preview'));
      var f = inp.files && inp.files[0];
      if (!img || !f) return;
      img.src = URL.createObjectURL(f);
      img.hidden = false;
    });
  });

  // Galeri halaman produk
  var main = document.getElementById('main-img');
  document.querySelectorAll('.thumbs button').forEach(function (b) {
    b.addEventListener('click', function () {
      if (!main) return;
      main.src = b.getAttribute('data-shot');
      document.querySelectorAll('.thumbs button').forEach(function (x) { x.classList.remove('on'); });
      b.classList.add('on');
    });
  });

  // Pengalih ukuran viewport pada halaman demo
  var frame = document.getElementById('demo-frame');
  document.querySelectorAll('[data-vp]').forEach(function (b) {
    b.addEventListener('click', function () {
      if (!frame) return;
      frame.style.width = b.getAttribute('data-vp');
      document.querySelectorAll('[data-vp]').forEach(function (x) { x.classList.remove('on'); });
      b.classList.add('on');
    });
  });
})();
