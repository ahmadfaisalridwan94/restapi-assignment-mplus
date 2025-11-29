# Laravel Project README

## Requirements

- PHP 8.2+
- Laravel 12
- Composer
- MySQL
- Docker Desktop (optional, for containerized setup)

---

# 📦 Setup Menggunakan Docker

## 1. Clone Project

```bash
git clone https://github.com/ahmadfaisalridwan94/restapi-assignment-mplus.git
cd restapi-assignment-mplus
```

## 2. Copy Environment

```bash
cp .env.example .env
```

Sesuaikan dengan kebutuhan misalnya `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, ` GOOGLE_REDIRECT_URL`, `FACEBOOK_CLIENT_ID`, `FACEBOOK_CLIENT_SECRET`, ` FACEBOOK_REDIRECT_URL`

## 3. Build & Run Containers

development environment

```bash
docker compose -f docker-compose.dev.yml up -d
```

production environment

```bash
docker compose -f docker-compose.prod.yml up --build -d
```

## 4. Install Dependencies

```bash
docker exec -it laravel-app composer install
docker exec -it laravel-app php artisan key:generate
docker exec -it laravel-app php artisan jwt:secret
```

## 5. Jalankan Migration

```bash
docker exec -it laravel-app php artisan migrate
```

## 6. Akses Aplikasi

- App: http://localhost:8080

---

# 🔧 Manual Setup (tanpa Docker)

## 1. Clone Project

```bash
git clone https://github.com/ahmadfaisalridwan94/restapi-assignment-mplus.git
cd restapi-assignment-mplus/src
```

## 2. Install Composer Dependencies

```bash
composer install
```

## 3. Copy `.env`

```bash
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

Sesuaikan dengan kebutuhan misalnya `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, ` GOOGLE_REDIRECT_URL`, `FACEBOOK_CLIENT_ID`, `FACEBOOK_CLIENT_SECRET`, ` FACEBOOK_REDIRECT_URL`

## 4. Buat Database

Buat database kosong lalu sesuaikan di file `.env`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_db
DB_USERNAME=root
DB_PASSWORD=
```

## 5. Jalankan Migration

```bash
php artisan migrate
```

## 6. Jalankan Server

```bash
php artisan serve
```

Akses: http://127.0.0.1:8000

---

# 🗂 Struktur Folder

```
/docker
├── nginx/
├── php/
├── supervisor/
/src
├── app/
├── bootstrap/
├── config/
├── database/
├── public/
├── resources/
├── routes/
├── storage/
└── tests/
```

---

# Fitur Utama

- Autentikasi (JWT)
- Login SSO via Google
- Login SSO via Facebook
- REST API
- Docker Support

---

# 📄 License

Proyek ini menggunakan lisensi MIT
