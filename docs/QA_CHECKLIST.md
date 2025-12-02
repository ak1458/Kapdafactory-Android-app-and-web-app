# QA Checklist

- [ ] **Setup**: Database created, schema imported, admin seeded.
- [ ] **Login**: Can login with seeded credentials.
- [ ] **Upload**:
    - [ ] Upload valid image + token -> Success.
    - [ ] Upload duplicate token -> Error 409.
    - [ ] Upload large file (>5MB) -> Error 400.
    - [ ] Upload invalid file type (e.g. .exe) -> Error 400.
- [ ] **Search**:
    - [ ] Search by valid token -> Returns record + image.
    - [ ] Search by invalid token -> Error 404.
- [ ] **Update**:
    - [ ] Update text fields -> Success.
    - [ ] Replace image -> Success, new image displayed.
- [ ] **Security**:
    - [ ] Direct access to `uploads/file.php` -> Forbidden (403).
    - [ ] SQL Injection attempt in token -> Blocked.
