# Mobile Access Guide

Since this is a local development build, you can access it on your mobile device if it is connected to the same Wi-Fi network as your computer.

## Step 1: Find your Computer's IP Address
1. Open Command Prompt on your computer.
2. Type `ipconfig` and press Enter.
3. Look for **IPv4 Address** (e.g., `192.168.1.5`).

## Step 2: Update API Configuration
For the mobile device to reach the backend, the frontend needs to know the computer's IP, not just `localhost`.

1. Open `frontend/src/api.js`.
2. Change `baseURL` to use your IP:
   ```javascript
   // Replace 192.168.x.x with your actual IP
   baseURL: 'http://192.168.1.5:8000/api', 
   ```
3. Restart the frontend server:
   ```bash
   npm run dev -- --host
   ```
   (The `-- --host` flag exposes the server to the network).

4. Restart the backend server:
   You might need to ensure it listens on all interfaces (Node.js usually does by default).

## Step 3: Access on Mobile
1. Open Chrome or Safari on your phone.
2. Enter the URL: `http://192.168.1.5:5173` (replace with your IP).
3. You should see the login screen.

## Step 4: Add to Home Screen (App-like Experience)
1. **Android (Chrome)**: Tap the menu (⋮) -> **Add to Home Screen**.
2. **iOS (Safari)**: Tap Share button -> **Add to Home Screen**.

The app will appear as an icon on your home screen and launch in full-screen mode like a native app.
