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
        // Check if the user is an admin
        $admin_stmt = $conn->prepare("SELECT AdminID, Name, PasswordHash FROM admin WHERE Email = ?");
        $admin_stmt->execute([$email]);

        if ($admin_stmt->rowCount() > 0) {
            $admin_row = $admin_stmt->fetch(PDO::FETCH_ASSOC);
            $admin_id = $admin_row['AdminID'];
            $admin_name = $admin_row['Name'];
            $admin_hashed_password = $admin_row['PasswordHash'];

            // Verify password
            if (password_verify($password, $admin_hashed_password)) {
                $_SESSION['admin_id'] = $admin_id;
                $_SESSION['admin_name'] = $admin_name;
                header("Location: ../view/pages/admin_adding_matches.php");
                exit();
            } else {
                echo "Invalid password.";
            }
        } else {
            // Fetch user from the database (coach)
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
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
$conn = null;
?>
