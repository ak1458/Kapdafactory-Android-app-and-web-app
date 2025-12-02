# Kapdafactory API Documentation

## Authentication

### Login
- **Endpoint**: `POST /api/login.php`
- **Body**: `{ "username": "admin", "password": "..." }`
- **Response**: `{ "ok": true, "username": "admin" }`

### Logout
- **Endpoint**: `POST /api/logout.php`
- **Response**: `{ "ok": true }`

## Measurements

### Create Measurement
- **Endpoint**: `POST /api/measurements.php`
- **Headers**: `Content-Type: multipart/form-data`
- **Body**:
  - `token`: String (required, unique, 3-50 chars)
  - `image`: File (required, jpg/png/webp, max 5MB)
  - `measurement_text`: String (optional)
  - `expected_delivery`: Date (optional, YYYY-MM-DD)
  - `status`: String (optional, default: pending)
- **Response**: `{ "ok": true, "id": 123, "image_path": "uploads/..." }`

### Get Measurement
- **Endpoint**: `GET /api/measurements.php?token=TOKEN`
- **Response**: `{ "id": 123, "token": "...", "image_url": "...", ... }`

### Update Measurement
- **Endpoint**: `POST /api/measurements.php?id=ID` (for file updates) OR `PUT /api/measurements.php?id=ID` (JSON only)
- **Body (POST)**:
  - `measurement_text`: String
  - `expected_delivery`: Date
  - `status`: String
  - `image`: File (optional, replaces existing)
  - `original_updated_at`: Timestamp (optional, for optimistic locking)
- **Response**: `{ "ok": true, "message": "Record updated" }`
