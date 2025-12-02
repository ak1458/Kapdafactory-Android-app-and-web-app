# Setup Instructions

## Prerequisites
- Node.js & npm

## Database Setup
- The application uses SQLite (`api/kapdafactory.db`).
- The database file is automatically created on first run.
- Admin user is seeded automatically: `admin` / `password123`.

## Running the App

### Backend (Node.js)
```bash
cd api
npm install
node server.js
```
Server runs on `http://localhost:8000`.

### Frontend (React)
```bash
cd frontend
npm install
npm run dev
```
Access at `http://localhost:5173`.

## Notes
- This version uses a Node.js backend with SQLite to ensure compatibility without requiring PHP/MySQL installation.
- Uploads are stored in `uploads/` directory.
