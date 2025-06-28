# API SKY CLUB
`Penyedia Jasa Sewa Lapangan Murah Bogor`
## Deskripsi
API SKY CLUB adalah API yang digunakan untuk mengelola data lapangan, booking, dan user pada sistem penyewaan lapangan SKY CLUB. API ini dibangun menggunakan framework Laravel dan menyediakan berbagai endpoint untuk melakukan operasi CRUD (Create, Read, Update, Delete) pada data lapangan, booking, dan user.
## Cara Menggunakan
1. Clone repository ini ke dalam komputer Anda.
2. Buka terminal dan masuk ke direktori project.
3. Jalankan perintah `composer install` untuk menginstal semua dependensi yang diperlukan.
4. Buat file `.env` dengan menyalin file `.env.example` dan sesuaikan konfigurasi database dan pengaturan lainnya sesuai kebutuhan Anda.
5. Generate kode aplikasi dengan menjalankan perintah `php artisan key:generate`.
6. Jalankan migrasi database dengan perintah `php artisan migrate` untuk membuat tabel-tabel yang diperlukan.
7. Jalankan seeder untuk mengisi data awal dengan perintah `php artisan db:seed`.
8. Jalankan server lokal dengan perintah `php artisan serve --port=<masukan port komputer yang tersedia (misal 8001)>`.
9. Akses API melalui URL `http://localhost:<port>/api/` di browser atau menggunakan aplikasi seperti Postman.
10. Atau gunakan dokumenentasi API yang bisa di akses dengan URL `http://api.skyclub.my.id/api/docs/api` untuk melihat semua endpoint yang tersedia.

## Buka Dokumentasi API
### `Buka link http://api.skyclub.my.id/api/docs/api`
- [Dokumentasi API](http://api.skyclub.my.id/api/docs/api)
