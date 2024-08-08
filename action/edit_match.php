<?php 
session_start();
include '../settings/connection.php';
$is_admin = isset($_SESSION['AdminID']);
// Check if the logged-in coach is associated with the team in the URL, and if so, make them a viewer
$can_edit = $is_admin;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit' && $can_edit) {
    $match_id = $_POST['match_id'];
    $date = $_POST['date'];
    $time = $_POST['time'];

    try {
        $sql = "UPDATE matches SET Date = ?, Time = ? WHERE MatchID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$date, $time, $match_id]);

        header('Location: upcoming_matches.php?team_id=' . $team_id);
        exit();
    } catch (PDOException $e) {
        error_log('Error editing match: ' . $e->getMessage());
        echo 'Error editing match: ' . $e->getMessage();
    }
}

?>