# URL Shortener

A full-stack URL Shortener built with Laravel and React that generates short links, redirects users to original URLs, and tracks click analytics using queues.

## Features

* Generate unique 8-character short URLs
* Redirect short URLs to original links
* Click analytics tracking
* Queue-based background processing
* Redis caching support
* Kafka-style event architecture
* Responsive React frontend
* REST API powered by Laravel

## Tech Stack

### Backend

* PHP
* Laravel
* MySQL
* Redis
* Laravel Queues

### Frontend

* React
* JavaScript
* Axios

## Project Flow

### Create Short URL

User submits a long URL:

Frontend → Laravel API → Generate Short Code → Save to Database → Return Short URL

### Redirect

User opens short URL:

Browser → Laravel Redirect Controller → Lookup URL → Queue Analytics Job → Redirect User

## Database

### URLs Table

* Original URL
* Short Code
* Click Count
* Status
* Expiry Date
* Last Clicked Time

### Clicks Table

* URL ID
* Hashed IP
* Hashed User Agent
* Referrer
* Timestamp

## Installation

Clone the repository:

```bash
git clone https://github.com/Aman12start/url-shortener.git
cd url-shortener
```

Install dependencies:

```bash
composer install
npm install
```

Configure environment:

```bash
cp .env.example .env
php artisan key:generate
```

Run migrations:

```bash
php artisan migrate
```

Start Laravel:

```bash
php artisan serve --port=8001
```

Start Queue Worker:

```bash
php artisan queue:work --queue=clicks,default
```

Build Frontend:

```bash
npm run build
```

## Local Development

Example generated link:

```txt
http://127.0.0.1:8001/J7qudkrM
```

After deployment:

```txt
https://yourdomain.com/J7qudkrM
```

## Future Improvements

* Real Kafka Integration
* QR Code Generation
* User Authentication
* Custom Short URLs
* Advanced Analytics Dashboard

## Author

Aman Singh

Backend Developer | Laravel | PHP | Redis | System Design
