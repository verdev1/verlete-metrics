# Verlete Metrics


## Manual Tests

* Numeric at the end of these manual test commands is client ID, eg: `php artisan analytics:test 1`
* Specific month / year --month=YYYY-MM, eg: `php artisan analytics:test 1 --month=2026-06`

### Google Analytics

```
php artisan analytics:test 1
```

Timeframe:
```
php artisan analytics:test 1 --month=2026-06
```

### Fluent Forms

```
php artisan wordpress:test-forms 1
```

Timeframe:
```
php artisan wordpress:test-forms 1 --month=2026-06
```

### Ecommerce

```
php artisan store:test 1
```

Timeframe:
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

### Tinker on Prod

Issue: "User Notice  Writing to directory /home/1305801.cloudwaysapps.com/xxyvrvpfjf/.config/psysh is not allowed."

Create a writable config directory inside Laravel’s storage folder:
```
mkdir -p storage/framework/psysh
```

Then launch Tinker with that directory:
```
XDG_CONFIG_HOME="$(pwd)/storage/framework/psysh" php artisan tinker
```