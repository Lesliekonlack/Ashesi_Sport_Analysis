<?php
session_start();
include '../../settings/connection.php';


// Fetch matches (regardless of status) along with tournament names if available
$sql = "SELECT m.MatchID, m.Date, m.Time, t1.TeamName as Team1Name, t2.TeamName as Team2Name, s.SportName, 
               tr.Name as TournamentName
        FROM matches m
        JOIN teams t1 ON m.Team1ID = t1.TeamID
        JOIN teams t2 ON m.Team2ID = t2.TeamID
        JOIN sports s ON m.SportID = s.SportID
        LEFT JOIN tournaments tr ON m.TournamentID = tr.TournamentID
        ORDER BY m.Date ASC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$matches = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Fetch tournaments
$tournaments_sql = "SELECT * FROM tournaments";
$tournaments_stmt = $conn->prepare($tournaments_sql);
$tournaments_stmt->execute();
$tournaments = $tournaments_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch coach ID and team ID from session
$is_coach = isset($_SESSION['coach_id']);
$coach_team_id = $is_coach && isset($_SESSION['team_id']) ? $_SESSION['team_id'] : null;
$coach_team_name = '';
$coach_team_logo = '';

// Fetch the team details for the coach's team if available
if ($is_coach && $coach_team_id) {
    $coach_team_sql = "SELECT TeamName FROM teams WHERE TeamID = ?";
    $coach_team_stmt = $conn->prepare($coach_team_sql);
    $coach_team_stmt->execute([$coach_team_id]);
    $coach_team_info = $coach_team_stmt->fetch(PDO::FETCH_ASSOC);

    if ($coach_team_info) {
        $coach_team_name = $coach_team_info['TeamName'];
    }
}

// Get the team ID from the URL parameter
$team_id = isset($_GET['team_id']) ? intval($_GET['team_id']) : $coach_team_id;
$team_name = '';
$team_logo = '';

// Fetch the team details for the specified team ID
if ($team_id) {
    $sql = "SELECT TeamName, Logo, SportID FROM teams WHERE TeamID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$team_id]);
    $team_info = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($team_info) {
        $team_name = $team_info['TeamName'];
        $team_logo = $team_info['Logo'] ? '../../uploads/' . $team_info['Logo'] : '../../uploads/default_logo.png';
        $sport_id = $team_info['SportID'];
    }
}

// Check if the logged-in coach is associated with the team in the URL, and if so, make them a viewer
$can_edit = $is_coach && $coach_team_id !== $team_id;

// Fetch players for the team
$players_sql = "SELECT * FROM players WHERE TeamID = ?";
$players_stmt = $conn->prepare($players_sql);
$players_stmt->execute([$team_id]);
$players = $players_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch goalkeepers for the team
$goalkeepers = array_filter($players, function($player) {
    return stripos($player['Position'], 'goalkeeper') !== false || stripos($player['Position'], 'keeper') !== false;
});

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


// Fetch past matches for the specified team
$sql = "SELECT m.MatchID, m.Date, m.Time, m.SportID, m.Team1ID, m.Team2ID, m.HasEnded,
               t1.TeamName as Team1Name, t2.TeamName as Team2Name, s.SportName
        FROM matches m
        JOIN teams t1 ON m.Team1ID = t1.TeamID
        JOIN teams t2 ON m.Team2ID = t2.TeamID
        JOIN sports s ON m.SportID = s.SportID
        WHERE m.HasEnded = 1
        AND (m.Team1ID = ? OR m.Team2ID = ?)";
$stmt = $conn->prepare($sql);
$stmt->execute([$team_id, $team_id]);
$past_matches = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch teams for the sport
$teams_sql = "SELECT * FROM teams WHERE SportID = ?";
$teams_stmt = $conn->prepare($teams_sql);
$teams_stmt->execute([$sport_id]);
$teams = $teams_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch sports for the form (optional if needed)
$sports_sql = "SELECT * FROM sports";
$sports_stmt = $conn->prepare($sports_sql);
$sports_stmt->execute();
$sports = $sports_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission to add a match
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add' && $can_edit) {
    $team1_id = $team_id;
    $team2_id = $_POST['team2_id'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $sport_id = $team_info['SportID'];

    try {
        $sql = "INSERT INTO matches (Date, Time, SportID, Team1ID, Team2ID, IsUpcoming) VALUES (?, ?, ?, ?, ?, TRUE)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$date, $time, $sport_id, $team1_id, $team2_id]);

        header('Location: upcoming_matches.php?team_id=' . $team_id);
        exit();
    } catch (PDOException $e) {
        error_log('Error adding match: ' . $e->getMessage());
        echo 'Error adding match: ' . $e->getMessage();
    }
}

