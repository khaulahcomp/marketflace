# Marketplace Digital (PHP + MySQL)

Marketplace multi-penjual untuk **produk digital** (software, website, template, script, dll).
Setiap produk punya **halaman demo** (URL demo diisi penjual) dan tombol **Beli via WhatsApp**
yang otomatis tersambung ke nomor WA penjual produk tersebut. Pembeli tidak perlu akun.

## Kebutuhan
- **PHP 7.4 atau lebih baru, tanpa batas versi maksimum** (7.4, 8.0, 8.1, 8.2, 8.3 dan seterusnya), dengan ekstensi `pdo_mysql`, `mbstring`, `gd`.
  Kode hanya memakai fitur yang ada di PHP 7.4 dan tidak memakai fungsi yang usang atau dihapus di PHP 8.x.
  Versi PHP dapat diganti kapan saja lewat **MultiPHP Manager** di cPanel.
- MySQL / MariaDB
- Apache atau LiteSpeed dengan `mod_rewrite` (standar di cPanel)

## Cara pasang di cPanel
1. Zip proyek ini, buka **File Manager** > `public_html` (atau folder subdomain/subfolder), lalu **Upload** dan **Extract**.
2. Buka **MySQL Databases**: buat database, buat user database, lalu tambahkan user ke database dengan *All Privileges*.
3. Pastikan folder `uploads/products`, `uploads/avatars`, dan `uploads/branding` dapat ditulis (izin **755**; jika upload gagal, coba 775).
4. Buka `https://domain-anda.com/install.php`, isi data database, nama situs, dan akun admin, lalu klik Pasang.
5. Installer membuat tabel, kategori awal, pengaturan, dan `app/config.php`, lalu **menghapus dirinya sendiri**.
   Jika `install.php` masih ada, hapus manual lewat File Manager.
6. Login di `https://domain-anda.com/masuk` memakai akun admin tadi, lalu atur situs di `/admin/pengaturan`.

**Akses login disembunyikan dari pembeli.** Tampilan pembeli tidak menampilkan menu Masuk, Daftar, maupun ajakan "Jual produk".
Pembeli tidak butuh akun: mereka hanya menjelajah, melihat demo, lalu menekan **Beli via WhatsApp** yang langsung membuka chat ke penjual.
Admin dan penjual masuk lewat URL langsung `/masuk` (penjual baru lewat `/daftar`; bagikan link ini hanya ke calon penjual).
Setelah login, menu Dashboard/Admin/Keluar muncul di header. Halaman `/masuk` dan `/daftar` ditandai `noindex` dan diblokir di `robots.txt`.
Untuk menutup pendaftaran penjual baru sepenuhnya, matikan **Izinkan pendaftaran** di `/admin/pengaturan`.

Dapat dipasang di subfolder (mis. `domain.com/toko`), URL menyesuaikan otomatis.

## Fitur
- Beranda, pencarian, filter kategori, urutan, halaman toko penjual
- Halaman produk: galeri gambar, deskripsi, fitur, tombol demo, tombol WhatsApp, bar beli tetap di ponsel
- Halaman demo: iframe terisolasi (sandbox), pilihan ukuran desktop/tablet/ponsel, serta tombol buka di tab baru
- Tombol WA ke `/go/wa/{slug}` mencatat prospek (maks. 1 per IP per produk per jam) lalu membuka `wa.me` dengan pesan otomatis
- Dashboard penjual: kelola produk, unggah gambar, lihat prospek dan statistik, profil toko
- Panel admin: pengguna (ban/aktifkan, persetujuan penjual), moderasi produk, kategori, pengaturan situs
- **Logo situs**: admin mengunggah, mengganti, atau menghapus logo di `/admin/pengaturan` (bagian "Logo situs"). Logo tampil di header, footer, dan sidebar dashboard, serta dipakai sebagai ikon tab browser dan gambar saat tautan dibagikan. Format JPG/PNG/WEBP maks 2 MB (disarankan PNG transparan, rasio memanjang). Nama situs di samping logo bisa disembunyikan. Tanpa logo, ikon bawaan dipakai.
- **Akun admin**: di `/admin/akun` admin dapat mengganti **username** dan **password**. Setiap perubahan wajib memasukkan password saat ini, dan percobaan password salah dibatasi seperti halaman masuk. Setelah username diganti, login bisa memakai username baru atau email.
- **Harga coret & diskon**: penjual cukup mengisi nominal **Harga coret** (harga lama) di form produk. Persentase potongan dihitung otomatis dari selisih dengan harga jual dan tampil sebagai badge "-XX%" serta "Hemat XX%". Harga coret harus lebih tinggi dari harga jual.
- **Flash sale berbatas waktu**: penjual mengisi **Harga flash sale**, waktu **Mulai** (boleh kosong = langsung aktif) dan **Berakhir** (zona WIB; tombol durasi cepat +1 jam s/d +7 hari tersedia, maks. 90 hari). Saat aktif, kartu produk diberi bingkai merah, pita "FLASH SALE", badge diskon, dan **hitung mundur** yang berjalan di browser; halaman produk menampilkan kotak flash sale dengan hitung mundur Hari/Jam/Menit/Detik, dan bilah beli di ponsel menampilkan harga + sisa waktu. Spanduk flash sale muncul di atas semua halaman publik, beranda punya bagian **Flash Sale** tersendiri, dan daftar produk punya filter "Hanya flash sale" (`/produk?flash=1`). Setelah waktunya habis, harga kembali normal otomatis dan halaman memuat ulang sendiri. Flash sale yang belum mulai menampilkan teaser "segera dimulai" di halaman produk.
  Filter dan urutan harga di daftar produk memakai harga yang berlaku saat itu (harga flash bila sedang aktif).
