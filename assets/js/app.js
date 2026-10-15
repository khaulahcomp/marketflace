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

  /* ---------- Hitung mundur flash sale ---------- */
  // Server mengirim sisa detik (data-left); dihitung mundur di browser memakai jam monotonik
  // sehingga tidak terpengaruh jam perangkat pengunjung yang salah.
  (function () {
    var els = document.querySelectorAll('.cd[data-left]');
    if (!els.length) return;
    var now = (window.performance && performance.now) ? function () { return performance.now(); } : function () { return Date.now(); };
    var t0 = now();
    var items = [];
    var reloading = false;
    function pad(n) { return n < 10 ? '0' + n : String(n); }

    els.forEach(function (el) {
      var left = parseInt(el.getAttribute('data-left'), 10) || 0;
      items.push({
        el: el,
        left: left,
        full: el.getAttribute('data-mode') === 'full',
        done: left <= 0, // yang sejak awal 0 tidak memicu muat ulang (hindari perulangan)
        u: {}
      });
      el.querySelectorAll('[data-u]').forEach(function (b) { items[items.length - 1].u[b.getAttribute('data-u')] = b; });
      items[items.length - 1].t = el.querySelector('.cd-t');
    });

    function scheduleReload() {
      if (reloading) return;
      try {
        var last = parseInt(sessionStorage.getItem('mf_cd_reload') || '0', 10);
        if (Date.now() - last < 15000) return;
        sessionStorage.setItem('mf_cd_reload', String(Date.now()));
      } catch (e) { /* sessionStorage tidak tersedia: tetap muat ulang sekali */ }
      reloading = true;
      setTimeout(function () { location.reload(); }, 1500);
    }

    function tick() {
      var elapsed = Math.floor((now() - t0) / 1000);
      var ended = false;
      items.forEach(function (it) {
        var s = Math.max(0, it.left - elapsed);
        var d = Math.floor(s / 86400), h = Math.floor((s % 86400) / 3600), m = Math.floor((s % 3600) / 60), sec = s % 60;
        if (it.full) {
          if (it.u.d) it.u.d.textContent = pad(d);
          if (it.u.h) it.u.h.textContent = pad(h);
          if (it.u.m) it.u.m.textContent = pad(m);
          if (it.u.s) it.u.s.textContent = pad(sec);
        } else if (it.t) {
          it.t.textContent = (d > 0 ? d + 'h ' : '') + pad(h) + ':' + pad(m) + ':' + pad(sec);
        }
        it.el.classList.toggle('is-urgent', s > 0 && s <= 3600);
        if (s === 0 && !it.done) {
          it.done = true;
          it.el.classList.add('is-ended');
          var card = it.el.closest('[data-flash-card]');
          if (card) card.classList.add('flash-over');
          ended = true;
        }
      });
      if (ended) scheduleReload();
    }
    tick();
    setInterval(tick, 1000);
  })();

  /* ---------- Form produk: pratinjau harga, durasi cepat ---------- */
  (function () {
    var box = document.querySelector('[data-sale-form]');
    if (!box) return;
    var priceEl = document.getElementById('price');
    var cmpEl = document.getElementById('compare_price');
    var flEl = document.getElementById('flash_price');
    var stEl = document.getElementById('flash_start');
    var enEl = document.getElementById('flash_end');
    var out = box.querySelector('[data-preview-out]');
    if (!priceEl || !cmpEl || !flEl || !out) return;

    function num(el) { return parseInt((el.value || '').replace(/\D/g, '') || '0', 10) || 0; }
    function rp(n) { return 'Rp ' + String(n).replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }
    function mk(tag, cls, txt) {
      var e = document.createElement(tag);
      if (cls) e.className = cls;
      if (txt) e.textContent = txt;
      return e;
    }

    function render() {
      var price = num(priceEl), cmp = num(cmpEl), fl = num(flEl);
      var flash = price > 0 && fl > 0 && fl < price;
      var cur = flash ? fl : price;
      var strike = 0;
      if (flash) strike = cmp > fl ? cmp : price;
      else if (price > 0 && cmp > price) strike = cmp;
      var pct = (strike > cur && cur > 0) ? Math.min(99, Math.max(1, Math.round((strike - cur) / strike * 100))) : 0;

      out.textContent = '';
      out.className = 'pr pr-detail' + (flash ? ' is-flash' : '') + (pct ? ' has-disc' : '');
      if (cur <= 0) { out.appendChild(mk('span', 'muted', 'Isi harga untuk melihat pratinjau.')); return; }
      if (strike > cur) {
        var was = mk('span', 'was');
        was.appendChild(mk('del', '', rp(strike)));
        out.appendChild(was);
      }
      var row = mk('span', 'row');
      row.appendChild(mk('strong', 'price now', rp(cur)));
      if (pct) row.appendChild(mk('span', 'save-pill', 'Hemat ' + pct + '%'));
      out.appendChild(row);
      var note = '';
      if (flash) note = 'Tampilan saat flash sale aktif.';
      else if (fl > 0 && price > 0 && fl >= price) note = 'Harga flash sale harus lebih rendah dari harga jual.';
      else if (cmp > 0 && price > 0 && cmp <= price) note = 'Harga coret harus lebih tinggi dari harga jual.';
      if (note) out.appendChild(mk('small', 'pr-note', note));
    }
    [priceEl, cmpEl, flEl].forEach(function (el) { el.addEventListener('input', render); });
    render();

    // Durasi cepat berbasis jam server (WIB), bukan jam perangkat
    function parseLocal(v) {
      var m = /^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2})/.exec(v || '');
      return m ? Date.UTC(+m[1], +m[2] - 1, +m[3], +m[4], +m[5]) : null;
    }
    function fmtLocal(ms) {
      var d = new Date(ms);
      function p(n) { return n < 10 ? '0' + n : String(n); }
      return d.getUTCFullYear() + '-' + p(d.getUTCMonth() + 1) + '-' + p(d.getUTCDate()) + 'T' + p(d.getUTCHours()) + ':' + p(d.getUTCMinutes());
    }
    box.querySelectorAll('[data-dur]').forEach(function (b) {
      b.addEventListener('click', function () {
        var base = parseLocal(stEl && stEl.value) || parseLocal(box.getAttribute('data-now'));
        if (base === null || !enEl) return;
        enEl.value = fmtLocal(base + parseInt(b.getAttribute('data-dur'), 10) * 60000);
      });
    });
    var clr = box.querySelector('[data-clear-flash]');
    if (clr) clr.addEventListener('click', function () {
      flEl.value = ''; if (stEl) stEl.value = ''; if (enEl) enEl.value = '';
      render();
    });
  })();

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
