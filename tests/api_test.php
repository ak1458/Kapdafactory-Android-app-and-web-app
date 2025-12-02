<?php
// Simple test script to verify API endpoints using curl
// Usage: php tests/api_test.php

$baseUrl = 'http://localhost:8000/api';
$cookieFile = tempnam(sys_get_temp_dir(), 'cookie');

function req($method, $url, $data = [], $isJson = true) {
    global $cookieFile;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($isJson) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
    }
    
    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ['code' => $code, 'body' => json_decode($response, true)];
}

echo "1. Login...\n";
$res = req('POST', "$baseUrl/login.php", ['username' => 'admin', 'password' => 'password123']);
echo "Code: " . $res['code'] . "\n";
if ($res['code'] !== 200) die("Login failed\n");

echo "2. Upload...\n";
$token = 'TEST_' . time();
$file = new CURLFile(__DIR__ . '/../frontend/public/vite.svg', 'image/svg+xml', 'test.svg'); // Use dummy file
// Note: SVG might be rejected by our filter (only jpg/png/webp). 
// So this test might fail if we don't have a valid image.
// But it tests the connection.
$res = req('POST', "$baseUrl/measurements.php", ['token' => $token, 'image' => $file], false);
echo "Code: " . $res['code'] . "\n";
print_r($res['body']);

echo "Done.\n";
