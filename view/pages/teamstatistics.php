<?php
session_start();
include '../../settings/connection.php'; // Include your database connection file

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Get the team ID from the URL parameter
$team_id = isset($_GET['team_id']) ? intval($_GET['team_id']) : null;

// Fetch team details
$team_info = [];
if ($team_id) {
    $sql = "SELECT TeamName, Logo FROM teams WHERE TeamID = ?";
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute([$team_id]);
        $team_info = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo 'Query failed: ' . $e->getMessage();
        exit();
    }
}

// Fetch general performance metrics
$metrics = [];
$total_matches = 0;
if ($team_id) {
    $metrics_sql = "SELECT 
                    COUNT(DISTINCT m.MatchID) as total_matches,
                    SUM(CASE WHEN e.EventType = 'Goal' THEN 1 ELSE 0 END) as total_goals,
                    SUM(CASE WHEN e.EventType = 'Assist' THEN 1 ELSE 0 END) as total_assists,
                    SUM(CASE WHEN e.EventType = 'CleanSheet' THEN 1 ELSE 0 END) as total_clean_sheets
                    FROM matches m
                    LEFT JOIN match_events e ON m.MatchID = e.MatchID
                    WHERE (m.Team1ID = ? OR m.Team2ID = ?) AND m.HasEnded = 1";
    try {
        $stmt = $conn->prepare($metrics_sql);
        $stmt->execute([$team_id, $team_id]);
        $metrics = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_matches = $metrics['total_matches'];
    } catch (PDOException $e) {
        echo 'Query failed: ' . $e->getMessage();
        exit();
    }
}

// Fetch recent form
$recent_matches = [];
if ($team_id) {
    $recent_matches_sql = "SELECT m.MatchID, m.Date, m.Team1ID, m.Team2ID, 
                           (CASE 
                                WHEN m.Team1ID = ? AND m.ScoreTeam1 > m.ScoreTeam2 THEN 'Win'
                                WHEN m.Team2ID = ? AND m.ScoreTeam2 > m.ScoreTeam1 THEN 'Win'
                                WHEN m.ScoreTeam1 = m.ScoreTeam2 THEN 'Draw'
                                ELSE 'Loss'
                            END) as result,
                           (CASE 
                                WHEN m.Team1ID = ? THEN t2.TeamName
                                WHEN m.Team2ID = ? THEN t1.TeamName
                            END) as opponent
                           FROM matches m
                           JOIN teams t1 ON m.Team1ID = t1.TeamID
                           JOIN teams t2 ON m.Team2ID = t2.TeamID
                           WHERE (m.Team1ID = ? OR m.Team2ID = ?) AND m.HasEnded = 1
                           ORDER BY m.Date DESC LIMIT 10";
    try {
        $stmt = $conn->prepare($recent_matches_sql);
        $stmt->execute([$team_id, $team_id, $team_id, $team_id, $team_id, $team_id]);
        $recent_matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo 'Query failed: ' . $e->getMessage();
        exit();
    }
}

// Fetch top scorers
$top_scorers = [];
if ($team_id) {
    $top_scorers_sql = "SELECT p.Name, COUNT(e.EventID) as goals
                        FROM players p
                        JOIN match_events e ON p.PlayerID = e.PlayerID
                        WHERE e.TeamID = ? AND e.EventType = 'Goal'
                        GROUP BY p.PlayerID
                        ORDER BY goals DESC
                        LIMIT 3";
    try {
        $stmt = $conn->prepare($top_scorers_sql);
        $stmt->execute([$team_id]);
        $top_scorers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo 'Query failed: ' . $e->getMessage();
        exit();
    }
}

// Fetch top assisters
$top_assisters = [];
if ($team_id) {
    $top_assisters_sql = "SELECT p.Name, COUNT(e.EventID) as assists
                          FROM players p
                          JOIN match_events e ON p.PlayerID = e.PlayerID
                          WHERE e.TeamID = ? AND e.EventType = 'Assist'
                          GROUP BY p.PlayerID
                          ORDER BY assists DESC
                          LIMIT 3";
    try {
        $stmt = $conn->prepare($top_assisters_sql);
        $stmt->execute([$team_id]);
        $top_assisters = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo 'Query failed: ' . $e->getMessage();
        exit();
    }
}