// Handle delete match
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete' && $can_edit) {
    $match_id = $_POST['match_id'];

    try {
        $sql = "UPDATE matches SET IsDeleted = 1 WHERE MatchID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$match_id]);

        header('Location: upcoming_matches.php?team_id=' . $team_id);
        exit();
    } catch (PDOException $e) {
        error_log('Error deleting match: ' . $e->getMessage());
        echo 'Error deleting match: ' . $e->getMessage();
    }
}

// Handle edit match
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

// Handle update match details
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_details' && $can_edit) {
    $match_id = $_POST['match_id'];
    $goalscorer = $_POST['goalscorer'] === 'na' ? null : $_POST['goalscorer'];
    $minute_scored = $_POST['minute_scored'] === 'na' ? null : $_POST['minute_scored'];
    $assisted_by = $_POST['assisted_by'] === 'na' ? null : $_POST['assisted_by'];
    $cleansheets = $_POST['cleansheets'] === 'na' ? null : $_POST['cleansheets'];

    try {
        $sql = "INSERT INTO match_events (MatchID, EventType, PlayerID, Minute, Details, TeamID) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if ($goalscorer) {
            // Retrieve the goalscorer's name
            $player_sql = "SELECT Name FROM players WHERE PlayerID = ?";
            $player_stmt = $conn->prepare($player_sql);
            $player_stmt->execute([$goalscorer]);
            $goalscorer_data = $player_stmt->fetch(PDO::FETCH_ASSOC);
            $goalscorer_name = $goalscorer_data ? $goalscorer_data['Name'] : 'Unknown player';

            // Format details for the goal event
            $details = "Goal scored by: $goalscorer_name";
            if ($assisted_by) {
                // Retrieve the assistant's name
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
            // Initialize variables for player details
            $player_id = ($cleansheets === 'team') ? null : $cleansheets;
            $details = null;

            if ($player_id) {
                // Fetch player name from the database
                $player_sql = "SELECT Name FROM players WHERE PlayerID = ?";
                $player_stmt = $conn->prepare($player_sql);
                $player_stmt->execute([$player_id]);
                $player = $player_stmt->fetch(PDO::FETCH_ASSOC);

                if ($player) {
                    $player_name = $player['Name'];
                    $details = "Clean Sheet achieved by goalkeeper: $player_name";
                }
            } else {
                // For team clean sheets, leave details as null
                $details = null;
            }

            // Insert CleanSheet event
            $stmt->execute([$match_id, 'CleanSheet', $player_id, null, $details, $team_id]);
        }

        $_SESSION['success_message'] = 'Match details updated successfully';
        header('Location: upcoming_matches.php?team_id=' . $team_id);
        exit();
    } catch (PDOException $e) {
        error_log('Error updating match details: ' . $e->getMessage());
        $_SESSION['error_message'] = 'Error updating match details: ' . $e->getMessage();
        header('Location: upcoming_matches.php?team_id=' . $team_id);
        exit();
    }
}

