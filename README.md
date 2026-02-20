# POS Analytics API

API for aggregating revenue across points of sale. Pass a date range — get back a summary: order count, total revenue, average order value. Only active locations included.

## Stack

PHP 8.3, Symfony 7.2, PostgreSQL 16, Docker.

## Endpoints

### `GET /api/pos/summary`

Revenue summary per point of sale for a given period.

| Param  | Format       | Default              |
|--------|--------------|----------------------|
| `from` | `YYYY-MM-DD` | first day of month   |
| `to`   | `YYYY-MM-DD` | last day of month    |

```bash
curl "http://localhost:8080/api/pos/summary?from=2025-01-01&to=2025-01-31"
```

```json
{
    "meta": {
        "period": { "from": "2025-01-01", "to": "2025-01-31" },
        "count": 3,
        "generatedAt": "2025-02-19T12:00:00+00:00"
    },
    "data": [
        {
            "id": 1,
            "name": "Berlin Flagship Store",
            "orderCount": 342,
            "totalRevenue": 28450.75,
            "averageOrderValue": 83.19
        }
    ]
}
```

Invalid dates return `400` with a description of what went wrong.

### `GET /api/health`

Health check. Runs `SELECT 1` against the database — returns `200` if everything is fine, `503` otherwise.

## Architecture

Controller → Service → Repository. The service depends on an interface, not the Doctrine implementation directly — easy to swap if needed.

All errors on `/api/*` routes return JSON via an event subscriber.
