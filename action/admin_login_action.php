<?php
session_start();
include '../settings/connection.php'; // Adjust the path as necessary

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        // Fetch admin from the database
        $stmt = $conn->prepare("SELECT AdminID, Name, PasswordHash FROM admin WHERE Email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $admin_id = $row['AdminID'];
            $name = $row['Name'];
            $hashed_password = $row['PasswordHash'];

            // Verify password
            if (password_verify($password, $hashed_password)) {
                $_SESSION['admin_id'] = $admin_id;
                $_SESSION['admin_name'] = $name;

                header("Location: ../view/pages/admin_adding_matches.php"); // Adjust the path as necessary
                exit();
            } else {
                echo "Invalid password.";
            }
        } else {
            echo "No admin found with that email.";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
$conn = null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login - Ashesi Sports Insight</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="login-container">
        <h2>Admin Login</h2>
        <form action="admin_login.php" method="post"> <!-- Ensure this path is correct -->
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
