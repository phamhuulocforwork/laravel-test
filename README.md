- PHP >= 8.2
- Composer
- SQLite

## Clone project
```bash
git clone https://github.com/phamhuulocforwork/laravel-test.git
cd laravel-test
composer install
```

## Setup env & database
```bash
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

```bash
php artisan migrate
php artisan db:seed --class=ProductSeeder
```

## Run sv
```bash
php artisan serve
```

---

## Swagger Doc:
```
http://localhost:8000/api/documentation