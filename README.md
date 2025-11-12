- PHP >= 8.2
- Composer
- SQLite

Account:
usr:admin@admin.com
pw:123456

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
php artisan db:seed
```

## Run sv
```bashc
php artisan serve
```

---

## Swagger Doc:

```bash
php artisan l5-swagger
```

```
http://localhost:8000/api/documentation