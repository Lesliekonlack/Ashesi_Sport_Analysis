<?php
session_start();
include '../settings/connection.php'; // Include your database connection file

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        // Fetch user from the database including PasswordHash
        $stmt = $conn->prepare("SELECT AdminID, Name, PasswordHash FROM admin WHERE Email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $Admin_id = $row['AdminID'];
            $name = $row['Name'];
            $hashed_password = $row['PasswordHash'];

            // Verify password
            if (password_verify($password, $hashed_password)) {
                $_SESSION['AdminID'] = $Admin_id;
                $_SESSION['Name'] = $name;

                header("Location: ../view/pages/homepage.php");
                exit();
            } else {
                echo "Invalid password.";
            }
        } else {
            echo "No user found with that email.";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
$conn = null;
?>

