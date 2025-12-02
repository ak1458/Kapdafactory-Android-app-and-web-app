<?php
require_once 'db.php';

$username = 'admin';
$password = 'password123'; // Change this!

$hash = password_hash($password, PASSWORD_BCRYPT);

try {
    DB::query("INSERT INTO admins (username, password_hash) VALUES (?, ?)", [$username, $hash]);
    echo "Admin user created.\n";
    echo "Username: $username\n";
    echo "Password: $password\n";
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        echo "Admin user already exists.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