// Handle update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status' && $can_edit) {
    $match_id = $_POST['match_id'];

    try {
        $sql = "UPDATE matches SET HasEnded = 1, IsUpcoming = 0 WHERE MatchID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$match_id]);

        $_SESSION['success_message'] = 'Match status updated successfully.';
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Error updating match status: ' . $e->getMessage();
    }

    header('Location: upcoming_matches.php?team_id=' . $team_id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upcoming Matches - Ashesi Sports Insight</title>
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
                    <li>
                        <a style="margin-left: 820px;"href="footballsport.php">Go Back To View All Clubs</a>
                       
                    </li>
                   
                    <li><a href="homepage.php">HOME</a></li>  
                </nav>

            <div class="nav-icons">
                <?php if (isset($_SESSION['coach_id'])): ?>
                    <?php if ($is_coach && $coach_team_id): ?>
                        <div class="dropdown">
                            <button class="dropbtn">Welcome, <?php echo htmlspecialchars($_SESSION['coach_name']); ?></button>
                            <div class="dropdown-content">
                                <a href="../../action/logout.php">Logout</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="dropdown">
                            <button class="dropbtn"><?php echo htmlspecialchars($_SESSION['coach_name']); ?></button>
                            <div class="dropdown-content">
                                <a href="../../settings/logout.php">Logout</a>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <img src="https://cdn-icons-png.flaticon.com/512/1077/1077114.png" alt="Profile Icon" class="profile-icon">
                <?php endif; ?>
            </div>
        </div>
    </header>
    <div class="sidebar">
        <div class="team-info">
            <img src="<?php echo htmlspecialchars($team_logo); ?>" alt="Team Logo">
            <h2><?php echo htmlspecialchars($team_name); ?></h2>
        </div>
        <ul>
        <li><a href="footballclub.php?team_id=<?php echo htmlspecialchars($team_id); ?>"> Team Overview</a></li>
            <li><a href="team_stories.php?team_id=<?php echo htmlspecialchars($team_id); ?>">Team Stories</a></li>
            <li><a href="teamstatistics.php?team_id=<?php echo htmlspecialchars($team_id); ?>">Team Stats</a></li>
            <li><a href="players.php?team_id=<?php echo htmlspecialchars($team_id); ?>">Players</a></li>
            <li><a href="competitions.php?team_id=<?php echo htmlspecialchars($team_id); ?>">Upcoming Competitions</a></li>
            <li><a href="awards.php?team_id=<?php echo htmlspecialchars($team_id); ?>">Awards</a></li>
        </ul>

    </div>
    <div class="main-content">
    <section id="upcoming-matches" class="section">
        <h2>Upcoming Matches</h2>

        <?php if ($can_edit): ?>
            <div class="add-match-form">
                <h3>Add New Match</h3>
                <form action="upcoming_matches.php" method="post">
                    <input type="hidden" name="action" value="add">
                    <label for="team1_id">Your Team:</label>
                    <input type="text" name="team1_name" id="team1_name" value="<?php echo htmlspecialchars($coach_team_name); ?>" readonly>
                    <input type="hidden" name="team1_id" value="<?php echo htmlspecialchars($coach_team_id); ?>">
                    <label for="team2_id">Opponent Team:</label>
                    <select name="team2_id" id="team2_id" required>
                        <?php foreach ($teams as $team): ?>
                            <?php if ($team['TeamID'] != $coach_team_id): ?>
                                <option value="<?php echo htmlspecialchars($team['TeamID']); ?>"><?php echo htmlspecialchars($team['TeamName']); ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    <label for="date">Date:</label>
                    <input type="date" name="date" id="date" required>
                    <label for="time">Time:</label>
                    <input type="time" name="time" id="time" required>
                    <label for="sport_id">Sport:</label>
                    <input type="text" name="sport_name" id="sport_name" value="<?php echo htmlspecialchars($sports[array_search($sport_id, array_column($sports, 'SportID'))]['SportName']); ?>" readonly>
                    <input type="hidden" name="sport_id" value="<?php echo htmlspecialchars($sport_id); ?>">
                    <button type="submit">Add Match</button>
                </form>
            </div>
        <?php endif; ?>

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

        <?php foreach ($upcoming_matches as $match): ?>
            <div class="upcoming-matches" data-match-id="<?php echo htmlspecialchars($match['MatchID']); ?>">
                <div class="match-details">
                <h3><?php echo htmlspecialchars($match['Team1Name']) . ' vs ' . htmlspecialchars($match['Team2Name']); ?></h3>
                <p>Date: <?php echo htmlspecialchars($match['Date']); ?></p>
                <p>Time: <?php echo htmlspecialchars($match['Time']); ?> GMT</p>
                <p>Sport: <?php echo htmlspecialchars($match['SportName']); ?></p>

                <?php if (!empty($match['TournamentID'])): ?>
                    <p><?php 
                        // Fetch the tournament name based on TournamentID
                        $tournament_sql = "SELECT Name FROM tournaments WHERE TournamentID = ?";
                        $tournament_stmt = $conn->prepare($tournament_sql);
                        $tournament_stmt->execute([$match['TournamentID']]);
                        $tournament = $tournament_stmt->fetch(PDO::FETCH_ASSOC);

                        echo htmlspecialchars($tournament['Name']);
                    ?></p>
                <?php else: ?>
                    <p>Friendly Match</p>
                <?php endif; ?>

    <?php if (!$can_edit): ?>
        <!-- <button>Notify Me</button> -->
    <?php else: ?>
        <?php if (strtotime($match['Date'] . ' ' . $match['Time']) <= time()): ?>
            <button class="update-details-button">Update Match Details</button>
            <button class="status-button" data-match-id="<?php echo htmlspecialchars($match['MatchID']); ?>">Status</button>
        <?php endif; ?>
        <button class="edit-button">Edit</button>
        <form action="upcoming_matches.php" method="post" class="delete-form">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="match_id" value="<?php echo htmlspecialchars($match['MatchID']); ?>">
            <button type="submit">Delete</button>
        </form>
    <?php endif; ?>
</div>

                <?php if ($can_edit): ?>
                    <div class="edit-form">
                        <form action="upcoming_matches.php" method="post">
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
                    <div class="details-form">
                        <form action="upcoming_matches.php" method="post">
                            <input type="hidden" name="action" value="update_details">
                            <input type="hidden" name="match_id" value="<?php echo htmlspecialchars($match['MatchID']); ?>">
                            <label for="goalscorer_<?php echo htmlspecialchars($match['MatchID']); ?>">Goalscorer:</label>
                            <select name="goalscorer" id="goalscorer_<?php echo htmlspecialchars($match['MatchID']); ?>" required>
                                <option value="na">Non-applicable</option>
                                <?php foreach ($players as $player): ?>
                                    <option value="<?php echo htmlspecialchars($player['PlayerID']); ?>"><?php echo htmlspecialchars($player['Name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <label for="minute_scored_<?php echo htmlspecialchars($match['MatchID']); ?>">Minute Scored:</label>
                            <select name="minute_scored" id="minute_scored_<?php echo htmlspecialchars($match['MatchID']); ?>" required>
                                <option value="na">Non-applicable</option>
                                <?php for ($i = 1; $i <= 120; $i++): ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                            <label for="assisted_by_<?php echo htmlspecialchars($match['MatchID']); ?>">Assisted By:</label>
                            <select name="assisted_by" id="assisted_by_<?php echo htmlspecialchars($match['MatchID']); ?>" required>
                                <option value="na">Non-applicable</option>
                                <?php foreach ($players as $player): ?>
                                    <option value="<?php echo htmlspecialchars($player['PlayerID']); ?>"><?php echo htmlspecialchars($player['Name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <label for="cleansheets_<?php echo htmlspecialchars($match['MatchID']); ?>">Cleansheets:</label>
                            <select name="cleansheets" id="cleansheets_<?php echo htmlspecialchars($match['MatchID']); ?>" required>
                                <option value="na">Non-applicable</option>
                                <option value="team">Entire Team</option>
                                <?php foreach ($goalkeepers as $goalkeeper): ?>
                                    <option value="<?php echo htmlspecialchars($goalkeeper['PlayerID']); ?>"><?php echo htmlspecialchars($goalkeeper['Name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit">Save</button>
                            <button type="button" class="cancel-details-button">Cancel</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <button class="toggle-button">Show Past Matches</button>

        <div class="past-matches-section" style="display: none;">
            <h2>Past Matches</h2>
            <?php foreach ($past_matches as $match): ?>
                <div class="past-matches" data-match-id="<?php echo htmlspecialchars($match['MatchID']); ?>">
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
    echo '<p>Tournament: Friendly</p>';
}
?>

                        <?php if ($can_edit): ?>
                            <form action="upcoming_matches.php" method="post" class="delete-form">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="match_id" value="<?php echo htmlspecialchars($match['MatchID']); ?>">
                                <button type="submit">Delete</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </section>
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
            button.addEventListener('click', () => {
                const matchElement = button.closest('.upcoming-matches');
                matchElement.classList.add('updating');
            });
        });

        document.querySelectorAll('.cancel-details-button').forEach(button => {
            button.addEventListener('click', () => {
                const matchElement = button.closest('.upcoming-matches');
                matchElement.classList.remove('updating');
            });
        });

        document.querySelectorAll('.status-button').forEach(button => {
            button.addEventListener('click', () => {
                const matchId = button.getAttribute('data-match-id');
                const matchElement = button.closest('.upcoming-matches');
                const matchTime = matchElement.querySelector('p:nth-child(3)').textContent.split(': ')[1];
                const currentTime = new Date().toISOString().split('T')[1].split('.')[0];
                const matchDateTime = new Date();
                matchDateTime.setHours(parseInt(matchTime.split(':')[0]));
                matchDateTime.setMinutes(parseInt(matchTime.split(':')[1]));
                matchDateTime.setSeconds(0);

                const currentTimeDate = new Date();
                const timeDifference = currentTimeDate - matchDateTime;
                const ninetyMinutes = 90 * 60 * 1000; // 90 minutes in milliseconds

                const confirmationMessage = `The match started at ${matchDateTime.toTimeString().split(' ')[0]} GMT. It is currently ${currentTime}. Are you sure you want to mark this match as ended?`;

                if (confirm(confirmationMessage)) {
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', 'upcoming_matches.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === 4 && xhr.status === 200) {
                            alert('Match status updated successfully.');
                            location.reload();
                        }
                    };
                    xhr.send('action=update_status&match_id=' + matchId);
                }
            });
        });

        document.querySelector('.toggle-button').addEventListener('click', () => {
            const pastMatchesSection = document.querySelector('.past-matches-section');
            if (pastMatchesSection.style.display === 'none') {
                pastMatchesSection.style.display = 'block';
                document.querySelector('.toggle-button').textContent = 'Hide Past Matches';
            } else {
                pastMatchesSection.style.display = 'none';
                document.querySelector('.toggle-button').textContent = 'Show Past Matches';
            }
        });

        document.querySelector('.details-form form').addEventListener('submit', function(event) {
            const goalscorer = document.querySelector('[name="goalscorer"]').value;
            const minute_scored = document.querySelector('[name="minute_scored"]').value;
            const assisted_by = document.querySelector('[name="assisted_by"]').value;
            const cleansheets = document.querySelector('[name="cleansheets"]').value;

            if (goalscorer === 'na' && minute_scored === 'na' && assisted_by === 'na' && cleansheets === 'na') {
                event.preventDefault();
                alert('At least one field must be selected.');
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            function updateFormFields(form) {
                var cleansheets = form.querySelector('select[name="cleansheets"]');
                var goalscorer = form.querySelector('select[name="goalscorer"]');
                var minuteScored = form.querySelector('select[name="minute_scored"]');
                var assistedBy = form.querySelector('select[name="assisted_by"]');

                // Handle changes in cleansheets selection
                cleansheets.addEventListener('change', function() {
                    if (cleansheets.value !== 'na') {
                        // Disable and set other fields to 'na'
                        goalscorer.value = 'na';
                        minuteScored.value = 'na';
                        assistedBy.value = 'na';

                        goalscorer.disabled = true;
                        minuteScored.disabled = true;
                        assistedBy.disabled = true;
                    } else {
                        // Enable other fields if 'na' is selected for cleansheets
                        goalscorer.disabled = false;
                        minuteScored.disabled = false;
                        assistedBy.disabled = false;
                    }
                });

                // Initial check on page load
                if (cleansheets.value !== 'na') {
                    goalscorer.value = 'na';
                    minuteScored.value = 'na';
                    assistedBy.value = 'na';

                    goalscorer.disabled = true;
                    minuteScored.disabled = true;
                    assistedBy.disabled = true;
                }

                // Validate form submission
                form.addEventListener('submit', function(event) {
                    // Check if assisted by and goalscorer are the same
                    if (goalscorer.value !== 'na' && assistedBy.value !== 'na' && goalscorer.value === assistedBy.value) {
                        alert("A goal cannot be assisted by the same player who scored it. Please select a different player for Assisted By.");
                        event.preventDefault(); // Prevent form submission
                    }
                    // Ensure goalscorer is selected before assisted by
                    if (assistedBy.value !== 'na' && goalscorer.value === 'na') {
                        alert("Please select a Goalscorer before selecting Assisted By.");
                        event.preventDefault(); // Prevent form submission
                    }
                });
            }

            // Apply to all forms with class 'details-form'
            var forms = document.querySelectorAll('.details-form form');
            forms.forEach(function(form) {
                updateFormFields(form);
            });
        });
    </script>
</body>
</html>