# Release & Distribution SOP

## 1. Build & Release Process

### Prerequisites
- **Keystore**: `kapdafactory-release.jks` (Stored in Secure Vault)
- **Passwords**: See Vault for `storePassword` and `keyPassword`.
- **Android Studio**: Installed and configured.

### Steps to Build Signed APK
1.  **Generate Keystore** (One-time):
    ```bash
    keytool -genkeypair -v -keystore release-keystore.jks -alias kapdafactory_key -keyalg RSA -keysize 2048 -validity 10000
    ```
    *Store `release-keystore.jks` in your secure vault.*

2.  **Build in Android Studio**:
    - Open `frontend/android`.
    - **Build > Generate Signed Bundle / APK**.
    - Choose **APK**.
    - Select your `release-keystore.jks`.
    - Select **release** build.
    - Ensure **V1** and **V2** signatures are checked.
    - Click **Finish**.

### Output & Distribution
1.  **Locate**: `frontend/android/app/release/app-release.apk`
2.  **Rename**: `KapdaFactory_Admin_v{VERSION_CODE}.apk`
3.  **Checksum**:
    ```bash
    # Windows
    certutil -hashfile KapdaFactory_Admin_v1.apk SHA256
    # Linux/Mac
    sha256sum KapdaFactory_Admin_v1.apk > KapdaFactory_Admin_v1.apk.sha256
    ```
4.  **Upload**: Put APK + Checksum in Secure Vault.
5.  **Share**: Send link + `docs/INSTALL_GUIDE.txt` to staff.
2.  Update the `CHANGELOG.md` (or Release Notes) in the folder.
3.  Send the **Install Guide** (see `docs/INSTALL_GUIDE.txt`) and the **Download Link** to the Shop Admins via secure channel (WhatsApp/Signal).

## 3. Update Process
1.  **Increment Version**:
    - Open `frontend/android/app/build.gradle`.
    - Increment `versionCode` (e.g., 1 -> 2).
    - Increment `versionName` (e.g., "1.0" -> "1.1").
2.  **Rebuild**: Follow "Steps to Build Signed APK" above.
3.  **Distribute**: Upload new APK. Notify admins to "Install Update" (it will overwrite and keep data).

## 4. Rollback Plan
**If a new version crashes or fails:**
1.  **Do NOT uninstall** (to preserve data), unless the crash is due to data corruption.
2.  Instruct Admins to download the **Previous Version** (e.g., v1) from the Secure Folder.
3.  Install the previous APK over the current one (Android allows downgrade if signed with same key, usually requires `adb install -r -d` or uninstalling if version check fails).
    - *Note*: Standard Android blocks downgrades. **Safe Rollback**: Uninstall new app (warn about data loss if local-only) -> Install old APK.
    - *Better*: Fix bug and release v{Next} immediately.

## 5. Artifact Retention
- Keep the last **3 Versions** of APKs in the Secure Vault.