- Template pesan WA dapat diatur admin dengan variabel `{toko} {produk} {harga} {situs} {link}`. `{harga}` otomatis memakai harga yang berlaku dan menambahkan keterangan bila sedang flash sale atau diskon.
- SEO dasar: `sitemap.xml`, `robots.txt`, judul dan meta per halaman

## Memperbarui instalasi lama
Cukup timpa berkas dengan versi terbaru. Kolom baru (`compare_price`, `flash_price`, `flash_start`, `flash_end`)
ditambahkan **otomatis sekali** saat halaman pertama dibuka (versi skema dicatat di tabel `settings`, kunci `schema_version`).
Data produk lama tidak berubah. Jika hosting tidak mengizinkan `ALTER TABLE`, fitur ini disembunyikan dan aplikasi tetap berjalan;
detail kesalahan ada di `app/error.log`. Alternatif manual: impor ulang bagian `products` dari `schema.sql` atau jalankan `ALTER TABLE` yang setara lewat phpMyAdmin.

## Pengaturan penting (`/admin/pengaturan`)
- **Moderasi produk** (`moderate_products`, default aktif): produk penjual tayang setelah disetujui admin
- **Persetujuan penjual** (`seller_approval`, default nonaktif): penjual baru harus disetujui sebelum bisa menjual
- **Izinkan pendaftaran**, warna aksen, nama situs, tagline, template pesan WA

## Keamanan
- Query memakai PDO prepared statements; password memakai `password_hash`
- Token CSRF di semua form POST, pembatasan percobaan login, honeypot di pendaftaran
- Upload gambar divalidasi (`getimagesize`), lalu di-encode ulang dengan GD (JPG/PNG/WEBP, maks. 2 MB);
  folder `uploads/` memblokir eksekusi script
- Folder `app/` dan berkas `.sql/.md/.log` tidak dapat diakses dari browser
- Header keamanan (nosniff, SAMEORIGIN, Referrer-Policy, Permissions-Policy)

## Catatan
- **Iframe demo**: situs demo yang mengirim header `X-Frame-Options`/CSP `frame-ancestors` akan menolak tampil di iframe.
  Tombol "Buka di tab baru" selalu tersedia sebagai cadangan. Saran ke penjual: pakai demo di domain/subdomain milik sendiri tanpa blokir iframe.
- **Batas upload**: bila gambar besar gagal, naikkan `upload_max_filesize` dan `post_max_size` di *MultiPHP INI Editor* cPanel.
- Jika terjadi error, detail dicatat di `app/error.log` (tidak tampil ke pengunjung).
- Cadangkan database secara berkala lewat phpMyAdmin (Export).

## Ide pengembangan lanjutan
Notifikasi email, ulasan dan rating, keranjang/pembayaran online (Midtrans/Xendit), kupon, file unduhan berlisensi,
statistik kunjungan per produk, dukungan banyak bahasa.
