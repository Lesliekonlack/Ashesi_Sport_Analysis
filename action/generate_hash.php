<?php
// generate_hash.php

$plaintext_password = 'password123'; // Replace with the password you want to hash

$hashed_password = password_hash($plaintext_password, PASSWORD_DEFAULT);
echo "Plaintext password: " . $plaintext_password . "\n";
echo "Hashed password: " . $hashed_password . "\n";
?>
