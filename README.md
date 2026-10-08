# Webhook Inspector

Inspect, debug and replay HTTP webhooks.

## Features

- Endpoint creation, rename, enable/disable, deletion and token regeneration.
- GET, POST, PUT, PATCH and DELETE webhook capture.
- Header, query, JSON, raw body, IP, user agent, size and timestamp storage.
- Request inspection and replay with modified headers and JSON payload.
- Replay history with target URL, response status, headers, body and duration.
- Authentication and ownership policies.
- Request-size limits and per-endpoint rate limiting.
- SSRF protection for replay destinations.

## Requirements

- PHP 8.3+
- Composer
- Laravel 13
- SQLite for the default setup

## Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
mkdir -p database
# Create database/database.sqlite on systems that need an explicit SQLite file.
php artisan migrate
php artisan serve
```

Open the application, create an account, create an endpoint, and send an HTTP request to its generated webhook URL.

## Usage

The dashboard provides endpoint management, captured request inspection, replay, and replay results.

### Creating an Endpoint

Create an endpoint from the dashboard. The generated URL is unique to its token. Regenerating the token invalidates the previous URL.

### Inspecting Requests

Captured requests retain the HTTP method, headers, query parameters, JSON body when valid JSON is supplied, raw body, source IP, user agent, request size, and receive timestamp.

### Replay

Select a request, enter an HTTP or HTTPS target URL, modify headers or the JSON payload, and execute the replay. The response is stored with status, headers, body, and duration.

## Security

Replay destinations accept only HTTP and HTTPS. Localhost, local hostnames, private IP addresses, reserved IP addresses, and hostnames resolving to private/reserved addresses are rejected. Incoming bodies are limited by `WEBHOOK_MAX_BODY_BYTES`, and webhook ingestion is rate limited by `WEBHOOK_RATE_LIMIT`.

Do not use production webhook URLs as a substitute for a dedicated secret-management system. Payloads may contain credentials or personal data and are stored by design for inspection.

## API

Authenticated routes:

- GET /api/endpoints
- POST /api/endpoints
- GET /api/endpoints/{id}
- PATCH /api/endpoints/{id}
- DELETE /api/endpoints/{id}
- POST /api/endpoints/{id}/regenerate-token
- GET /api/endpoints/{id}/requests
- GET /api/requests/{id}
- POST /api/requests/{id}/replay

Webhook receiver:

```bash
curl -X POST "https://your-app.example/webhook/YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "X-Example: demo" \
  -d '{"event":"order.created","id":123}'
```

## Testing

```bash
php artisan test
php artisan pint --test
```

## License

MIT

## Global geographic data

The project includes a full geographic-data import pipeline based on GeoNames rather than a hand-maintained city list.

- Countries: imported from the GeoNames countryInfo.txt dump.
- Populated places: imported from the worldwide allCountries.zip dump by selecting every feature-class P record. This includes cities, towns, villages, localities, administrative seats and related populated-place records.
- Regional classification: enriched from the United Nations M49 country/area classification.
- Search: name, English name, ASCII/transliterated name and alternate names.
- Nearby search: latitude/longitude radius query.
- Pagination and search-result caching are enabled for API reads.
- The raw worldwide dump is not committed because it is large and changes frequently; it is downloaded into storage/app/geo by the importer.

### Geographic commands

php artisan geo:import
php artisan geo:import --countries-only
php artisan geo:import --cities-only
php artisan geo:update

The import process downloads the source, validates rows and coordinates, normalizes fields, maps countries, upserts by GeoNames ID, creates database indexes through migrations, and prints import statistics.

### Geographic API

- GET /api/countries
- GET /api/countries/{id}
- GET /api/countries/{id}/cities
- GET /api/cities
- GET /api/cities/search?q=riyadh
- GET /api/cities/nearby?lat=24.7136&lng=46.6753&radius=25

### Source and licensing

GeoNames publishes its data under Creative Commons Attribution 4.0 and permits commercial use with attribution. The worldwide dump is updated regularly. UN M49 is used only for country/region classification. Natural Earth was evaluated as a supplementary source but was not selected as the primary city dataset because GeoNames provides substantially broader populated-place coverage.

At the time this geographic integration was implemented, GeoNames reported 5,231,938 populated-place records across feature class P and more than 13 million total geographic names/features. The application's own geo:import report is authoritative for the exact rows imported into its current database snapshot; source counts can change as GeoNames is updated.

### Geographic schema

countries stores ISO identifiers, numeric code, continent, M49 region/subregion, capital, coordinates, timezone, phone code, currency and source metadata.

cities stores GeoNames ID, country, names, ASCII name, coordinates, population, feature code, administrative codes, timezone, elevation, alternate names and source metadata.
