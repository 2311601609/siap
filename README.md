<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sistem Informasi Akuntansi Perusahaan (SIAP)</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      line-height: 1.6;
      margin: 20px;
      background-color: #f4f4f9;
      color: #333;
    }
    h1, h2, h3 {
      color: #0078d7;
    }
    a {
      color: #0078d7;
      text-decoration: none;
    }
    a:hover {
      text-decoration: underline;
    }
    ul {
      margin: 10px 0;
      padding: 0 20px;
    }
    .code {
      background-color: #eaeaea;
      padding: 10px;
      border-radius: 5px;
      font-family: "Courier New", Courier, monospace;
      margin: 10px 0;
    }
    footer {
      margin-top: 30px;
      font-size: 0.9em;
      text-align: center;
      color: #555;
    }
  </style>
</head>
<body>
  <header>
    <h1>Sistem Informasi Akuntansi Perusahaan (SIAP)</h1>
  </header>
  
  <section>
    <h2>Deskripsi</h2>
    <p><strong>Sistem Informasi Akuntansi Perusahaan (SIAP)</strong> adalah solusi digital yang dirancang untuk meningkatkan efisiensi dan akurasi pengelolaan data keuangan perusahaan. Proyek ini dikembangkan sebagai bagian dari tugas mata kuliah <em>Perancangan Perangkat Lunak</em> di Program Studi Magister Ilmu Komputer, Universitas Budi Luhur.</p>
  </section>

  <section>
    <h2>Fitur Utama</h2>
    <ul>
      <li><strong>Penganggaran</strong>: Monitoring realisasi anggaran secara real-time.</li>
      <li><strong>Kontrak</strong>: Pengelolaan data kontrak pelanggan.</li>
      <li><strong>Penagihan (Akrual)</strong>: Otomasi penagihan dan pelacakan piutang.</li>
      <li><strong>Penerimaan</strong>: Pencatatan penerimaan kas dan bank.</li>
      <li><strong>Uang Muka Kegiatan (UMK)</strong>: Manajemen uang muka kegiatan tertentu.</li>
      <li><strong>Pengeluaran (BUK)</strong>: Pencatatan pengeluaran kas dan bank.</li>
      <li><strong>Kas Kecil</strong>: Pengelolaan transaksi kas kecil dan rekonsiliasi kas besar.</li>
      <li><strong>Laporan Keuangan</strong>: Generasi laporan otomatis seperti neraca, laba rugi, dan arus kas.</li>
    </ul>
  </section>

  <section>
    <h2>Keunggulan Utama</h2>
    <ul>
      <li><strong>COA Dinamis</strong>: Pembuatan akun otomatis yang ramah pengguna non-akuntansi.</li>
      <li><strong>Integrasi Modul</strong>: Semua data terupdate otomatis di seluruh modul.</li>
      <li><strong>Aksesibilitas</strong>: Dapat diakses kapan saja melalui perangkat terhubung internet.</li>
      <li><strong>Keamanan</strong>: Perlindungan data keuangan yang kuat.</li>
    </ul>
  </section>

  <section>
    <h2>Tujuan Pengembangan</h2>
    <ul>
      <li>Meningkatkan efisiensi melalui otomasi proses akuntansi.</li>
      <li>Memastikan akurasi data dengan sistem yang terintegrasi.</li>
      <li>Menyediakan laporan keuangan untuk pengambilan keputusan yang lebih baik.</li>
      <li>Meningkatkan transparansi bagi stakeholder.</li>
    </ul>
  </section>

  <section>
    <h2>Instalasi</h2>
    <p>Ikuti langkah-langkah berikut untuk menjalankan proyek ini:</p>
    <div class="code">
      git clone https://github.com/2311601609/siap.git
    </div>
    <p>Instalasi dependensi backend dan frontend, atur file konfigurasi seperti <code>.env</code>, lalu jalankan migrasi:</p>
    <div class="code">
      php artisan migrate --seed
    </div>
    <p>Jalankan server lokal:</p>
    <div class="code">
      php artisan serve
    </div>
  </section>

  <section>
    <h2>Kontribusi</h2>
    <p>Kami terbuka untuk kontribusi. Silakan kirimkan <em>pull request</em> atau ajukan masalah di halaman <a href="https://github.com/2311601609/siap/issues">Issues</a>.</p>
  </section>

  <section>
    <h2>Lisensi</h2>
    <p>Proyek ini dilisensikan di bawah <a href="LICENSE">Universitas Budi Luhur</a>.</p>
  </section>

  <footer>
    <p>© 2024 Eko Firmansyah - Hanafi Firdaus - Universitas Budi Luhur</p>
  </footer>
</body>
</html>
