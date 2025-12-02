<?php
require_once 'db.php';
require_once 'auth_middleware.php';
require_once 'rate_limit.php';

header('Content-Type: application/json');
ini_set('memory_limit', '256M');
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../php_errors.log');

$method = $_SERVER['REQUEST_METHOD'];

// Helper to sanitize filename
function sanitizeFilename($filename) {
    return preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
}

// Helper to resize image
function resizeImage($sourcePath, $destPath, $maxWidth, $maxHeight) {
    list($width, $height, $type) = getimagesize($sourcePath);
    
    $ratio = $width / $height;
    if ($width > $maxWidth || $height > $maxHeight) {
        if ($width / $maxHeight > $ratio) {
            $width = $maxHeight * $ratio;
            $height = $maxHeight;
        } else {
            $height = $maxWidth / $ratio;
            $width = $maxWidth;
        }
    }

    $newImage = imagecreatetruecolor($width, $height);
    
    switch ($type) {
        case IMAGETYPE_JPEG:
            $source = imagecreatefromjpeg($sourcePath);
            break;
        case IMAGETYPE_PNG:
            $source = imagecreatefrompng($sourcePath);
            break;
        case IMAGETYPE_WEBP:
            $source = imagecreatefromwebp($sourcePath);
            break;
        default:
            return false;
    }

    imagecopyresampled($newImage, $source, 0, 0, 0, 0, $width, $height, $width, $height);
    
    // Save as WebP for optimization
    imagewebp($newImage, $destPath, 80);
    
    imagedestroy($newImage);
    imagedestroy($source);
    return true;
}

