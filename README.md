Apakah Validasi Numeric Cukup untuk Mencegah Manipulasi Total?

1. Adelia Rizki Eka Putri
Jawaban:
Nggak cukup mengandalkan aturan validasi numeric pada field total yang dikirim dari form masih sangat rawan dan tidak aman dari manipulasi data. Hal ini karena aturan numeric hanya bertugas memastikan bahwa data yang masuk berbentuk angka, tanpa memeriksa apakah nominal tersebut benar sesuai dengan harga asli produk dan jumlah yang dibeli. Penyerang (attacker) bisa dengan mudah mengintersepsi request—misalnya lewat browser developer tools atau perintah Curl—lalu mengubah nilai total dari Rp500.000 menjadi Rp1 sebelum dikirim ke server. Karena angka Rp1 tetap berupa angka, sistem akan menganggapnya valid jika hanya menggunakan aturan numeric. Oleh karena itu, solusi yang benar adalah dengan menghitung ulang nilai total secara mandiri di sisi server (backend). Server harus mengambil ID produk dan jumlah pesanan yang dikirim pengguna, mencocokkan harga resmi yang tersimpan di dalam basis data, lalu melakukan perkalian harga secara otomatis di backend. Field total sebaiknya tidak lagi dikirimkan atau dipercayai dari input form sisi klien guna mencegah celah manipulasi harga.

2. Adinda Luluk Hanifah
Jawaban:
Validasi numeric belum cukup untuk melindungi total pembayaran dari manipulasi. Aturan itu hanya mengecek apakah nilai yang dikirim berupa angka, bukan apakah angka tersebut sesuai dengan harga produk dan jumlah yang dipesan. Dengan memanfaatkan developer tools di browser atau perintah Curl, seseorang bisa memodifikasi request dan mengubah total dari Rp100.000 menjadi Rp1, dan sistem tetap menerimanya karena nilainya masih tergolong angka. Cara yang lebih aman adalah menghitung total langsung di backend: server cukup menerima ID produk dan kuantitas, mengambil harga resmi dari database, lalu melakukan perkalian sendiri. Dengan begitu, nilai total dari form di sisi klien tidak perlu dipercaya (bahkan lebih baik tidak dikirim sama sekali), sehingga pengguna tidak punya celah untuk menentukan sendiri jumlah yang harus dibayarnya.

4. Rafi Zeta Fauzan
Jawaban:
Tidak cukup, karena aturan numeric hanya memastikan input berbentuk angka tanpa memverifikasi kebenaran nilainya. Data dari sisi klien mudah dimanipulasi menggunakan browser DevTools atau tools seperti cURL. Sebagai contoh, pembeli bisa mengubah total dari Rp1.000.000 menjadi Rp1.000; karena angka seribu tetap numerik, validasi server akan meloloskannya begitu saja.

Solusi yang tepat adalah tidak mengirimkan field total dari form sama sekali. Backend harus mengambil harga asli produk langsung dari database berdasarkan product_id, lalu menghitung totalnya sendiri di server dengan mengalikan harga tersebut dengan qt

6. Sukma Ananda Putri
Jawaban:
Tidak, validasi numeric saja tidak cukup untuk mencegah manipulasi nilai total. Validasi tersebut hanya memastikan bahwa data yang dikirim berupa angka, tetapi tidak memastikan bahwa nilai total tersebut benar sesuai dengan harga dan jumlah barang yang dibeli. Sebagai contoh, jika harga barang Rp50.000 dan jumlah pembelian 2 barang, total yang seharusnya dibayar adalah Rp100.000. Namun, pengguna dapat memanipulasi data dari form dan mengirimkan total sebesar Rp10.000. Nilai tersebut tetap lolos validasi numeric karena masih berupa angka. Oleh karena itu, nilai total sebaiknya dihitung ulang di sisi server berdasarkan harga barang dan jumlah pembelian yang tersimpan atau diperoleh secara tepercaya dari database. Dengan demikian, pengguna tidak dapat menentukan sendiri nilai total yang harus dibayar hanya dengan mengubah data pada form.

## Skenario Pengujian Manual (Otorisasi Akses)

1. Skenario Uji Peran Admin
Langkah:
- Akses halaman /login.
- Masukkan email admin@pos.test dan kata sandi password, lalu klik tombol Masuk.
- Amati bilah navigasi di bagian atas.
- Coba buka URL /products dan /categories.

Hasil yang Diharapkan:
- Login berhasil dan mengarah ke halaman POS.
- Navigasi menampilkan menu Kasir, Transaksi, Produk, Kategori, serta nama dan peran Admin  Kafe (admin).
- Halaman Produk dan Kategori dapat dibuka dengan normal.


2. Skenario Uji Peran Kasir
Langkah:
- Akses halaman /login.
- Masukkan email kasir@pos.test dan kata sandi password, lalu klik tombol Masuk.
- Amati bilah navigasi di bagian atas.
- Ketik URL /products atau /categories secara manual di address bar browser.

Hasil yang Diharapkan:
- Login berhasil dan mengarah ke halaman POS.
- Navigasi hanya menampilkan menu Kasir dan Transaksi (menu Produk dan Kategori disembunyikan).
- Akses ke URL /products atau /categories ditolak dan muncul pesan error 403 Forbidden.


3. Skenario Uji Peran Tamu (Guest / Belum Login)
Langkah:
- Pastikan status akun sudah logout atau gunakan mode Incognito di browser.
- Buka langsung URL /pos, /transactions, atau /products.

Hasil yang Diharapkan:
- Sistem menolak akses dan langsung mengalihkan (redirect) kembali ke halaman /login.

