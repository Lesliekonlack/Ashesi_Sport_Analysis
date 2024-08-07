<?php
session_start();
include '../settings/connection.php'; // Ensure this file correctly sets up the $conn variable

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if the user is logged in and is a coach
if (!isset($_SESSION['coach_id'])) {
    die('Access denied.');
}

// Check if the connection variable is set
if (!isset($conn)) {
    die('Database connection not established.');
}

// Validate and sanitize input
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['team_id'])) {
    $teamId = filter_var($_POST['team_id'], FILTER_SANITIZE_NUMBER_INT);
    $coachId = $_SESSION['coach_id'];

    // Verify the team belongs to the coach
    try {
        $sql = "SELECT TeamID, Logo FROM teams WHERE TeamID = :team_id AND CoachID = :coach_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':team_id', $teamId, PDO::PARAM_INT);
        $stmt->bindParam(':coach_id', $coachId, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() == 1) {
            $team = $stmt->fetch(PDO::FETCH_ASSOC);
            $logoPath = '../../uploads/' . $team['Logo'];

            // Remove the logo file if it exists
            if (file_exists($logoPath)) {
                if (!unlink($logoPath)) {
                    die('Error deleting the logo file.');
                }
            }

            // Update the team's logo path in the database
            $sql = "UPDATE teams SET Logo = NULL WHERE TeamID = :team_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':team_id', $teamId, PDO::PARAM_INT);

            if ($stmt->execute()) {
                header('Location: ../view/pages/footballsport.php?success=logo_deleted');
                exit();
            } else {
                die('Database update failed.');
            }
        } else {
            die('Invalid team or access denied.');
        }
    } catch (PDOException $e) {
        echo 'Database error: ' . $e->getMessage();
    }
} else {
    die('Invalid request.');
}
?>
