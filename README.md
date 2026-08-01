# Verlete Metrics


## Manual Tests

Numeric at the end of these manual test commands is client ID.

### Google Analytics

```
php artisan analytics:test 1
```

### Fluent Forms

```
php artisan wordpress:test-forms 1
```

### Ecommerce

General test:
```
php artisan store:test 1
```

Specific time period:
```
php artisan store:test 1 --month=2026-06
```


## Laravel Helpers

Update laravel composer dependencies:
```
composer install --no-dev --optimize-autoloader
php artisan optimize:clear
php artisan optimize
```

Activate local env:
```
php artisan serve
```