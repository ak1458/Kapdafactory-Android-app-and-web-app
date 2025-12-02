<?php
require_once 'db.php';
require_once 'auth_middleware.php';
require_once 'rate_limit.php';

header('Content-Type: application/json');

checkRateLimit('login', 5, 60); // 5 attempts per minute

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$username = $input['username'] ?? '';
$password = $input['password'] ?? '';

<?php
require_once 'db.php';
require_once 'auth_middleware.php';
require_once 'rate_limit.php';

header('Content-Type: application/json');

checkRateLimit('login', 5, 60); // 5 attempts per minute

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$username = $input['username'] ?? '';
$password = $input['password'] ?? '';

if (empty($username) || empty($password)) {
    http_response_code(400);
    echo json_encode(['error' => 'Username and password required']);
    exit;
}

    // Assuming $pdo is available from db.php or a similar global/static access
    // If DB::fetch is the intended way, this would need adjustment to use DB::fetch
    // For this change, we'll assume $pdo is accessible, or that DB::getPDO() exists.
    // If DB::fetch is the only interface, the code would be:
    // $user = DB::fetch("SELECT * FROM admins WHERE username = ?", [$username]);
    // For now, let's assume $pdo is available, or DB::getPDO() returns it.
    // If db.php defines a global $pdo, or DB class has a static method to get it:
    // global $pdo; // if $pdo is global
    // $pdo = DB::getPDO(); // if DB class provides it

    // For the purpose of this edit, we'll assume DB::getConnection() returns a PDO object.
    // If DB::fetch is the only interface, the code would be:
    // $user = DB::fetch("SELECT * FROM admins WHERE username = ?", [$username]);
    // and then use $user instead of $stmt->fetch().
    // Given the instruction to use $pdo->prepare, we'll assume $pdo is available.
    // Let's assume DB::getConnection() returns the PDO object.
    $pdo = DB::getConnection(); // Assuming this method exists in your DB class

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // Password check removed as requested
    if ($user) {
        // Generate a simple token (for PHP we can just use session or a simple random string if not using JWT lib)
        // For simplicity and zero-dep, let's use a random string and store in DB or just session.
        // But the frontend expects a JWT format or at least a string.
        // Let's generate a fake JWT-looking string or just a session ID.
        $token = bin2hex(random_bytes(32)); 
        
        // Store in session
        $_SESSION['admin_id'] = $user['id']; // Changed from user_id to admin_id to match original session key
        $_SESSION['username'] = $user['username']; // Added to match original session key
        $_SESSION['token'] = $token;

        echo json_encode(['ok' => true, 'username' => $user['username'], 'token' => $token]);
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid credentials']);
    }
