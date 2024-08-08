<?php 
session_start();
include '../settings/connection.php';

$is_admin = isset($_SESSION['AdminID']);
// Check if the logged-in coach is associated with the team in the URL, and if so, make them a viewer
$can_edit = $is_admin;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add' && $can_edit) {
    // Retrieve form data
    $team1_id = isset($_POST['team1_id']) ? $_POST['team1_id'] : null;
    $team2_id = isset($_POST['team2_id']) ? $_POST['team2_id'] : null;
    $date = isset($_POST['date']) ? $_POST['date'] : null;
    $time = isset($_POST['time']) ? $_POST['time'] : null;
    $sport_id = 1;

    // Validate inputs
    if ($team1_id && $team2_id && $date && $time && $sport_id) {
        try {
            // Prepare and execute the SQL statement
            $sql = "INSERT INTO matches 
                (Date, Time, SportID, Team1ID, Team2ID, ScoreTeam1, ScoreTeam2, PlayerStats, ArbiterID, IsUpcoming, NotificationSent, HasEnded) 
                VALUES (?, ?, ?, ?, ?, NULL, NULL, NULL, NULL, TRUE, FALSE, FALSE)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$date, $time, $sport_id, $team1_id, $team2_id]);

            // Redirect to the upcoming matches page
            header('Location: ../view/pages/upcoming_matches.php');
            exit();
        } catch (PDOException $e) {
            // Log and display error
            error_log('Error adding match: ' . $e->getMessage());
            echo 'Error adding match: ' . $e->getMessage();
        }
    } else {
        // Handle the case where required data is missing
        echo 'Please fill out all fields.';
    }
}

?>