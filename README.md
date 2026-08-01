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

### Email Test

```
php artisan metrics:test-email 1 play+test@verlete.com
```

Timeframe:
```
php artisan metrics:test-email 1 play+test@verlete.com --month=2026-07
```

### Manually Queue Active Clients

Queue active clients:
```
php artisan metrics:send-monthly --month=2026-07
```

To process just the first job:
```
php artisan queue:work database \
    --queue=monthly-metrics \
    --once \
    --tries=3 \
    --timeout=180
```

To process all the jobs:
```
php artisan queue:work database \
    --queue=monthly-metrics \
    --stop-when-empty \
    --rest=30 \
    --tries=3 \
    --timeout=180
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