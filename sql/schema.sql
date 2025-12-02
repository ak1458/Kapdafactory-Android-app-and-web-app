-- Kapdafactory Database Schema

CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS measurements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(50) NOT NULL UNIQUE,
    image_path VARCHAR(255) NOT NULL,
    measurement_text TEXT,
    expected_delivery DATE,
    status ENUM('pending', 'in_progress', 'completed', 'delivered') DEFAULT 'pending',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_token (token),
    FOREIGN KEY (created_by) REFERENCES admins(id)
);
