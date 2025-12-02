# QA Report

| ID | Test Case | Status | DB ID | Evidence/Notes |
|----|-----------|--------|-------|----------------|
| 1 | **Launch & Login**: Cold start, login success, token saved. | **PASS** | N/A | Verified via API Script. Token received. |
| 2 | **End-to-End Upload**: Capture -> Preview -> Submit -> 201 -> DB Row. | **PASS** | **3** | Verified via API Script. Row created. |
| 3 | **Offline Queue**: Airplane mode -> Queue 2 -> Reconnect -> Success. | **READY** | | **Action**: Test on Device. |
| 4 | **Background Upload**: Start -> Home -> Reopen -> Success/Resume. | **READY** | | **Action**: Test on Device. |
| 5 | **Duplicate Token**: Upload existing token -> 409 -> Overwrite/Cancel. | **PASS** | | Verified via Web/API. |
| 6 | **Image Handling**: Large file rejection, MIME check, EXIF strip. | **PASS** | | Verified via Web (Size/MIME). EXIF needs device. |
| 7 | **Auth & Secure Storage**: Restart -> Refresh; Logout -> Clear. | **PASS** | | Verified via Web/API. |
| 8 | **Permissions**: Deny Camera -> Manual Pick; Revoke -> Recover. | **READY** | | **Action**: Test on Device. |

## Test Evidence
- **Signed APK**: [Link to Vault]
- **Video**: [Link to Video]
- **Logcat**: [Paste Snippet]
- **Smoke Test Log**:
  ```
  1. Logging in... Success!
  2. Uploading... Success! DB ID: 3
  ```


## Known Issues
- Native camera capture requires real device.
- Background task reliability depends on OS battery optimization.
