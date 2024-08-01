<?php
// seemore.php

// Example player data. In a real scenario, this should be fetched from a database.
$players = [
    "player1" => [
        "name" => "Player 1",
        "position" => "Forward",
        "description" => "Player 1 is a talented forward known for his speed and scoring ability.",
        "image" => "https://rawcdn.githack.com/naomikonlack/WEBTECHGITDEMO/f3860df6b8b5fb350f106739b94052947dc3ff85/WhatsApp%20Image%202024-07-25%20at%2021.51.24.jpeg",
        "matches_played" => 100,
        "goals" => 45,
        "assists" => 30,
        "trophies" => 5,
        "age" => 28,
        "height" => "6'1\"",
        "nationality" => "Country A",
    ],
    // Add more players as needed
];

$playerId = $_GET['player'] ?? null;

if ($playerId && isset($players[$playerId])) {
    $player = $players[$playerId];
} else {
    echo "Player not found.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($player['name']); ?> - Player Details</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f9;
            padding-top: 80px; /* Space for the fixed header */
            display: flex;
            flex-direction: column;
            min-height: 100vh; /* Minimum height to accommodate footer */
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
            margin-left: 220px;
            padding: 40px;
            flex: 1;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
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
            color: #FFD700; /* Gold color for highlight */
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
            color: #FFD700; /* Gold color for stats */
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
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
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
            transition: color 0.3s;
        }

        nav ul li a:hover {
            color: #88C057;
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
                    <li><a href="#">SPORTS</a>
                        <ul>
                            <li><a href="footballsport.php">Football</a></li>
                            <li><a href="basketballsport.php">Basketball</a></li>
                        </ul>
                    </li>
                    <li> <a href="homepage.php">HOME</a></li>
                    <li><a href="#">NEWS</a></li>
                    <li><a href="#">RANKINGS</a></li>
                    <li><a href="#">TEAMS & COACHES</a>
                        <ul>
                            <li><a href="#">Teams</a></li>
                            <li><a href="#">Coaches</a></li>
                        </ul>
                    </li>
                    <li><a href="#">PLAYER STATS</a>
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
                <img src="https://cdn-icons-png.flaticon.com/512/1077/1077114.png" alt="Profile Icon" class="profile-icon">
            </div>
        </div>
    </header>
    <div class="sidebar">
        <h2>Football</h2>
        <ul>
            <li><a href="#stats">Stats</a></li>
            <li><a href="#teams">Teams</a></li>
            <li><a href="#coaches">Coaches</a></li>
            <li><a href="club.php">Clubs</a></li>
            <li><a href="#players">Players</a></li>
            <li><a href="#competitions">Competitions</a></li>
        </ul>
    </div>
    <div class="main-content">
        <h1 style="text-align:center; color: #4B0000;;"><?php echo htmlspecialchars($player['name']); ?></h1>
        <div class="player-image">
            <img src="<?php echo htmlspecialchars($player['image']); ?>" alt="<?php echo htmlspecialchars($player['name']); ?>">
        </div>
        <section class="section player-details">
            <div class="player-info">
                <p><strong>Position:</strong> <?php echo htmlspecialchars($player['position']); ?></p>
                <p><strong>Description:</strong> <?php echo htmlspecialchars($player['description']); ?></p>
            </div>
            <div class="player-stats">
                <div class="stat-item">
                    <p><strong>Matches Played:</strong> <?php echo htmlspecialchars($player['matches_played']); ?></p>
                </div>
                <div class="stat-item">
                    <p><strong>Goals:</strong> <?php echo htmlspecialchars($player['goals']); ?></p>
                </div>
                <div class="stat-item">
                    <p><strong>Assists:</strong> <?php echo htmlspecialchars($player['assists']); ?></p>
                </div>
                <div class="stat-item">
                    <p><strong>Trophies:</strong> <?php echo htmlspecialchars($player['trophies']); ?></p>
                </div>
                <div class="stat-item">
                    <p><strong>Age:</strong> <?php echo htmlspecialchars($player['age']); ?></p>
                </div>
                <div class="stat-item">
                    <p><strong>Height:</strong> <?php echo htmlspecialchars($player['height']); ?></p>
                </div>
                <div class="stat-item">
                    <p><strong>Nationality:</strong> <?php echo htmlspecialchars($player['nationality']); ?></p>
                </div>
            </div>
        </section>
    </div>
    <footer>
        <p>&copy; 2024 Ashesi Sports Insight. All rights reserved.</p>
    </footer>
</body>
</html>
