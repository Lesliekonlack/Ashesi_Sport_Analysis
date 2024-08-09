<?php
session_start();
include '../../settings/connection.php'; // Include your database connection file

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if a story ID was provided
// Check if team_id and story_id are provided in the URL
if (!isset($_GET['team_id']) || empty($_GET['team_id']) || !isset($_GET['story_id']) || empty($_GET['story_id'])) {
    echo "Required parameters not provided!";
    exit();
}

$team_id = intval($_GET['team_id']);
$story_id = intval($_GET['story_id']);

// Fetch the story from the database
$sql = "SELECT Title, Content, ImagePath, DatePosted FROM stories WHERE StoryID = :story_id";
try {
    $stmt = $conn->prepare($sql);
    $stmt->execute(['story_id' => $story_id]);
    $story = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$story) {
        echo "Story not found!";
        exit();
    }
} catch (PDOException $e) {
    echo 'Query failed: ' . $e->getMessage();
    exit();
}

// Function to get image path
function getImagePath($image) {
    $defaultImage = '../../uploads/default_image.png'; // Ensure this file exists in the appropriate directory
    if ($image && file_exists(__DIR__ . '/../../' . $image)) {
        return '../../' . $image;
    }
    return $defaultImage;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($story['Title']); ?> - Ashesi Sports Insight</title>
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

        .story-content img {
            max-width: 100%;
    
            height: auto;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .story-content {
            margin-top: 20px;
        }

        .story-content h2 {
            font-size: 2rem;
            color: #4B0000;
            margin-bottom: 20px;
        }

        .story-content p {
            font-size: 1.1rem;
            line-height: 1.6;
            color: #333;
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
        <li><a href="team_stories.php?team_id=<?php echo htmlspecialchars($team_id); ?>">Team Stories</a></li>
        <li><a href="footballclub.php?team_id=<?php echo htmlspecialchars($team_id); ?>">Team Overview</a></li>
                <li><a href="teamstatistics.php?team_id=<?php echo htmlspecialchars($team_id); ?>">Team Stats</a></li>
            <li><a href="players.php?team_id=<?php echo htmlspecialchars($team_id); ?>">Players</a></li>
            <li><a href="upcoming_matches.php?team_id=<?php echo htmlspecialchars($team_id); ?>">Upcoming Matches</a></li>
            <li><a href="awards.php?team_id=<?php echo htmlspecialchars($team_id); ?>">Awards</a></li>
        </ul>
    </div>
    <div class="main-content">
        <section class="story-content">
            <h2><?php echo htmlspecialchars($story['Title']); ?></h2>
            <img src="<?php echo htmlspecialchars(getImagePath($story['ImagePath'])); ?>" alt="<?php echo htmlspecialchars($story['Title']); ?>">
            <p><strong>Posted on:</strong> <?php echo htmlspecialchars(date('F j, Y', strtotime($story['DatePosted']))); ?></p>
            <p><?php echo nl2br(htmlspecialchars($story['Content'])); ?></p>
        </section>
    </div>
    <footer>
        <div class="footer-container">
            <p>&copy; 2024 Ashesi Sports Insight. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>

</html>
