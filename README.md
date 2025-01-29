# Article App

## Requirement

- PHP 8.0
- Composer
- MySQL
- Web server (Apache or Nginx)

## Project Setup

```shell
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:refresh 
```

## Get API Key
Get the API key for the data sources; [News API](https://newsapi.org/) , [New York Time](https://developer.nytimes.com/docs/articlesearch-product/1/overview) and [The Guardian](https://open-platform.theguardian.com/access/). Proceed to update the `.env` variables
```shell
NEWS_API_KEY=****
THE_GUARDIAN_API_KEY=****
NEW_YORK_TIMES_API_KEY=****
```

## Open Crontab 
```shell
* * * * * cd ~/<path-to-your-project>/article-app && php artisan schedule:run >> /dev/null 2>&1
```

## Run development server

```shell
php artisan serve
```

## Endpoints

```text
http://localhost:8000/api/articles?q=protester
http://localhost:8000/api/articles?source=news_api
http://localhost:8000/api/articles?category=politics
http://localhost:8000/api/articles?dates=2025-01-27,2025-01-28

```
