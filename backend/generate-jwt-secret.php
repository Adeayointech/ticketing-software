<?php
/**
 * JWT Secret Generator
 * Run this file once to generate a secure JWT secret key
 * 
 * Usage: php generate-jwt-secret.php
 */

// Generate a secure random string
function generateSecureSecret($length = 64) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_+-=[]{}|;:,.<>?';
    $secret = '';
    $max = strlen($characters) - 1;
    
    for ($i = 0; $i < $length; $i++) {
        $secret .= $characters[random_int(0, $max)];
    }
    
    return $secret;
}

echo "=================================\n";
echo "JWT Secret Key Generator\n";
echo "=================================\n\n";

$secret = generateSecureSecret(64);

echo "Your secure JWT secret key:\n";
echo $secret . "\n\n";

echo "Copy this key and paste it in your .env file:\n";
echo "JWT_SECRET=" . $secret . "\n\n";

echo "=================================\n";
echo "IMPORTANT: Keep this secret secure!\n";
echo "Never commit this to version control.\n";
echo "=================================\n";
?>
