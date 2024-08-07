<?php
session_start();
include '../../settings/connection.php'; // Include your database connection file

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fetch club ID from the URL parameter
$club_id = isset($_GET['club_id']) ? intval($_GET['club_id']) : 0;
if ($club_id <= 0) {
    die('Invalid club ID.');
}

// Fetch club details from the database
$club = [];
$sql = "SELECT t.*, c.Name AS CoachName
        FROM teams t
        LEFT JOIN coaches c ON t.CoachID = c.CoachID
        WHERE t.TeamID = ?";
try {
    $stmt = $conn->prepare($sql);
    $stmt->execute([$club_id]);
    $club = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$club) {
        die('Club not found.');
    }
} catch (PDOException $e) {
    echo 'Query failed: ' . $e->getMessage();
    exit();
}

// Fetch club members (players)
$members = [];
$sql = "SELECT p.* FROM players p WHERE p.TeamID = ?";
try {
    $stmt = $conn->prepare($sql);
    $stmt->execute([$club_id]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $members[] = $row;
    }
} catch (PDOException $e) {
    echo 'Query failed: ' . $e->getMessage();
    exit();
}

// Fetch matches involving the club
$matches = [];
$sql = "SELECT m.*, t1.TeamName AS Team1Name, t2.TeamName AS Team2Name
        FROM matches m
        JOIN teams t1 ON m.Team1ID = t1.TeamID
        JOIN teams t2 ON m.Team2ID = t2.TeamID
        WHERE m.Team1ID = ? OR m.Team2ID = ?";
try {
    $stmt = $conn->prepare($sql);
    $stmt->execute([$club_id, $club_id]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $matches[] = $row;
    }
} catch (PDOException $e) {
    echo 'Query failed: ' . $e->getMessage();
    exit();
}

// Fetch aggregated statistics for the club
$statistics = [];
$sql = "SELECT * FROM aggregatedstatistics WHERE TeamID = ?";
try {
    $stmt = $conn->prepare($sql);
    $stmt->execute([$club_id]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $statistics[] = $row;
    }
} catch (PDOException $e) {
    echo 'Query failed: ' . $e->getMessage();
    exit();
}

// Fetch accomplishments for the club
$accomplishments = [];
$sql = "SELECT * FROM bigeventawards WHERE TeamID = ?";
try {
    $stmt = $conn->prepare($sql);
    $stmt->execute([$club_id]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $accomplishments[] = $row;
    }
} catch (PDOException $e) {
    echo 'Query failed: ' . $e->getMessage();
    exit();
}