if ($method === 'POST') {
    // Enable error reporting for debugging
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    // Check for required extensions
    if (!extension_loaded('pdo_sqlite')) {
        http_response_code(500);
        echo json_encode(['error' => 'Server Error: pdo_sqlite extension is missing']);
        exit;
    }
    if (!extension_loaded('gd')) {
        http_response_code(500);
        echo json_encode(['error' => 'Server Error: GD extension is missing']);
        exit;
    }

    requireAuth();
    checkRateLimit('upload', 10, 60); 

    if (!isset($_POST['token']) || !isset($_FILES['image'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Token and image are required']);
        exit;
    }

    $token = $_POST['token'];
    
    if (strlen($token) < 3 || strlen($token) > 50 || !preg_match('/^[a-zA-Z0-9_-]+$/', $token)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid token format']);
        exit;
    }

    $measurementText = $_POST['measurement_text'] ?? '';
    $expectedDelivery = $_POST['expected_delivery'] ?? null;
    $status = $_POST['status'] ?? 'pending';

    try {
        $existing = DB::fetch("SELECT id FROM measurements WHERE token = ?", [$token]);
        if ($existing) {
            http_response_code(409);
            echo json_encode(['error' => 'Token already exists']);
            exit;
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database Check Failed: ' . $e->getMessage()]);
        exit;
    }

    $file = $_FILES['image'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    
    if (!in_array($file['type'], $allowedTypes)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid file type. Only JPG, PNG, WEBP allowed.']);
        exit;
    }

    if ($file['size'] > 5 * 1024 * 1024) { 
        http_response_code(400);
        echo json_encode(['error' => 'File too large. Max 5MB.']);
        exit;
    }

    $uploadDir = __DIR__ . '/../uploads/';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create uploads directory']);
            exit;
        }
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $randomSuffix = bin2hex(random_bytes(4));
    $filename = sanitizeFilename($token . '_' . time() . '_' . $randomSuffix . '.' . $ext);
    $targetPath = $uploadDir . $filename;
    
    $optimizedFilename = sanitizeFilename($token . '_' . time() . '_' . $randomSuffix . '_opt.webp');
    $optimizedPath = $uploadDir . $optimizedFilename;

    $thumbFilename = sanitizeFilename($token . '_' . time() . '_' . $randomSuffix . '_thumb.webp');
    $thumbPath = $uploadDir . $thumbFilename;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        $dbPath = 'uploads/' . $filename; 

        try {
            if (function_exists('imagewebp')) {
                // Wrap image processing in try-catch to prevent fatal errors
                try {
                    if (resizeImage($targetPath, $optimizedPath, 1280, 1280)) {
                        $dbPath = 'uploads/' . $optimizedFilename;
                    }
                    resizeImage($targetPath, $thumbPath, 200, 200);
                    
                    $keepOriginal = getenv('KEEP_ORIGINAL') === 'true';
                    if (!$keepOriginal && file_exists($optimizedPath)) {
                        unlink($targetPath); 
                    }
                } catch (Exception $e) {
                    // Log error but continue with original image
                    error_log("Image processing failed: " . $e->getMessage());
                } catch (Error $e) {
                    // Catch fatal errors from GD
                    error_log("Image processing fatal error: " . $e->getMessage());
                }
            }

            DB::query(
                "INSERT INTO measurements (token, image_path, measurement_text, expected_delivery, status, created_by) VALUES (?, ?, ?, ?, ?, ?)",
                [$token, $dbPath, $measurementText, $expectedDelivery, $status, $_SESSION['admin_id']]
            );
            
            http_response_code(201);
            echo json_encode(['ok' => true, 'id' => DB::lastInsertId(), 'image_path' => $dbPath]);
        } catch (Exception $e) {
            // Cleanup
            if (file_exists($targetPath)) unlink($targetPath);
            if (file_exists($optimizedPath)) unlink($optimizedPath);
            if (file_exists($thumbPath)) unlink($thumbPath);

            http_response_code(500);
            echo json_encode(['error' => 'Database Insert Error: ' . $e->getMessage()]);
        }
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to move uploaded file. Check folder permissions.']);
    }

} elseif ($method === 'GET') {
    requireAuth();

    if (!isset($_GET['token'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Token is required']);
        exit;
    }

    $token = $_GET['token'];
    $measurement = DB::fetch("SELECT * FROM measurements WHERE token = ?", [$token]);

    if ($measurement) {
        // Construct absolute image URL
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        // Assuming api is in /api and uploads in /uploads relative to root.
        // If script is in /api/measurements.php, then uploads is ../uploads.
        // But for URL, if we serve from root, it's /uploads/filename.
        // If we serve via `php -S localhost:8000` from `api` dir, then uploads is not accessible directly unless we serve from root.
        // The README says `cd api; php -S localhost:8000`. This means `uploads` (which is `../uploads`) is NOT accessible via web server!
        // This is a common issue with `php -S`.
        // To fix this for local dev, we should serve from the ROOT `Kapdafactory` directory, or symlink, or use a router.
        // User plan: "local PHP server responds to /api/ping".
        // If I run `php -S localhost:8000` in `api`, then `http://localhost:8000/ping.php` works.
        // But `http://localhost:8000/../uploads/file.jpg` will NOT work.
        // I should probably recommend running `php -S localhost:8000` from the `Kapdafactory` root and use a router script or just access via `/api/measurements.php`.
        // But if I run from root, then `/api/measurements.php` becomes `http://localhost:8000/api/measurements.php`.
        // And `uploads` becomes `http://localhost:8000/uploads/...`.
        // This is better.
        // I will assume the server is run from ROOT.
        // So image_url = $protocol . $host . '/' . $measurement['image_path'];
        
        $measurement['image_url'] = $protocol . $host . '/' . $measurement['image_path'];
        echo json_encode($measurement);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Measurement not found']);
    }
} elseif ($method === 'POST' && (isset($_GET['id']) || (isset($_POST['_method']) && $_POST['_method'] === 'PUT'))) {
    // Handle Update via POST (for file uploads)
    requireAuth();
    
    $id = $_GET['id'] ?? null;
    if (!$id) {
        // Try to get ID from route if using router, but here we rely on query param
        http_response_code(400);
        echo json_encode(['error' => 'ID is required for update']);
        exit;
    }

    // Fetch existing
    $existing = DB::fetch("SELECT * FROM measurements WHERE id = ?", [$id]);
    if (!$existing) {
        http_response_code(404);
        echo json_encode(['error' => 'Record not found']);
        exit;
    }

    // Optimistic locking check
    // Client should send 'updated_at' matching the DB.
    // For simplicity in this phase, we might skip strict optimistic locking or just check it if provided.
    if (isset($_POST['original_updated_at']) && $_POST['original_updated_at'] !== $existing['updated_at']) {
        http_response_code(409);
        echo json_encode(['error' => 'Record has been modified by another user. Please reload.']);
        exit;
    }

    $measurementText = $_POST['measurement_text'] ?? $existing['measurement_text'];
    $expectedDelivery = $_POST['expected_delivery'] ?? $existing['expected_delivery'];
    $status = $_POST['status'] ?? $existing['status'];
    $imagePath = $existing['image_path'];

    // Handle Image Replacement
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['image'];
        // Validation (same as create)
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid file type']);
            exit;
        }

        $uploadDir = __DIR__ . '/../uploads/';
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = sanitizeFilename($existing['token'] . '_' . time() . '.' . $ext);
        $targetPath = $uploadDir . $filename;
        $optimizedFilename = sanitizeFilename($existing['token'] . '_' . time() . '_opt.webp');
        $optimizedPath = $uploadDir . $optimizedFilename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            if (function_exists('imagewebp')) {
                resizeImage($targetPath, $optimizedPath, 1280, 1280);
                $imagePath = 'uploads/' . $optimizedFilename;
            } else {
                $imagePath = 'uploads/' . $filename;
            }
            
            // Optional: Delete old file
            // if (file_exists(__DIR__ . '/../' . $existing['image_path'])) { unlink(__DIR__ . '/../' . $existing['image_path']); }
        }
    }

    DB::query(
        "UPDATE measurements SET measurement_text = ?, expected_delivery = ?, status = ?, image_path = ? WHERE id = ?",
        [$measurementText, $expectedDelivery, $status, $imagePath, $id]
    );

    echo json_encode(['ok' => true, 'message' => 'Record updated']);

} elseif ($method === 'PUT') {
    // Handle JSON Update (no file)
    requireAuth();
    $id = $_GET['id'] ?? null;
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID is required']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    
    $existing = DB::fetch("SELECT * FROM measurements WHERE id = ?", [$id]);
    if (!$existing) {
        http_response_code(404);
        echo json_encode(['error' => 'Record not found']);
        exit;
    }

    $measurementText = $input['measurement_text'] ?? $existing['measurement_text'];
    $expectedDelivery = $input['expected_delivery'] ?? $existing['expected_delivery'];
    $status = $input['status'] ?? $existing['status'];

    DB::query(
        "UPDATE measurements SET measurement_text = ?, expected_delivery = ?, status = ? WHERE id = ?",
        [$measurementText, $expectedDelivery, $status, $id]
    );

    echo json_encode(['ok' => true, 'message' => 'Record updated']);

} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
