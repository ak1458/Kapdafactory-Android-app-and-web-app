<?php

function checkRateLimit($key, $limit, $window) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $now = time();
    if (!isset($_SESSION['rate_limit'][$key])) {
        $_SESSION['rate_limit'][$key] = ['count' => 0, 'start_time' => $now];
    }

    $data = &$_SESSION['rate_limit'][$key];

    if ($now - $data['start_time'] > $window) {
        $data['count'] = 0;
        $data['start_time'] = $now;
    }

    $data['count']++;

    if ($data['count'] > $limit) {
        http_response_code(429);
        echo json_encode(['error' => 'Too many requests. Please try again later.']);
        exit;
    }
}
