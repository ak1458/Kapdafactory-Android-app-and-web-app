# Security Checklist

- [ ] **Keystore Security**: `kapdafactory-release.jks` is stored in a secure vault (LastPass/1Password/Offline USB). NOT in git.
- [ ] **Secrets**: No API keys or passwords hardcoded in `App.jsx` or `api.js`.
- [ ] **Server Auth**: Backend enforces `Authorization: Bearer` on all sensitive endpoints.
- [ ] **Admin Access**: List of active admin accounts reviewed. Compromised accounts revoked/deleted.
- [ ] **Debug Disabled**: Release APK built with `minifyEnabled true` (ProGuard/R8) and `debuggable false`.
- [ ] **Network**: Server uses HTTPS (if deployed remotely) or secure LAN.
- [ ] **Data**: No sensitive customer data stored permanently on device (images uploaded, local queue temporary).
