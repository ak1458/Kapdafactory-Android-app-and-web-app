# Kapdafactory

Mobile-first admin app for managing measurement records.

## Structure

- `/frontend`: React admin UI (Vite)
- `/api`: PHP API
- `/sql`: Database schema

## Prerequisites

- Node.js & npm
- PHP 8.0+
- MySQL

## Running Locally

### Frontend

```bash
cd frontend
npm install
npm run dev
```

### API

```bash
cd api
php -S localhost:8000
```

## Database Setup

1. Create a database named `kapdafactory`.
2. Import `sql/schema.sql`.

## Download App

[Download APK](./frontend/android/app/release/app-release.apk)

