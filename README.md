# 🎣 Website Wisata Pemancingan Sensasion

Website profil dan informasi wisata Sensasion, destinasi wisata keluarga terbaik di Samarinda, Kalimantan Timur.

## Team Sensasion

*Kelompok C4 Sensasion* — Sistem Informasi C 2024  

| No | Nama | NIM | GitHub |
|----|------|-----|--------|
| 1 | Muhammad Nur Alfian | 2409116105 | [@muhammadnuralfian](https://github.com/muhammadnuralfian) |
| 2 | Nanda Pesona Putri | 2409116101 | [@Nunanad](https://github.com/Nunanad) |
| 3 | Keisya Siti Nafisa Andini | 2409116115 | [@KeisyaMiaw](https://github.com/KeisyaMiaw) |
| 4 | Chaesarrio Taufiqul Hakim  | 2409116096 | [@meltedcheese16](https://github.com/meltedcheese16) |
| 5 | Jabbar Hafizh Abdillah | 2409116116 | [@1nOut](https://github.com/1nOut) |

## Deskripsi Website

**Sensasion** adalah website resmi yang menampilkan informasi lengkap tentang destinasi wisata pemancingan Sensasion di Samarinda, Kalimantan Timur. Website ini menyediakan informasi fasilitas, galeri foto, daftar harga tiket, jadwal event, sistem reservasi berbasis WhatsApp dengan QR Code, cuaca realtime, serta sistem ulasan pengunjung yang interaktif.

Website dibangun menggunakan **HTML5**, **CSS**, **Bootstrap 5** untuk tampilan responsif, **Vue.js 3** untuk komponen ulasan interaktif, **Chart.js 4** untuk grafik jam sibuk pengunjung, dan **PHP + MySQL** sebagai backend API. Data cuaca ditampilkan secara realtime melalui **OpenWeatherMap API**.



## Fitur Website

### Halaman Publik

| Halaman | Deskripsi |
|---------|-----------|
| **Beranda** | Hero section, cuaca realtime Samarinda, grafik jam sibuk pengunjung, daftar fasilitas, dan ulasan terbaru |
| **Detail** | Informasi lengkap wisata, foto kolam, jam operasional, dan daftar fasilitas |
| **Fasilitas** | Grid fasilitas dinamis yang dikelola dari panel admin |
| **Galeri** | Foto kolam dan area wisata dengan filter kategori |
| **Harga** | Daftar tiket masuk dan paket memancing beserta harganya |
| **Event** | Informasi program & aktivitas yang tersedia |
| **Reservasi** | Form reservasi online yang dikirim langsung via WhatsApp |
| **Cek Reservasi** | Cek status reservasi berdasarkan kode booking + scan QR Code |
| **Kontak** | Informasi kontak dan kirim pesan langsung via WhatsApp |

### Panel Admin

| Halaman | Deskripsi |
|---------|-----------|
| **Dashboard** | Statistik ringkas dan akses cepat ke semua fitur |
| **Reservasi** | Kelola pemesanan masuk: lihat detail, ubah status, dan ekspor data |
| **Scan QR** | Scan QR Code pengunjung untuk verifikasi reservasi secara langsung |
| **Ulasan** | CRUD ulasan pengunjung menggunakan **Vue.js 3** |
| **Fasilitas** | Tambah / Edit / Hapus fasilitas beserta upload gambar |
| **Event** | Tambah / Edit / Hapus program event |
| **Galeri** | Tambah / Edit / Hapus foto galeri dengan upload gambar |
| **Harga** | Tambah / Edit / Hapus harga tiket dengan toggle aktif/nonaktif |
| **Users** | Manajemen akun pengguna admin |
| **Settings** | Pengaturan informasi umum website |



## Teknologi yang Digunakan

| Kategori | Teknologi |
|----------|-----------|
| Frontend | HTML5, CSS3, Bootstrap 5.3, Bootstrap Icons |
| JavaScript | Vue.js 3 (CDN), Chart.js 4, Vanilla JS |
| Backend | PHP 8.0+, PDO MySQL |
| Database | MySQL / MariaDB |
| Server | Apache + mod_rewrite (Laragon) |
| API Eksternal | [OpenWeatherMap](https://openweathermap.org) (cuaca realtime Samarinda) |
| QR Code | html5-qrcode (scan), QR Server API (generate) |
| Penyimpanan | localStorage (default), MySQL (opsional via API) |


## Struktur Folder

```
sensasion/
│
├── index.html                  
├── detail.html                
├── fasilitas.html              
├── galeri.html                
├── harga.html                  
├── event.html                 
├── reservasi.html             
├── cek-reservasi.html         
├── kontak.html                 
├── .htaccess
│
├── css/
│   └── style.css               
│
├── js/
│   ├── data.js                 
│   ├── layout.js               
│   └── main.js                
│
├── api/
│   ├── config.php             
│   ├── fasilitas.php          
│   ├── galeri.php              
│   ├── ulasan.php              
│   ├── reservasi.php           
│   ├── users.php               
│   ├── kunjungan.php           
│   ├── foto_profil.php         
│   └── upload.php              
│
├── admin/                      
│   ├── index.html             
│   ├── reservasi.html          
│   ├── scan-qr.html           
│   ├── ulasan.html            
│   ├── fasilitas.html         
│   ├── event.html              
│   ├── galeri.html            
│   ├── harga.html              
│   ├── users.html              
│   ├── settings.html           
│   ├── login.html              
│   ├── .htaccess
│   ├── css/
│   │   └── admin.css           
│   └── js/
│       └── admin-layout.js    
│
├── public/
│   ├── assets/                 
│   └── qrcodes/              
│
├── uploads/                   
│
└── sensasion.sql              
```


## Tampilan Website

<img width="940" height="450" alt="image" src="https://github.com/user-attachments/assets/682832b7-598a-4e2c-b2c9-a1fd105d7ddd" />

<img width="940" height="450" alt="image" src="https://github.com/user-attachments/assets/c67f1a7f-cb12-41bb-beed-535910921485" />

<img width="940" height="456" alt="image" src="https://github.com/user-attachments/assets/643bc7ad-f731-42cc-b700-28bf3ebfe25a" />

<img width="940" height="450" alt="image" src="https://github.com/user-attachments/assets/799eb0d9-4666-479a-b5b3-90e40b7afcf4" />
