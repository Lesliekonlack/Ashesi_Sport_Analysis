<?php
include '../../settings/connection.php';

if (isset($_GET['team_id'])) {
    $team_id = intval($_GET['team_id']);

    $players_sql = "SELECT PlayerID, Name, Position FROM players WHERE TeamID = ?";
    $players_stmt = $conn->prepare($players_sql);
    $players_stmt->execute([$team_id]);
    $players = $players_stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($players);
}
?>
