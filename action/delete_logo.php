<?php
session_start();
include '../../settings/connection.php'; // Include your database connection file

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if user is logged in and is a coach
if (!isset($_SESSION['coach_id']) || !isset($_POST['team_id'])) {
    echo "Unauthorized access.";
    exit();
}

$team_id = $_POST['team_id'];
$coach_id = $_SESSION['coach_id'];

// Verify that the coach owns the team
$sql = "SELECT TeamID, Logo FROM teams WHERE TeamID = ? AND CoachID = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$team_id, $coach_id]);

if ($stmt->rowCount() === 0) {
    echo "Unauthorized action.";
    exit();
}

$team = $stmt->fetch(PDO::FETCH_ASSOC);
$logo_path = '../../uploads/' . $team['Logo'];

// Delete the logo file if it exists
if ($team['Logo'] && file_exists($logo_path)) {
    unlink($logo_path);
}

// Update the team logo in the database
$sql = "UPDATE teams SET Logo = NULL WHERE TeamID = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$team_id]);

header("Location: ../view/pages/footballsport.php");
exit();
?>
