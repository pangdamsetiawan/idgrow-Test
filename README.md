# Laravel Inventory Management API

Project REST API ini dibuat sebagai bagian dari tes rekrutmen Software Engineer oleh **ID-GROW (PT. Clavata Extra Sukses)**.  
Project ini dibangun menggunakan **Laravel 11** dan menggunakan **Docker** untuk kemudahan deployment.

📑 Dokumentasi API (Postman)
📎 Link Dokumentasi Postman:
https://documenter.getpostman.com/view/31181555/2sB2qcC1ET

## 📦 Fitur Utama

- Autentikasi menggunakan Bearer Token (Laravel Sanctum)
- CRUD untuk:
  - User
  - Produk
  - Lokasi
  - Mutasi
- Relasi:
  - Produk <-> Lokasi (Many-to-Many dengan pivot `stok`)
  - Mutasi <-> ProdukLokasi <-> User
- Mutasi otomatis mengubah stok
- Endpoint untuk history mutasi per produk dan user
- Dokumentasi API tersedia di Postman

---

## ⚙️ Teknologi yang Digunakan

- Laravel 11
- Sanctum (untuk autentikasi token)
- MySQL
- Docker
- Postman (untuk dokumentasi API)

---

## 🚀 Cara Install dan Menjalankan Project

### 🔧 Prasyarat

- Composer
- PHP >= 8.2
- MySQL
- Git
- Docker & Docker Compose

---

### 💻 Jalankan Secara Lokal Menggunakan XAMPP (Tanpa Docker)
1. Aktifkan Apache dan MySQL dari XAMPP

2. Clone Repository
git clone https://github.com/username/laravel-inventory-idgrow.git
cd laravel-inventory-idgrow

3. Install Dependensi
composer install
cp .env.example .env
php artisan key:generate

4. Buat Database via phpMyAdmin
Buat database baru dengan nama laravel_mutasi .

5. Edit Konfigurasi .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_mutasi
DB_USERNAME=root
DB_PASSWORD=

6. Migrasi dan Seed Database
php artisan migrate --seed

7. Jalankan Server Laravel
php artisan serve
Akses aplikasi di: http://localhost:8000


🐳 Cara MenJalankan Menggunakan Docker

1. **Copy .env File**
    cp .env.example .env

2. **Edit Variabel Database di .env**
    DB_CONNECTION=mysql
    DB_HOST=host.docker.internal
    DB_PORT=3306
    DB_DATABASE=laravel_mutasi
    DB_USERNAME=root
    DB_PASSWORD=

3. **Jalankan Docker**
    docker-compose up -d --build

4. **Masuk ke Container Laravel**
    docker exec -it app bash

5. **Install Dependensi dan Migrate**
    composer install
    php artisan key:generate
    php artisan migrate --seed

6. Akses Aplikasi 

    Laravel: http://localhost:8000

    phpMyAdmin (jika tersedia): http://localhost:8080



