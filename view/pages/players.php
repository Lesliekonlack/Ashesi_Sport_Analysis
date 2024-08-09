<?php
session_start();
include '../../settings/connection.php';

// Check if a team ID was provided in the URL
if (!isset($_GET['team_id']) || empty($_GET['team_id'])) {
    echo "Team ID not provided!";
    exit();
}

$team_id = intval($_GET['team_id']);

// Fetch the team details
$team_name = '';
try {
    $stmt = $conn->prepare("SELECT TeamName FROM teams WHERE TeamID = ?");
    $stmt->execute([$team_id]);
    $team = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($team) {
        $team_name = $team['TeamName'];
    } else {
        echo "Team not found!";
        exit();
    }
} catch (PDOException $e) {
    echo 'Error fetching team details: ' . $e->getMessage();
    exit();
}

// Fetch players associated with the team
$players = [];
try {
    $stmt = $conn->prepare("SELECT * FROM players WHERE TeamID = ? ORDER BY Name ASC");
    $stmt->execute([$team_id]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $players[] = $row;
    }
} catch (PDOException $e) {
    echo 'Error fetching players: ' . $e->getMessage();
    exit();
}

// Function to get player image path, ensuring it exists
function getPlayerImagePath($imagePath) {
    $defaultImagePath = '../../uploads/default_image.png'; // Replace with your default image path
    if ($imagePath && file_exists(__DIR__ . '/../../uploads/' . $imagePath)) {
        return '../../uploads/' . $imagePath;
    }
    return $defaultImagePath;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($team_name); ?> Players - Ashesi Sports Insight</title>
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
            padding: 40px;
            background-color: #f4f4f4;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .section {
            width: 100%;
            max-width: 1200px;
            margin-bottom: 40px;
        }

        .section h2 {
            color: #4B0000;
            font-size: 2.5rem;
            margin-bottom: 40px;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .players-container {
            display: flex;
            flex-wrap: wrap;
            gap: 40px;
            justify-content: center;
        }

        .player-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            width: 320px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            text-decoration: none;
            color: #333;
        }

        .player-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }

        .player-image {
            width: 100%;
            height: 300px;
            background: #f9f9f9;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        .player-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-bottom: 5px solid #4B0000;
        }

        .player-info {
            padding: 20px;
            width: 100%;
            color: #4B0000;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-transform: uppercase;
            font-weight: bold;
        }

        .player-info h3 {
            margin: 0;
            font-size: 1.5rem;
        }

        .player-info p {
            margin: 10px 0;
            font-size: 1rem;
            color: #666;
            text-transform: none;
        }

        footer {
            background-color: #4B0000;
            color: white;
            text-align: center;
            padding: 20px 0;
            position: fixed;
            bottom: 0;
            width: 100%;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 10px;
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
            <img src="https://via.placeholder.com/40" alt="Team Logo">
            <h2><?php echo htmlspecialchars($team_name); ?></h2>
        </div>
        <ul>
            <li><a href="footballclub.php?team_id=<?php echo htmlspecialchars($team_id); ?>">Team Overview</a></li>
            <li><a href="teamstatistics.php?team_id=<?php echo htmlspecialchars($team_id); ?>">Team Stats</a></li>
            <li><a href="players.php?team_id=<?php echo htmlspecialchars($team_id); ?>">Players</a></li>
            <li><a href="upcoming_matches.php?team_id=<?php echo htmlspecialchars($team_id); ?>">Upcoming Matches</a></li>
            <li><a href="competitions.php?team_id=<?php echo htmlspecialchars($team_id); ?>">Upcoming Competitions</a></li>
            <li><a href="awards.php?team_id=<?php echo htmlspecialchars($team_id); ?>">Awards</a></li>
            <li><a href="team_stories.php?team_id=<?php echo htmlspecialchars($team_id); ?>">Team Stories</a></li>
        </ul>
    </div>
    <div class="main-content">
        <section class="section">
            <h2>Players of <?php echo htmlspecialchars($team_name); ?></h2>
            <div class="players-container">
                <?php if (empty($players)): ?>
                    <p>No players available for this team.</p>
                <?php else: ?>
                    <?php foreach ($players as $player): ?>
                        <a href="seemore.php?player=<?php echo htmlspecialchars($player['PlayerID']); ?>&team_id=<?php echo htmlspecialchars($team_id); ?>" class="player-card">
                            <div class="player-image">
                                <img src="<?php echo htmlspecialchars(getPlayerImagePath($player['Image'])); ?>" alt="<?php echo htmlspecialchars($player['Name']); ?>">
                            </div>
                            <div class="player-info">
                                <h3><?php echo htmlspecialchars($player['Name']); ?></h3>
                                <p>Position: <?php echo htmlspecialchars($player['Position']); ?></p>
                                <p>Age: <?php echo htmlspecialchars($player['Age']); ?></p>
                                <p>Origin: <?php echo htmlspecialchars($player['Nationality']); ?></p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>
</body>
</html>
