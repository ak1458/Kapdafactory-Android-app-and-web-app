# Kapdafactory

Mobile-first admin app for managing measurement records.

## Project Structure

This repository contains both the **Android App** and the **Web App** components.

### 1. Android App
-   **APK**: Located in `Android-App-Build/Kapdafactory.apk`.
-   **Source Code**: The Android app is built from the shared source code in `Shared-Source-Code`.

### 2. Web App
-   **Backend (API)**: Located in `Web-App-Backend/api`.
-   **Frontend**: The Web app frontend is built from the shared source code in `Shared-Source-Code`.

### 3. Shared Source Code
-   `Shared-Source-Code`: Contains the React application used for both the Android app (via Capacitor) and the Web app.

## Prerequisites

- Node.js & npm
- PHP 8.0+
- MySQL

## Running Locally

### Shared Frontend

```bash
cd Shared-Source-Code
npm install
npm run dev
```

### Web Backend (API)

```bash
cd Web-App-Backend/api
php -S localhost:8000
```

## Database Setup

1. Create a database named `kapdafactory`.
2. Import `sql/schema.sql`.

## Download App

[Download APK](./Android-App-Build/Kapdafactory.apk)



