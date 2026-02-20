# 🛒 POS Analytics API

> REST API for aggregating e-commerce revenue across points of sale. Pass a date range — get back a summary: order count, total revenue, and average order value. Only active locations included.

[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![Symfony](https://img.shields.io/badge/Symfony-7.2-000000?style=flat-square&logo=symfony&logoColor=white)](https://symfony.com)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-316192?style=flat-square&logo=postgresql&logoColor=white)](https://postgresql.org)
[![Docker](https://img.shields.io/badge/Docker-2496ED?style=flat-square&logo=docker&logoColor=white)](https://docker.com)
[![PHPUnit](https://img.shields.io/badge/PHPUnit-tested-green?style=flat-square&logo=php&logoColor=white)](https://phpunit.de)
[![PHPStan](https://img.shields.io/badge/PHPStan-max-blue?style=flat-square)](https://phpstan.org)

---

## 📋 Overview

**POS Analytics API** is a lightweight Symfony-based REST API designed for e-commerce platforms that operate across multiple physical or virtual points of sale. It provides instant revenue aggregation for any given date range — order counts, totals, and averages — filtered to active locations only.

**Use cases:**
- Dashboard widgets showing daily/monthly revenue per store
- Automated financial reporting across POS locations
- Integration with ERP or BI systems

---

## ⚙️ Tech Stack

| Layer | Technology |
|---|---|
| Language | PHP 8.3 |
| Framework | Symfony 7.2 |
| Database | PostgreSQL 16 |
| ORM | Doctrine |
| Containerization | Docker / Docker Compose |
| Testing | PHPUnit |
| Static Analysis | PHPStan |
| Code Style | PHP CS Fixer |
| CI/CD | GitHub Actions |

---

## 🗂️ Project Structure

```
ecom-pos-summary/
├── .github/workflows/    # GitHub Actions CI pipeline
├── bin/                  # Symfony console
├── config/               # App configuration
├── docker/               # Docker setup files
├── migrations/           # Database migrations
├── public/               # Entry point (index.php)
├── src/                  # Application source code
│   ├── Controller/       # API controllers
│   ├── Service/          # Business logic
│   └── Repository/       # Data access layer
├── tests/                # PHPUnit test suites
├── docker-compose.yml
├── composer.json
└── phpunit.dist.xml
```

---

## 🚀 Quick Start

### Prerequisites
- Docker & Docker Compose

### 1. Clone & configure

```bash
git clone https://github.com/yasunchukandriy/ecom-pos-summary.git
cd ecom-pos-summary
cp .env.example .env
```

### 2. Start containers

```bash
docker compose up -d
```

### 3. Run migrations

```bash
docker compose exec app php bin/console doctrine:migrations:migrate
```

### 4. Load fixtures *(optional)*

```bash
docker compose exec app php bin/console doctrine:fixtures:load
```

The API will be available at **http://localhost:8080**

---

## 📡 API Endpoints

### `GET /api/pos/summary`

Revenue summary per point of sale for a given period.

| Parameter | Format | Default |
|---|---|---|
| `from` | `YYYY-MM-DD` | First day of current month |
| `to` | `YYYY-MM-DD` | Last day of current month |

**Example request:**
```bash
curl "http://localhost:8080/api/pos/summary?from=2025-01-01&to=2025-01-31"
```

**Example response:**
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

> Invalid dates return `400` with a detailed error description.

---

### `GET /api/health`

Health check endpoint. Runs `SELECT 1` against the database.

| Status | Meaning |
|---|---|
| `200 OK` | App and database are healthy |
| `503 Service Unavailable` | Database connection failed |

---

## 🏗️ Architecture

The application follows a clean **Controller → Service → Repository** pattern:

- **Controller** — handles HTTP request/response, input validation
- **Service** — contains business logic, depends on a repository *interface* (not implementation)
- **Repository** — Doctrine-based data access, easily swappable

All errors on `/api/*` routes return **JSON** via a global event subscriber — no HTML error pages.

---

## 🧪 Testing

```bash
# Run all tests
docker compose exec app php bin/phpunit

# With coverage report
docker compose exec app php bin/phpunit --coverage-text
```

---

## 🔍 Code Quality

```bash
# Static analysis
docker compose exec app vendor/bin/phpstan analyse

# Code style check
docker compose exec app vendor/bin/php-cs-fixer fix --dry-run
```

---

## 👤 Author

**Andrii Yasynchuk** — Senior Full Stack Developer
[![LinkedIn](https://img.shields.io/badge/LinkedIn-0077B5?style=flat-square&logo=linkedin&logoColor=white)](https://www.linkedin.com/in/andriy-yasunchuk-750028197/)
[![GitHub](https://img.shields.io/badge/GitHub-100000?style=flat-square&logo=github&logoColor=white)](https://github.com/yasunchukandriy)

---

## 📄 License

MIT © Andrii Yasynchuk
