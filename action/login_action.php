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
        // Fetch user from the database
        $stmt = $conn->prepare("SELECT CoachID, Name, PasswordHash FROM coaches WHERE Email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $coach_id = $row['CoachID'];
            $name = $row['Name'];
            $hashed_password = $row['PasswordHash'];

            // Verify password
            if (password_verify($password, $hashed_password)) {
                $_SESSION['coach_id'] = $coach_id;
                $_SESSION['coach_name'] = $name;

                // Fetch the team_id associated with this coach
                $team_stmt = $conn->prepare("SELECT TeamID FROM teams WHERE CoachID = ?");
                $team_stmt->execute([$coach_id]);

                if ($team_stmt->rowCount() > 0) {
                    $team_row = $team_stmt->fetch(PDO::FETCH_ASSOC);
                    $_SESSION['team_id'] = $team_row['TeamID'];
                }

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
