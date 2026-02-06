<?php
require_once __DIR__ . '/autoload.php';
require_once __DIR__ . '/config/config.php';

use Config\Database;

// Test credentials
$email = 'organizer@example.com';
$password = 'password';

echo "Testing login for: $email\n\n";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo "ERROR: User not found!\n";
        exit;
    }
    
    echo "User found:\n";
    echo "ID: " . $user['id'] . "\n";
    echo "Email: " . $user['email'] . "\n";
    echo "Role: " . $user['role'] . "\n";
    echo "Hash: " . $user['password_hash'] . "\n\n";
    
    // Test password verification
    echo "Testing password verification...\n";
    $isValid = password_verify($password, $user['password_hash']);
    
    if ($isValid) {
        echo "✓ Password is CORRECT!\n";
    } else {
        echo "✗ Password is INCORRECT!\n";
        echo "\nTrying to create correct hash for 'password':\n";
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        echo "New hash: $newHash\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
