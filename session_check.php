<?php
session_start();

$response = [
    'isAdmin' => false,
    'profileUrl' => 'login.php'
];

if (isset($_SESSION['username'])) {
    $response['profileUrl'] = 'profile.php';
}

if (isset($_SESSION['admin']) && $_SESSION['admin']) {
    $response['isAdmin'] = true;
}

echo json_encode($response);
?>