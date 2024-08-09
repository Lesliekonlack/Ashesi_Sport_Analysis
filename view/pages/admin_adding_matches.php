<?php
session_start();
include '../../settings/connection.php';

// Ensure only admin can access this page
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../../login.php');
    exit();
}

// Fetch teams
$teams_sql = "SELECT * FROM teams";
$teams_stmt = $conn->prepare($teams_sql);
$teams_stmt->execute();
$teams = $teams_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch sports
$sports_sql = "SELECT * FROM sports";
$sports_stmt = $conn->prepare($sports_sql);
$sports_stmt->execute();
$sports = $sports_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch tournaments
$tournaments_sql = "SELECT * FROM tournaments";
$tournaments_stmt = $conn->prepare($tournaments_sql);
$tournaments_stmt->execute();
$tournaments = $tournaments_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch upcoming matches
$sql = "SELECT m.MatchID, m.Date, m.Time, m.SportID, m.Team1ID, m.Team2ID, m.TournamentID, 
               t1.TeamName as Team1Name, t2.TeamName as Team2Name, s.SportName, 
               tr.Name as TournamentName
        FROM matches m
        JOIN teams t1 ON m.Team1ID = t1.TeamID
        JOIN teams t2 ON m.Team2ID = t2.TeamID
        JOIN sports s ON m.SportID = s.SportID
        LEFT JOIN tournaments tr ON m.TournamentID = tr.TournamentID
        WHERE m.IsUpcoming = TRUE";
$stmt = $conn->prepare($sql);
$stmt->execute();
$upcoming_matches = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Fetch past matches
$sql = "SELECT m.MatchID, m.Date, m.Time, m.SportID, m.Team1ID, m.Team2ID, t1.TeamName as Team1Name, t2.TeamName as Team2Name, s.SportName
        FROM matches m
        JOIN teams t1 ON m.Team1ID = t1.TeamID
        JOIN teams t2 ON m.Team2ID = t2.TeamID
        JOIN sports s ON m.SportID = s.SportID
        WHERE m.HasEnded = TRUE";
$stmt = $conn->prepare($sql);
$stmt->execute();
$past_matches = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission to add a match
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $team1_id = $_POST['team1_id'];
    $team2_id = $_POST['team2_id'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $sport_id = $_POST['sport_id'];
    $tournament_id = ($_POST['tournament_id'] === 'friendly') ? null : $_POST['tournament_id'];

    try {
        $sql = "INSERT INTO matches (Date, Time, SportID, Team1ID, Team2ID, TournamentID, IsUpcoming) VALUES (?, ?, ?, ?, ?, ?, TRUE)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$date, $time, $sport_id, $team1_id, $team2_id, $tournament_id]);

        $_SESSION['success_message'] = 'Match added successfully.';
        header('Location: admin_adding_matches.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Error adding match: ' . $e->getMessage();
    }
}

// Handle delete match
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $match_id = $_POST['match_id'];

    try {
        $sql = "DELETE FROM matches WHERE MatchID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$match_id]);

        $_SESSION['success_message'] = 'Match deleted successfully.';
        header('Location: admin_adding_matches.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Error deleting match: ' . $e->getMessage();
    }
}

// Handle edit match
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $match_id = $_POST['match_id'];
    $date = $_POST['date'];
    $time = $_POST['time'];

    try {
        $sql = "UPDATE matches SET Date = ?, Time = ? WHERE MatchID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$date, $time, $match_id]);

        $_SESSION['success_message'] = 'Match updated successfully.';
        header('Location: admin_adding_matches.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Error editing match: ' . $e->getMessage();
    }
}

// Handle update match details
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_details') {
    $match_id = $_POST['match_id'];
    $team_id = $_POST['team_id'];
    $goalscorer = $_POST['goalscorer'] === 'na' ? null : $_POST['goalscorer'];
    $minute_scored = $_POST['minute_scored'] === 'na' ? null : $_POST['minute_scored'];
    $assisted_by = $_POST['assisted_by'] === 'na' ? null : $_POST['assisted_by'];
    $cleansheets = $_POST['cleansheets'] === 'na' ? null : $_POST['cleansheets'];

    try {
        $sql = "INSERT INTO match_events (MatchID, EventType, PlayerID, Minute, Details, TeamID) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if ($goalscorer) {
            $player_sql = "SELECT Name FROM players WHERE PlayerID = ?";
            $player_stmt = $conn->prepare($player_sql);
            $player_stmt->execute([$goalscorer]);
            $goalscorer_data = $player_stmt->fetch(PDO::FETCH_ASSOC);
            $goalscorer_name = $goalscorer_data ? $goalscorer_data['Name'] : 'Unknown player';

            $details = "Goal scored by: $goalscorer_name";
            if ($assisted_by) {
                $assistant_sql = "SELECT Name FROM players WHERE PlayerID = ?";
                $assistant_stmt = $conn->prepare($assistant_sql);
                $assistant_stmt->execute([$assisted_by]);
                $assistant_data = $assistant_stmt->fetch(PDO::FETCH_ASSOC);
                $assistant_name = $assistant_data ? $assistant_data['Name'] : 'Unknown player';

                $details .= ", Assisted by: $assistant_name";
            }
            
            $stmt->execute([$match_id, 'Goal', $goalscorer, $minute_scored, $details, $team_id]);
        }

        if ($cleansheets) {
            $player_id = ($cleansheets === 'team') ? null : $cleansheets;
            $details = null;

            if ($player_id) {
                $player_sql = "SELECT Name FROM players WHERE PlayerID = ?";
                $player_stmt = $conn->prepare($player_sql);
                $player_stmt->execute([$player_id]);
                $player = $player_stmt->fetch(PDO::FETCH_ASSOC);

                if ($player) {
                    $player_name = $player['Name'];
                    $details = "Clean Sheet achieved by goalkeeper: $player_name";
                }
            }

            $stmt->execute([$match_id, 'CleanSheet', $player_id, null, $details, $team_id]);
        }

        $_SESSION['success_message'] = 'Match details updated successfully.';
        header('Location: admin_adding_matches.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Error updating match details: ' . $e->getMessage();
        header('Location: admin_adding_matches.php');
        exit();
    }
}

