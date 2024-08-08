<?php
session_start();
include '../../settings/connection.php';

// Fetch player ID from the URL
$playerId = isset($_GET['player']) ? intval($_GET['player']) : null;

if (!$playerId) {
    echo "Player not found.";
    exit;
}

// Fetch player details from the database
$sql = "SELECT p.*, t.TeamName, t.Logo, t.TeamGender, t.TeamID FROM players p LEFT JOIN teams t ON p.TeamID = t.TeamID WHERE p.PlayerID = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$playerId]);
$player = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$player) {
    echo "Player not found.";
    exit;
}

// Assign team ID
$team_id = $player['TeamID'];

// Fetch trophies related to the player and count them
$sql = "SELECT COUNT(*) as TrophyCount FROM trophies WHERE PlayerID = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$playerId]);
$trophyCount = $stmt->fetch(PDO::FETCH_ASSOC)['TrophyCount'];

// Fetch trophy details
$sql = "SELECT * FROM trophies WHERE PlayerID = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$playerId]);
$trophies = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch match events related to the player
$sql = "SELECT EventType, COUNT(*) as Count FROM match_events WHERE PlayerID = ? GROUP BY EventType";
$stmt = $conn->prepare($sql);
$stmt->execute([$playerId]);
$matchEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Initialize stats
$goals = 0;
$assists = 0;
$cleanSheets = 0;

// Process match events
foreach ($matchEvents as $event) {
    if ($event['EventType'] === 'Goal') {
        $goals = $event['Count'];
    } elseif ($event['EventType'] === 'CleanSheet') {
        $cleanSheets = $event['Count'];
    }
}

// Count assists by searching the details column for the player's name
$sql = "SELECT COUNT(*) as AssistCount FROM match_events WHERE EventType = 'Goal' AND Details LIKE ?";
$stmt = $conn->prepare($sql);
$stmt->execute(['%Assisted by: ' . $player['Name'] . '%']);
$assists = $stmt->fetch(PDO::FETCH_ASSOC)['AssistCount'];

// Generate description
$description = "Meet " . htmlspecialchars($player['Name']) . ", a dedicated player from " . htmlspecialchars($player['TeamName']) . ". ";

if (!empty($player['Height'])) {
    $description .= htmlspecialchars($player['Name']) . " stands tall at " . htmlspecialchars($player['Height']) . ". ";
}

if (!empty($player['Nationality'])) {
    $description .= htmlspecialchars($player['Name']) . " comes from " . htmlspecialchars($player['Nationality']) . ". ";
}

$description .= "Specializing in the " . htmlspecialchars($player['Position']) . " position, " . htmlspecialchars($player['Name']) . " has shown remarkable commitment and skill on the field. ";

if ($goals > 10) {
    $description .= "So far in his career with " . htmlspecialchars($player['TeamName']) . ", he has achieved " . $goals . " goals. ";
}

if ($assists > 10) {
    $description .= "Additionally, he has provided " . $assists . " assists for his team. ";
}

if ($trophyCount > 0) {
    $description .= "Throughout his career, " . htmlspecialchars($player['Name']) . " has earned " . $trophyCount . " prestigious trophies, including: ";
    foreach ($trophies as $trophy) {
        $description .= htmlspecialchars($trophy['Year']) . " - " . htmlspecialchars($trophy['Name']) . ". ";
    }
} else {
    $description .= htmlspecialchars($player['Name']) . " continues to strive for excellence and aims to add more accolades to his name.";
}

