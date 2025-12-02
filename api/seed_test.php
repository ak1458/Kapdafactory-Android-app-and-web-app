<?php
$dbPath = __DIR__ . '/kapdafactory.db';
try {
    $db = new PDO("sqlite:$dbPath");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Insert a test record
    $stmt = $db->prepare("INSERT OR IGNORE INTO measurements (token, image_path, measurement_text, expected_delivery, status, created_by) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute(['TEST001', 'uploads/test.jpg', 'Test measurements', '2025-12-31', 'pending', 1]);
    
    echo "Seeded TEST001 successfully";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
