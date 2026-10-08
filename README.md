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
