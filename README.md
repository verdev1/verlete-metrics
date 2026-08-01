# verlete-metrics


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

### 


## Commands

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