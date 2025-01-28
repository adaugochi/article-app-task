# Article App

## Requirement

- PHP 8.0
- Laravel 11
- Composer
- MySQL
- Web server (Apache or Nginx)

## Project Setup

```shell
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:refresh 
php artisan queue:work
```

## Open Crontab 
```shell
* * * * * cd ~/<path-to-your-project>/article-app && php artisan schedule:run >> /dev/null 2>&1
```

### Run development server

```shell
php artisan serve
```