function getImagePath($image, $defaultImage) {
    return $image ? '../../uploads/' . $image : $defaultImage;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Team Statistics - Ashesi Sports Insight</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Styles for the navbar and sidebar (unchanged) */
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

        /* Main content styles */
        .main-content {
            margin-left: 280px;
            padding: 40px;
            flex: 1;
            background-color: #f4f7f6;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .section {
            margin-bottom: 40px;
        }

        .section h2 {
            color: #4B0000;
            font-size: 2.5rem;
            margin-bottom: 20px;
            text-align: center;
            text-transform: uppercase;
        }

        .card {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .card h3 {
            color: #4B0000;
            font-size: 1.8rem;
            margin-bottom: 15px;
            border-bottom: 2px solid #88C057;
            padding-bottom: 10px;
        }

        .card p {
            color: #333;
            font-size: 1rem;
            margin-bottom: 10px;
        }

        .chart-container {
            position: relative;
            height: 200px;
            margin-top: 20px;
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
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo-container">
                <img src="https://rawcdn.githack.com/naomikonlack/WEBTECHGITDEMO/246c29d2a7c8bff15a8f6206d9f7084c6018fa5a/Untitled_Artwork%204.png" alt="Ashesi Sports Insight Logo" class="logo">
                <div class="site-title">Ashesi Sports Insight</div>
            </div>
            
            <div class="nav-icons">
                <?php if (isset($_SESSION['coach_id'])): ?>
                    <div class="dropdown">
                        <button class="dropbtn">Welcome, <?php echo htmlspecialchars($_SESSION['coach_name']); ?></button>
                        <div class="dropdown-content">
                            <a href="../../action/logout.php">Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <img src="https://cdn-icons-png.flaticon.com/512/1077/1077114.png" alt="Profile Icon" class="profile-icon">
                <?php endif; ?>
            </div>
        </div>
    </header>
    <div class="sidebar">
        <div class="team-info">
            <img src="<?php echo getImagePath($team_info['Logo'], '../../uploads/default_logo.png'); ?>" alt="Team Logo">
            <h2><?php echo htmlspecialchars($team_info['TeamName']); ?></h2>
        </div>
        <ul>
            <li><a href="footballclub.php?team_id=<?php echo htmlspecialchars($team_id); ?>">Team Overview</a></li>
            <li><a href="team_stories.php?team_id=<?php echo htmlspecialchars($team_id); ?>">Team Stories</a></li>
            <li><a href="players.php?team_id=<?php echo htmlspecialchars($team_id); ?>">Players</a></li>
            <li><a href="upcoming_matches.php?team_id=<?php echo htmlspecialchars($team_id); ?>">Upcoming Matches</a></li>
            <li><a href="competitions.php?team_id=<?php echo htmlspecialchars($team_id); ?>">Upcoming Competitions</a></li>
            <li><a href="awards.php?team_id=<?php echo htmlspecialchars($team_id); ?>">Awards</a></li>
        </ul>
    </div>
    <div class="main-content">
        <section id="team-stats" class="section">
        <h2><?php echo htmlspecialchars($team_info['TeamName']); ?> Statistics</h2>
            <div class="card">
                <h3>General Performance Metrics</h3>
                <p>Total Matches: <?php echo htmlspecialchars($total_matches); ?></p>
                <p>Total Goals: <?php echo htmlspecialchars($metrics['total_goals']); ?></p>
                <p>Total Assists: <?php echo htmlspecialchars($metrics['total_assists']); ?></p>
                <p>Total Clean Sheets: <?php echo htmlspecialchars($metrics['total_clean_sheets']); ?></p>
                <div class="chart-container">
                    <canvas id="metricsChart"></canvas>
                </div>
            </div>
            <div class="card">
                <h3>Recent Form</h3>
                <?php foreach ($recent_matches as $match): ?>
                    <p><?php echo htmlspecialchars($team_info['TeamName']); ?> vs <?php echo htmlspecialchars($match['opponent']); ?> - Result: <?php echo htmlspecialchars($match['result']); ?></p>
                <?php endforeach; ?>
                <div class="chart-container">
                    <canvas id="recentFormChart"></canvas>
                </div>
            </div>
            <div class="card">
                <h3>Top Scorers</h3>
                <?php foreach ($top_scorers as $scorer): ?>
                    <p><?php echo htmlspecialchars($scorer['Name']); ?> - Goals: <?php echo htmlspecialchars($scorer['goals']); ?></p>
                <?php endforeach; ?>
                <div class="chart-container">
                    <canvas id="topScorersChart"></canvas>
                </div>
            </div>
            <div class="card">
                <h3>Top Assisters</h3>
                <?php foreach ($top_assisters as $assister): ?>
                    <p><?php echo htmlspecialchars($assister['Name']); ?> - Assists: <?php echo htmlspecialchars($assister['assists']); ?></p>
                <?php endforeach; ?>
                <div class="chart-container">
                    <canvas id="topAssistersChart"></canvas>
                </div>
            </div>
        </section>
    </div>

    <footer>
        <div class="footer-container">
            <p>&copy; 2024 Ashesi Sports Insight. All rights reserved.</p>
        </div>
    </footer>

    <script>
        const metricsData = {
            labels: ['Total Matches', 'Total Goals', 'Total Assists', 'Total Clean Sheets'],
            datasets: [{
                label: 'Metrics',
                data: [<?php echo htmlspecialchars($total_matches); ?>, <?php echo htmlspecialchars($metrics['total_goals']); ?>, <?php echo htmlspecialchars($metrics['total_assists']); ?>, <?php echo htmlspecialchars($metrics['total_clean_sheets']); ?>],
                backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0'],
                borderWidth: 1
            }]
        };

        const metricsConfig = {
            type: 'bar',
            data: metricsData,
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        };

        const recentFormData = {
            labels: <?php echo json_encode(array_column($recent_matches, 'opponent')); ?>,
            datasets: [{
                label: 'Recent Form',
                data: <?php echo json_encode(array_column($recent_matches, 'result')); ?>,
                backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0'],
                borderWidth: 1
            }]
        };

        const recentFormConfig = {
            type: 'line',
            data: recentFormData,
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        };

        const topScorersData = {
            labels: <?php echo json_encode(array_column($top_scorers, 'Name')); ?>,
            datasets: [{
                label: 'Top Scorers',
                data: <?php echo json_encode(array_column($top_scorers, 'goals')); ?>,
                backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0'],
                borderWidth: 1
            }]
        };

        const topScorersConfig = {
            type: 'bar',
            data: topScorersData,
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        };

        const topAssistersData = {
            labels: <?php echo json_encode(array_column($top_assisters, 'Name')); ?>,
            datasets: [{
                label: 'Top Assisters',
                data: <?php echo json_encode(array_column($top_assisters, 'assists')); ?>,
                backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0'],
                borderWidth: 1
            }]
        };

        const topAssistersConfig = {
            type: 'bar',
            data: topAssistersData,
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        };

        window.onload = function() {
            const metricsCtx = document.getElementById('metricsChart').getContext('2d');
            new Chart(metricsCtx, metricsConfig);

            const recentFormCtx = document.getElementById('recentFormChart').getContext('2d');
            new Chart(recentFormCtx, recentFormConfig);

            const topScorersCtx = document.getElementById('topScorersChart').getContext('2d');
            new Chart(topScorersCtx, topScorersConfig);

            const topAssistersCtx = document.getElementById('topAssistersChart').getContext('2d');
            new Chart(topAssistersCtx, topAssistersConfig);
        };
    </script>
</body>
<<<<<<< HEAD
</html>
=======
</html>

>>>>>>> c8fd726d7e6dfad5504611f15ad14b8a7dd45e9c