// Function to get image path
function getImagePath($image, $defaultImage) {
    return $image ? '../../uploads/' . $image : $defaultImage;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($player['Name']); ?> - Player Details</title>
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

        .player-image {
            text-align: center;
            margin-bottom: 20px;
        }

        .player-image img {
            width: 100%;
            max-width: 400px;
            height: auto;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .player-image img:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.5);
        }

        .player-details {
            background: linear-gradient(135deg, #4B0000, #388E3C);
            border-radius: 10px;
            padding: 30px;
            color: white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            animation: fadeIn 1s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .player-info h1 {
            margin-top: 0;
            font-size: 2.5rem;
            color: #FFD700;
        }

        .player-info p {
            font-size: 1.2rem;
            margin: 10px 0;
            color: #f0f0f0;
        }

        .player-info p strong {
            color: #fff;
        }

        .player-stats {
            margin-top: 30px;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            background-color: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 10px;
        }

        .stat-item {
            width: 30%;
            margin-bottom: 20px;
            background: rgba(0, 0, 0, 0.2);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            text-align: left;
        }

        .stat-item p {
            margin: 0;
            color: #fff;
            font-size: 1.1rem;
        }

        .stat-item p strong {
            color: #FFD700;
        }

        footer {
            background-color: #4B0000;
            color: white;
            text-align: center;
            padding: 10px 0;
            border-radius: 5px;
            width: 100%;
            box-sizing: border-box;
            margin-top: auto;
            position: relative;
            bottom: 0;
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
                <img src="https://cdn-icons-png.flaticon.com/512/54/54481.png" alt="Search Icon" class="search-icon">
                <img src="https://cdn-icons-png.flaticon.com/512/1077/1077114.png" alt="Profile Icon" class="profile-icon">
            </div>
        </div>
    </header>
    <div class="sidebar">
        <div class="team-info">
            <img src="<?php echo getImagePath($player['Logo'], 'default_logo.png'); ?>" alt="Team Logo">
            <h2><?php echo htmlspecialchars($player['TeamName']); ?></h2>
        </div>
        <ul>
        <ul>
            <li><a href="footballclub.php?team_id=<?php echo htmlspecialchars($team_id); ?>"> Team Overview</a></li>
            <li><a href="teamstories.php?team_id=<?php echo htmlspecialchars($team_id); ?>">Team Stories</a></li>
            <li><a href="teamstatistics.php?team_id=<?php echo htmlspecialchars($team['TeamID']); ?>">Team Stats</a></li>
            <li><a href="players.php?team_id=<?php echo htmlspecialchars($team_id); ?>">Players</a></li>
            <li><a href="upcoming_matches.php?team_id=<?php echo htmlspecialchars($team_id); ?>">Upcoming Matches</a></li>
            <li><a href="competitions.php?team_id=<?php echo htmlspecialchars($team_id); ?>">Upcoming Competitions</a></li>
            <li><a href="awards.php?team_id=<?php echo htmlspecialchars($team_id); ?>">Awards</a></li>
        </ul>
        </ul>
    </div>
    <div style = "margin-top:20px;" class="main-content">
        <h1 style="text-align:center; color: #4B0000;"><?php echo htmlspecialchars($player['Name']); ?></h1>
        <div class="player-image">
            <img src="<?php echo getImagePath($player['Image'], 'default_player_photo.jpg'); ?>" alt="<?php echo htmlspecialchars($player['Name']); ?>">
        </div>
        <section class="section player-details">
            <div class="player-info">
                <p><strong>Description:</strong> <?php echo $description; ?></p>
            </div>
            <div class="player-stats">
                <?php if (stripos($player['Position'], 'goalkeeper') !== false || stripos($player['Position'], 'keeper') !== false): ?>
                    <div class="stat-item">
                        <p><strong>Clean Sheets:</strong> <?php echo htmlspecialchars($cleanSheets); ?></p>
                    </div>
                <?php else: ?>
                    <div class="stat-item">
                        <p><strong>Goals:</strong> <?php echo htmlspecialchars($goals); ?></p>
                    </div>
                    <div class="stat-item">
                        <p><strong>Assists:</strong> <?php echo htmlspecialchars($assists); ?></p>
                    </div>
                <?php endif; ?>
               
                <div class="stat-item">
                    <p><strong>Age:</strong> <?php echo htmlspecialchars($player['Age']); ?></p>
                </div>
                <div class="stat-item">
                    <p><strong>Height:</strong> <?php echo htmlspecialchars($player['Height']); ?></p>
                </div>
                <div class="stat-item">
                    <p><strong>Nationality:</strong> <?php echo htmlspecialchars($player['Nationality']); ?></p>
                </div>
                <div class="stat-item">
                    <p><strong>Trophies:</strong> <?php echo htmlspecialchars($trophyCount); ?></p>
                </div>
            </div>
        </section>
    </div>
    <footer>
        <p>&copy; 2024 Ashesi Sports Insight. All rights reserved.</p>
    </footer>
</body>
</html>
