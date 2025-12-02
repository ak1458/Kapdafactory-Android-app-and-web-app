<?php
class DB {
    private static $pdo = null;

    public static function connect() {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        // SQLite Database File (relative to this file)
        $dbPath = __DIR__ . '/../kapda.db';
        $initialize = !file_exists($dbPath);
        
        try {
            self::$pdo = new PDO("sqlite:$dbPath");
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            if ($initialize) {
                self::initDB();
            }
            
            return self::$pdo;
        } catch (\PDOException $e) {
            // Return JSON error even for fatal DB connection issues
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Database Connection Error: ' . $e->getMessage()]);
            exit;
        }
    }

    private static function initDB() {
        $sql = "
        CREATE TABLE IF NOT EXISTS admins (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS measurements (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            token TEXT NOT NULL UNIQUE,
            image_path TEXT NOT NULL,
            measurement_text TEXT,
            expected_delivery DATE,
            status TEXT DEFAULT 'pending',
            created_by INTEGER,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES admins(id)
        );
        
        -- Seed default admin if empty
        INSERT OR IGNORE INTO admins (id, username, password_hash) VALUES (1, 'admin', 'nopass');
        ";
        
        self::$pdo->exec($sql);
    }

    public static function query($sql, $params = []) {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function fetch($sql, $params = []) {
        return self::query($sql, $params)->fetch();
    }

    public static function fetchAll($sql, $params = []) {
        return self::query($sql, $params)->fetchAll();
    }
    
    public static function lastInsertId() {
        return self::connect()->lastInsertId();
    }
}
