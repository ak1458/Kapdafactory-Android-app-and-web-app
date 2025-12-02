# Mobile API Contract

Base URL: `http://<YOUR_IP>:8000/api`

## Authentication
All protected endpoints require header: `Authorization: Bearer <token>`

### POST /login.php
- **Body**: `{ "username": "admin", "password": "..." }`
- **Response (200)**: `{ "ok": true, "token": "..." }`
- **Response (401)**: `{ "error": "Invalid credentials" }`

### POST /logout.php
- **Headers**: Authorization
- **Response (200)**: `{ "ok": true }`

## Measurements

### POST /measurements.php (Create)
- **Type**: `multipart/form-data`
- **Fields**:
  - `token` (text, required)
  - `image` (file, required, max 5MB)
  - `measurement_text` (text)
  - `expected_delivery` (date)
- **Response (201)**: `{ "ok": true, "id": 123, "image_path": "..." }`
- **Response (409)**: `{ "error": "Token exists" }`

### GET /measurements.php?token=...
- **Response (200)**: `{ "id": 123, "token": "...", "image_url": "..." }`
- **Response (404)**: `{ "error": "Not found" }`

### POST /measurements.php?id=... (Update Image)
- **Type**: `multipart/form-data`
- **Fields**: `image` (file), `measurement_text`, etc.
- **Response (200)**: `{ "ok": true }`