// Handle update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $match_id = $_POST['match_id'];

    try {
        $sql = "UPDATE matches SET HasEnded = 1, IsUpcoming = 0 WHERE MatchID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$match_id]);

        $_SESSION['success_message'] = 'Match status updated successfully.';
        header('Location: admin_adding_matches.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Error updating match status: ' . $e->getMessage();
        header('Location: admin_adding_matches.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Add Matches</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: white;
            padding-top: 30px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .header-container {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: white;
            padding: 5px 10px;
            height: 80px;
            border-radius: 1px;
            z-index: 1000;
            width: 100%;
            box-sizing: border-box;
        }

        .logo-container {
            height: 100%;
            display: flex;
            align-items: center;
            margin-left: 20px;
        }

        .site-title {
            font-size: 24px;
            color: #4B0000;
            margin-right: 20px;
        }

        .logo {
            height: 100%;
            margin-right: 10px;
        }

        nav ul {
            list-style: none;
            display: flex;
            gap: 10px;
            margin: 0;
            padding: 0;
        }

        nav ul li {
            position: relative;
            display: inline-block;
        }

        nav ul li a {
            color: #4B0000;
            text-decoration: none;
            font-weight: bold;
            line-height: 20px;
            padding: 0 15px;
        }

        nav ul li:hover > ul {
            display: block;
        }

        nav ul ul {
            display: none;
            position: absolute;
            background-color: white;
            min-width: 160px;
            box-shadow: 0px 8px 16px rgba(0,0,0,0.2);
            z-index: 1000;
        }

        nav ul ul li {
            display: block;
            text-align: left;
        }

        nav ul ul li a {
            color: #4B0000;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }

        nav ul ul li a:hover {
            background-color: #ddd;
        }

        .nav-icons img {
            height: 28px;
            cursor: pointer;
        }

        .nav-icons {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .profile-icon {
            height: 24px;
            cursor: pointer;
        }

        .sidebar {
            width: 200px;
            background-color: #4B0000;
            padding: 20px;
            height: 100vh;
            position: fixed;
            top: 80px;
            left: 0;
            overflow-y: auto;
        }

        .sidebar .team-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar .team-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }

        .sidebar h2 {
            color: white;
            font-size: 1.5rem;
            margin-bottom: 20px;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            margin-bottom: 15px;
        }

        .sidebar ul li a {
            color: white;
            text-decoration: none;
            font-weight: bold;
        }

        .sidebar ul li a:hover {
            text-decoration: underline;
        }

        .main-content {
            margin-left: 270px;
            padding: 20px;
            flex: 1;
        }

        .section {
            margin-bottom: 40px;
        }

        .section h2 {
            color: #4B0000;
            font-size: 2rem;
            margin-bottom: 20px;
            text-align: center;
        }

        .upcoming-matches, .past-matches {
            background-color: #88C057;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .upcoming-matches h3, .past-matches h3 {
            color: #4B0000;
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .upcoming-matches p, .past-matches p {
            color: #333;
            margin-bottom: 10px;
        }

        .upcoming-matches button, .past-matches button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            background-color: #4B0000;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-right: 10px;
        }

        .upcoming-matches button:hover, .past-matches button:hover {
            background-color: #333;
        }

        .upcoming-matches .edit-form,
        .upcoming-matches .details-form {
            display: none;
        }

        .upcoming-matches.editing .edit-form,
        .upcoming-matches.updating .details-form {
            display: block;
        }

        .upcoming-matches.editing .match-details,
        .upcoming-matches.updating .match-details {
            display: none;
        }

        .details-form label {
            display: block;
            margin-bottom: 5px;
            color: #333;
        }

        .details-form select,
        .details-form input {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .error-message, .success-message {
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 5px;
            color: white;
        }

        .error-message {
            background-color: #f44336;
        }

        .success-message {
            background-color: #4CAF50;
        }

        footer {
            background-color: #4B0000;
            color: white;
            text-align: center;
            padding: 10px 0;
            border-radius: 5px;
            position: static;
            bottom: 0;
            width: 100%;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 10px;
        }

        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropbtn {
            background-color: #4B0000;
            color: white;
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: white;
            min-width: 160px;
            box-shadow: 0px 8px 16px rgba(0,0,0,0.2);
            z-index: 1;
        }

        .dropdown-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }

        .dropdown-content a:hover {
            background-color: #ddd;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }

        .dropdown:hover .dropbtn {
            background-color: #3e8e41;
        }

        .add-match-form {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .add-match-form h3 {
            color: #4B0000;
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .add-match-form label {
            display: block;
            margin-bottom: 5px;
            color: #333;
        }

        .add-match-form select,
        .add-match-form input {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .add-match-form button {
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            color: white;
            background-color: #4B0000;
            transition: background-color 0.3s ease;
        }

        .add-match-form button:hover {
            background-color: #333;
        }

        .toggle-button {
            background-color: #4B0000;
            color: white;
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .toggle-button:hover {
            background-color: #333;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo-container">
                <img src="https://rawcdn.githack.com/naomikonlack/WEBTECHGITDEMO/246c29d2a7c8bff15a8f6206d9f7084c6018fa5a/Untitled_Artwork%204.png" alt="Ashesi Sports Insight Logo" class="logo">
                <div class="site-title">Ashesi Sports Insight</div>
            </div>
            <nav>
                <ul>
                    <!-- other navigation items -->
                </ul>
                </nav>
            <div class="nav-icons">
                <?php if (isset($_SESSION['admin_id'])): ?>
                    <div class="dropdown">
                        <button class="dropbtn">Welcome, Admin</button>
                        <div class="dropdown-content">
                            <a href="../../action/logout.php">Logout</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </header>
    <div class="sidebar">
        <div class="team-info">
            <img src="https://via.placeholder.com/40" alt="Admin">
            <h2>Admin Panel</h2>
        </div>
        <ul>
            <li><a href="#add-match">Add Match</a></li>
            <li><a href="admin_upload_story.php">ADD Stories</a></li>
            <li><a href="admin_manage_tournaments.php">ADD Upcoming tournaments</a></li>
            <li><a href="admin_manage_awards.php">ADD Upcoming Events</a></li>

        </ul>
    </div>
    <div class="main-content">
        <section id="add-match" class="section">
            <h2>Add New Match</h2>
            <div class="add-match-form">
                <form action="admin_adding_matches.php" method="post">
                    <input type="hidden" name="action" value="add">
                    
                    <label for="team1_id">Team 1:</label>
                    <select name="team1_id" id="team1_id" required>
                        <?php foreach ($teams as $team): ?>
                            <option value="<?php echo htmlspecialchars($team['TeamID']); ?>"><?php echo htmlspecialchars($team['TeamName']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    
                    <label for="team2_id">Team 2:</label>
                    <select name="team2_id" id="team2_id" required>
                        <?php foreach ($teams as $team): ?>
                            <option value="<?php echo htmlspecialchars($team['TeamID']); ?>"><?php echo htmlspecialchars($team['TeamName']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    
                    <label for="date">Date:</label>
                    <input type="date" name="date" id="date" required>
                    
                    <label for="time">Time:</label>
                    <input type="time" name="time" id="time" required>
                    
                    <label for="sport_id">Sport:</label>
                    <select name="sport_id" id="sport_id" required>
                        <?php foreach ($sports as $sport): ?>
                            <option value="<?php echo htmlspecialchars($sport['SportID']); ?>"><?php echo htmlspecialchars($sport['SportName']); ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label for="tournament_id">Tournament (Optional):</label>
                    <select name="tournament_id" id="tournament_id">
                        <option value="friendly">Friendly</option>
                        <?php foreach ($tournaments as $tournament): ?>
                            <option value="<?php echo htmlspecialchars($tournament['TournamentID']); ?>"><?php echo htmlspecialchars($tournament['Name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    
                    <button type="submit">Add Match</button>
                </form>
            </div>
        </section>
        
        <section id="upcoming-matches" class="section">
            <h2>Upcoming Matches</h2>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="error-message">
                    <p><?php echo htmlspecialchars($_SESSION['error_message']); ?></p>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="success-message">
                    <p><?php echo htmlspecialchars($_SESSION['success_message']); ?></p>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <?php foreach ($upcoming_matches as $match): 
                // Fetch players for Team1 and Team2
                $team1_players_sql = "SELECT * FROM players WHERE TeamID = ?";
                $team1_players_stmt = $conn->prepare($team1_players_sql);
                $team1_players_stmt->execute([$match['Team1ID']]);
                $team1_players = $team1_players_stmt->fetchAll(PDO::FETCH_ASSOC);

                $team2_players_sql = "SELECT * FROM players WHERE TeamID = ?";
                $team2_players_stmt = $conn->prepare($team2_players_sql);
                $team2_players_stmt->execute([$match['Team2ID']]);
                $team2_players = $team2_players_stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>
                <div class="upcoming-matches" data-match-id="<?php echo htmlspecialchars($match['MatchID']); ?>">
                    <div class="match-details">
                    <h3><?php echo htmlspecialchars($match['Team1Name']) . ' vs ' . htmlspecialchars($match['Team2Name']); ?></h3>
                    <p>Date: <?php echo htmlspecialchars($match['Date']); ?></p>
                    <p>Time: <?php echo htmlspecialchars($match['Time']); ?> GMT</p>
                    <p>Sport: <?php echo htmlspecialchars($match['SportName']); ?></p>

                    <?php 
                    // Check if the match has a TournamentID and fetch the tournament name
                    if (!empty($match['TournamentID'])) {
                        $tournament_sql = "SELECT Name FROM tournaments WHERE TournamentID = ?";
                        $tournament_stmt = $conn->prepare($tournament_sql);
                        $tournament_stmt->execute([$match['TournamentID']]);
                        $tournament = $tournament_stmt->fetch(PDO::FETCH_ASSOC);

                        if ($tournament) {
                            echo '<p>Tournament: ' . htmlspecialchars($tournament['Name']) . '</p>';
                        } else {
                            echo '<p>Tournament: Not specified</p>';
                        }
                    } else {
                        echo '<p>Friendly Match</p>';
                    }
                    ?>

                        <button class="edit-button">Edit</button>
                        <button class="update-details-button" data-team-id="1">Update Details for <?php echo htmlspecialchars($match['Team1Name']); ?></button>
                        <button class="update-details-button" data-team-id="2">Update Details for <?php echo htmlspecialchars($match['Team2Name']); ?></button>
                        <form action="admin_adding_matches.php" method="post" class="status-form">
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="match_id" value="<?php echo htmlspecialchars($match['MatchID']); ?>">
                            <button type="submit" class="status-button">Status</button>
                        </form>
                        <form action="admin_adding_matches.php" method="post" class="delete-form">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="match_id" value="<?php echo htmlspecialchars($match['MatchID']); ?>">
                            <button type="submit">Delete</button>
                        </form>
                    </div>
                    <div class="edit-form">
                        <form action="admin_adding_matches.php" method="post">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="match_id" value="<?php echo htmlspecialchars($match['MatchID']); ?>">
                            <label for="date_<?php echo htmlspecialchars($match['MatchID']); ?>">Date:</label>
                            <input type="date" name="date" id="date_<?php echo htmlspecialchars($match['MatchID']); ?>" value="<?php echo htmlspecialchars($match['Date']); ?>" required>
                            <label for="time_<?php echo htmlspecialchars($match['MatchID']); ?>">Time:</label>
                            <input type="time" name="time" id="time_<?php echo htmlspecialchars($match['MatchID']); ?>" value="<?php echo htmlspecialchars($match['Time']); ?>" required>
                            <button type="submit">Save</button>
                            <button type="button" class="cancel-button">Cancel</button>
                        </form>
                    </div>
                    <!-- Details form for Team 1 -->
                    <div class="details-form" id="details-form-<?php echo htmlspecialchars($match['MatchID']); ?>-team1">
                        <form action="admin_adding_matches.php" method="post">
                            <input type="hidden" name="action" value="update_details">
                            <input type="hidden" name="match_id" value="<?php echo htmlspecialchars($match['MatchID']); ?>">
                            <input type="hidden" name="team_id" value="<?php echo htmlspecialchars($match['Team1ID']); ?>">
                            <label for="goalscorer_team1_<?php echo htmlspecialchars($match['MatchID']); ?>">Goalscorer:</label>
                            <select name="goalscorer" id="goalscorer_team1_<?php echo htmlspecialchars($match['MatchID']); ?>" required>
                                <option value="na">Non-applicable</option>
                                <?php foreach ($team1_players as $player): ?>
                                    <option value="<?php echo htmlspecialchars($player['PlayerID']); ?>"><?php echo htmlspecialchars($player['Name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <label for="minute_scored_team1_<?php echo htmlspecialchars($match['MatchID']); ?>">Minute Scored:</label>
                            <select name="minute_scored" id="minute_scored_team1_<?php echo htmlspecialchars($match['MatchID']); ?>" required>
                                <option value="na">Non-applicable</option>
                                <?php for ($i = 1; $i <= 120; $i++): ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                            <label for="assisted_by_team1_<?php echo htmlspecialchars($match['MatchID']); ?>">Assisted By:</label>
                            <select name="assisted_by" id="assisted_by_team1_<?php echo htmlspecialchars($match['MatchID']); ?>" required>
                                <option value="na">Non-applicable</option>
                                <?php foreach ($team1_players as $player): ?>
                                    <option value="<?php echo htmlspecialchars($player['PlayerID']); ?>"><?php echo htmlspecialchars($player['Name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <label for="cleansheets_team1_<?php echo htmlspecialchars($match['MatchID']); ?>">Cleansheets:</label>
                            <select name="cleansheets" id="cleansheets_team1_<?php echo htmlspecialchars($match['MatchID']); ?>" required>
                                <option value="na">Non-applicable</option>
                                <option value="team">Entire Team</option>
                                <?php foreach ($team1_players as $player): ?>
                                    <option value="<?php echo htmlspecialchars($player['PlayerID']); ?>"><?php echo htmlspecialchars($player['Name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit">Save</button>
                            <button type="button" class="cancel-details-button">Cancel</button>
                        </form>
                    </div>

                    <!-- Details form for Team 2 -->
                    <div class="details-form" id="details-form-<?php echo htmlspecialchars($match['MatchID']); ?>-team2">
                        <form action="admin_adding_matches.php" method="post">
                            <input type="hidden" name="action" value="update_details">
                            <input type="hidden" name="match_id" value="<?php echo htmlspecialchars($match['MatchID']); ?>">
                            <input type="hidden" name="team_id" value="<?php echo htmlspecialchars($match['Team2ID']); ?>">
                            <label for="goalscorer_team2_<?php echo htmlspecialchars($match['MatchID']); ?>">Goalscorer:</label>
                            <select name="goalscorer" id="goalscorer_team2_<?php echo htmlspecialchars($match['MatchID']); ?>" required>
                                <option value="na">Non-applicable</option>
                                <?php foreach ($team2_players as $player): ?>
                                    <option value="<?php echo htmlspecialchars($player['PlayerID']); ?>"><?php echo htmlspecialchars($player['Name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <label for="minute_scored_team2_<?php echo htmlspecialchars($match['MatchID']); ?>">Minute Scored:</label>
                            <select name="minute_scored" id="minute_scored_team2_<?php echo htmlspecialchars($match['MatchID']); ?>" required>
                                <option value="na">Non-applicable</option>
                                <?php for ($i = 1; $i <= 120; $i++): ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                            <label for="assisted_by_team2_<?php echo htmlspecialchars($match['MatchID']); ?>">Assisted By:</label>
                            <select name="assisted_by" id="assisted_by_team2_<?php echo htmlspecialchars($match['MatchID']); ?>" required>
                                <option value="na">Non-applicable</option>
                                <?php foreach ($team2_players as $player): ?>
                                    <option value="<?php echo htmlspecialchars($player['PlayerID']); ?>"><?php echo htmlspecialchars($player['Name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <label for="cleansheets_team2_<?php echo htmlspecialchars($match['MatchID']); ?>">Cleansheets:</label>
                            <select name="cleansheets" id="cleansheets_team2_<?php echo htmlspecialchars($match['MatchID']); ?>" required>
                                <option value="na">Non-applicable</option>
                                <option value="team">Entire Team</option>
                                <?php foreach ($team2_players as $player): ?>
                                    <option value="<?php echo htmlspecialchars($player['PlayerID']); ?>"><?php echo htmlspecialchars($player['Name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit">Save</button>
                            <button type="button" class="cancel-details-button">Cancel</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>

        <section id="past-matches" class="section" style="display: none;">
            <h2>Past Matches</h2>
            <?php foreach ($past_matches as $match): ?>
                <div class="past-matches" data-match-id="<?php echo htmlspecialchars($match['MatchID']); ?>">
                    <div class="match-details">
                        <h3><?php echo htmlspecialchars($match['Team1Name']) . ' vs ' . htmlspecialchars($match['Team2Name']); ?></h3>
                        <p>Date: <?php echo htmlspecialchars($match['Date']); ?></p>
                        <p>Time: <?php echo htmlspecialchars($match['Time']); ?> GMT</p>
                        <p>Sport: <?php echo htmlspecialchars($match['SportName']); ?></p>
                        <form action="admin_adding_matches.php" method="post" class="delete-form">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="match_id" value="<?php echo htmlspecialchars($match['MatchID']); ?>">
                            <button type="submit">Delete</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>

        <button class="toggle-button">Show Past Matches</button>
    </div>

    <footer>
        <div class="footer-container">
            <p>&copy; 2024 Ashesi Sports Insight. All rights reserved.</p>
        </div>
    </footer>

    <script>
        document.querySelectorAll('.edit-button').forEach(button => {
            button.addEventListener('click', () => {
                const matchElement = button.closest('.upcoming-matches');
                matchElement.classList.add('editing');
            });
        });

        document.querySelectorAll('.cancel-button').forEach(button => {
            button.addEventListener('click', () => {
                const matchElement = button.closest('.upcoming-matches');
                matchElement.classList.remove('editing');
            });
        });

        document.querySelectorAll('.update-details-button').forEach(button => {
    button.addEventListener('click', (event) => {
        const matchElement = event.target.closest('.upcoming-matches');
        const teamId = event.target.getAttribute('data-team-id');
        
        // Remove 'updating' class from all match elements to avoid multiple forms opening
        document.querySelectorAll('.upcoming-matches').forEach(match => {
            match.classList.remove('updating');
            match.querySelectorAll('.details-form').forEach(form => {
                form.style.display = 'none'; // Ensure all forms are hidden
            });
        });

        // Add 'updating' class only to the current match element
        matchElement.classList.add('updating');
        
        // Show only the details form that matches the clicked button's team ID
        matchElement.querySelector(`.details-form#details-form-${matchElement.getAttribute('data-match-id')}-team${teamId}`).style.display = 'block';
    });
});


        document.querySelectorAll('.cancel-details-button').forEach(button => {
            button.addEventListener('click', () => {
                const matchElement = button.closest('.upcoming-matches');
                matchElement.classList.remove('updating');
                matchElement.querySelectorAll('.details-form').forEach(form => {
                    form.style.display = 'none';
                });
            });
        });

        document.querySelector('.toggle-button').addEventListener('click', () => {
            const pastMatchesSection = document.querySelector('#past-matches');
            if (pastMatchesSection.style.display === 'none') {
                pastMatchesSection.style.display = 'block';
                document.querySelector('.toggle-button').textContent = 'Hide Past Matches';
            } else {
                pastMatchesSection.style.display = 'none';
                document.querySelector('.toggle-button').textContent = 'Show Past Matches';
            }
        });

        document.querySelectorAll('.status-button').forEach(button => {
    button.addEventListener('click', (event) => {
        event.preventDefault();
        
        const confirmation = confirm("Updating the match status will mark it as completed and no longer upcoming. Are you sure you want to proceed?");
        
        if (confirmation) {
            // Submit the form associated with the status button
            const statusForm = button.closest('.status-form');
            statusForm.submit();
        }
    });
});

    </script>
</body>
</html>
