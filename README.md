# 📊 Aplikasi Tarik Data Mesin Absensi Fingerprint

Project ini adalah aplikasi berbasis **PHP + HTML + Bootstrap** untuk menampilkan data absensi dari mesin fingerprint melalui protokol SOAP (`fsockopen`).  
Aplikasi ini memungkinkan admin untuk memilih mesin, memfilter data berdasarkan **UserID, bulan, dan tahun**, lalu menampilkan hasilnya dalam bentuk tabel.

---

## ✨ Fitur Utama
- Pilih mesin fingerprint berdasarkan IP address.
- Filter data absensi:
  - Berdasarkan **UserID**.
  - Berdasarkan **bulan**.
  - Berdasarkan **tahun**.
- Tabel absensi interaktif dengan desain menggunakan Bootstrap.
- Menampilkan **UserID, Tanggal & Jam, Verified, dan Status**.

---

## 📂 Struktur Project
.
├── index.php # Halaman utama (form + tabel absensi)
└── README.md # Dokumentasi project


---

## 🛠️ Teknologi yang Digunakan
- **PHP** (untuk koneksi ke mesin fingerprint & proses data)
- **Bootstrap 5.3** (untuk tampilan UI)
- **HTML + CSS** (struktur & styling tambahan)

---

## 🚀 Cara Menjalankan
1. Pastikan sudah menginstall:
   - PHP (>=7.4)
   - Web server (Apache/Nginx) atau gunakan Laragon/XAMPP.
2. Clone repository ini:
   ```bash
   git clone https://github.com/username/nama-repo.git
3. Letakkan folder project di direktori server (misalnya: htdocs atau www).

4. Akses melalui browser:
    http://localhost/nama-repo/index.php

5. Pilih mesin fingerprint yang tersedia, lalu gunakan filter untuk melihat data absensi.


## ⚙️ Konfigurasi

- **Daftar mesin fingerprint** dapat diubah di bagian `<select>` dengan opsi IP:
  ```php
  <option value="192.168.1.100">Mesin Finger 1</option>
  <option value="192.168.1.101">Mesin Finger 2</option>

2. Port default yang digunakan adalah 80.
3. Kunci komunikasi (ComKey) diset ke 0 secara default.


## ⚠️ Catatan

- Pastikan mesin fingerprint berada satu jaringan dengan server yang menjalankan aplikasi.
- Jika koneksi gagal, akan muncul pesan "Koneksi Gagal ke Mesin Fingerprint".
- Script ini menggunakan SOAP request manual via fsockopen, bukan library eksternal.
