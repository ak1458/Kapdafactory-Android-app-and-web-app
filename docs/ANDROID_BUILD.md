# Android APK Build Guide

## Prerequisites
- **Android Studio**: Download and install from [developer.android.com](https://developer.android.com/studio).
- **Java Development Kit (JDK)**: Usually bundled with Android Studio.

## Steps to Build APK

1. **Open Project in Android Studio**
   - Launch Android Studio.
   - Click **Open**.
   - Navigate to `d:\gravity\Kapdafactory\frontend\android` and select it.
   - Wait for Gradle sync to complete (this may take a while first time).

2. **Build Signed APK**
   - Go to **Build** > **Generate Signed Bundle / APK**.
   - Select **APK** and click **Next**.
   - **Key Store Path**: Click **Create new...** if you don't have one.
     - Save it as `release-key.jks` in a safe place.
     - Set a password and alias (e.g., `key0`).
   - Fill in the password and alias.
   - Click **Next**.
   - Select **release** build variant.
   - Click **Create**.

3. **ProGuard / R8 (Optimization)**
   - In `android/app/build.gradle`, ensure `minifyEnabled true` is set for release.
   - This shrinks and obfuscates the code for security.

4. **Locate APK**
   - Once built, a notification will appear. Click **locate**.
   - Or go to `frontend/android/app/release/app-release.apk`.

4. **Install on Device**
   - Transfer the APK to your Android phone via USB or Google Drive.
   - Tap to install (enable "Install from unknown sources" if prompted).

## Troubleshooting
- **Gradle Errors**: Try **File** > **Invalidate Caches / Restart**.
- **Permission Issues**: Ensure you accepted camera/storage permissions on the device.