// Function to get logo path
function getLogoPath($logo) {
    $defaultLogo = 'default_logo.png'; // Ensure this file exists in the appropriate directory
    if ($logo && file_exists(__DIR__ . '/../../uploads/' . $logo)) {
        return '../../uploads/' . $logo;
    }
    return $defaultLogo;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($club['TeamName']); ?> - Ashesi Sports Insight</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: white;
            padding-top: 80px; /* Space for the fixed header */
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        .sidebar {
            width: 200px;
            background-color: #4B0000;
            padding: 20px;
            height: 100vh;
            position: fixed;
            top: 80px; /* Below the navbar */
            left: 0;
            overflow-y: auto;
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
        }

        .card-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            padding: 20px;
            width: calc(33.333% - 40px); /* Responsive card width */
            box-sizing: border-box;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .card img {
            width: 100px;
            height: 100px;
            margin-bottom: 10px;
            border-radius: 50%; /* Make the logo round */
        }

        .card h3 {
            margin: 0;
            color: #4B0000;
            font-size: 1.2rem; /* Smaller font size */
            text-align: center;
        }

        .card p {
            color: #666;
            font-size: 0.9rem; /* Smaller font size */
            text-align: center;
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

        .profile-name {
            font-size: 1rem;
            color: #4B0000;
            font-weight: bold;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: white;
            min-width: 160px;
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
            z-index: 1;
        }

        .profile-name:hover .dropdown-content {
            display: block;
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

        footer {
            background-color: #4B0000;
            color: white;
            text-align: center;
            padding: 10px 0;
            border-radius: 5px;
            width: 100%;
            box-sizing: border-box;
            margin-top: auto; /* Push footer to the bottom */
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
                    <li>
                        <a href="#">SPORTS</a>
                        <ul>
                            <li><a href="footballsport.php">Football</a></li>
                            <li><a href="basketballsport.php">Basketball</a></li>
                        </ul>
                    </li>
                    <li> <a href="homepage.php">HOME</a></li>
                    <li><a href="#">NEWS</a></li>
                    <li><a href="#">RANKINGS</a></li>
                    <li>
                        <a href="#">TEAMS & COACHES</a>
                        <ul>
                            <li><a href="#">Teams</a></li>
                            <li><a href="#">Coaches</a></li>
                        </ul>
                    </li>
                    <li>
                        <a href="#">PLAYER STATS</a>
                        <ul>
                            <li><a href="#">Statistics</a></li>
                            <li><a href="#">Accomplishments</a></li>
                        </ul>
                    </li>
                    <li><a href="#">UPCOMING EVENTS</a></li>
                </ul>
            </nav>
            <div class="nav-icons">
                <img src="https://cdn-icons-png.flaticon.com/512/54/54481.png" alt="Search Icon" class="search-icon">
                <?php if (isset($_SESSION['coach_name'])): ?>
                    <span class="profile-name"><?php echo htmlspecialchars($_SESSION['coach_name']); ?>
                        <div class="dropdown-content">
                            <a href="../../action/logout.php">Logout</a>
                        </div>
                    </span>
                <?php else: ?>
                    <img src="https://cdn-icons-png.flaticon.com/512/1077/1077114.png" alt="Profile Icon" class="profile-icon">
                <?php endif; ?>
            </div>
        </div>
    </header>
    <div class="sidebar">
        <h2>Football</h2>
        <ul>
            <li><a href="#stats">Stats</a></li>
            <li><a href="#teams">Teams</a></li>
            <li><a href="#coaches">Coaches</a></li>
            <li><a href="#clubs">Clubs</a></li>
            <li><a href="#players">Players</a></li>
            <li><a href="#competitions">Competitions</a></li>
        </ul>
    </div>
    <div class="main-content">
        <section id="club-details" class="section">
            <h2><?php echo htmlspecialchars($club['TeamName']); ?></h2>
            <img src="<?php echo htmlspecialchars(getLogoPath($club['Logo'])); ?>" alt="Club Logo" style="width:150px; height:150px; border-radius: 50%;">
            <p><strong>Coach:</strong> <?php echo htmlspecialchars($club['CoachName']); ?></p>
            <p><strong>Win/Loss Record:</strong> <?php echo htmlspecialchars($club['WinLossRecord']); ?></p>
            <p><strong>Accomplishments:</strong> <?php echo htmlspecialchars($club['Accomplishments']); ?></p>
        </section>

        <section id="members" class="section">
            <h2>Members</h2>
            <div class="card-container">
                <?php foreach ($members as $member): ?>
                    <div class="card">
                        <h3><?php echo htmlspecialchars($member['Name']); ?></h3>
                        <p><strong>Position:</strong> <?php echo htmlspecialchars($member['Position']); ?></p>
                        <p><strong>Injuries:</strong> <?php echo htmlspecialchars($member['Injuries']); ?></p>
                        <p><strong>Accomplishments:</strong> <?php echo htmlspecialchars($member['Accomplishments']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section id="matches" class="section">
            <h2>Matches</h2>
            <div class="card-container">
                <?php foreach ($matches as $match): ?>
                    <div class="card">
                        <h3><?php echo htmlspecialchars($match['Team1Name']); ?> vs <?php echo htmlspecialchars($match['Team2Name']); ?></h3>
                        <p><strong>Date:</strong> <?php echo htmlspecialchars($match['Date']); ?></p>
                        <p><strong>Score:</strong> <?php echo htmlspecialchars($match['ScoreTeam1'] . ' - ' . $match['ScoreTeam2']); ?></p>
                        <p><strong>Injuries Reported:</strong> <?php echo htmlspecialchars($match['InjuriesReported']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section id="statistics" class="section">
            <h2>Statistics</h2>
            <div class="card-container">
                <?php foreach ($statistics as $stat): ?>
                    <div class="card">
                        <h3><?php echo htmlspecialchars($stat['MetricName']); ?></h3>
                        <p><?php echo htmlspecialchars($stat['MetricValue']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section id="accomplishments" class="section">
            <h2>Accomplishments</h2>
            <div class="card-container">
                <?php foreach ($accomplishments as $accomplishment): ?>
                    <div class="card">
                        <h3><?php echo htmlspecialchars($accomplishment['AwardName']); ?></h3>
                        <p><?php echo htmlspecialchars($accomplishment['AwardDescription']); ?></p>
                        <p><strong>Date:</strong> <?php echo htmlspecialchars($accomplishment['AwardDate']); ?></p>
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
</body>
</html>
