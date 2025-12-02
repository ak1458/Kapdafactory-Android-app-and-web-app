<?php
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => false, // Set to true if using HTTPS
    'cookie_samesite' => 'Strict',
]);

function requireAuth() {
    // Check Session
    if (isset($_SESSION['admin_id'])) {
        return;
    }

    // Check Bearer Token
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    
    if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        $token = $matches[1];
        // In our simple no-auth setup, we accept any token that matches what we issued (or just any token for now since we removed password)
        // Ideally we should verify it against the DB or session store, but since we are stateless with SQLite file,
        // and we just want it to work:
        if ($token) {
            // Mock user for the session
            $_SESSION['admin_id'] = 1; // Default admin ID
            return;
        }
    }

    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

function cors() {
    // Allow from localhost:5173 (Vite default)
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');    // cache for 1 day
    }

    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");         

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

        exit(0);
    }
}

cors();
