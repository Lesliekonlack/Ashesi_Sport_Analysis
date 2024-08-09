<?php
session_start();
include '../../settings/connection.php'; // Include your database connection file

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fetch all stories from the database
$allStories = [];
$sql = "SELECT StoryID, Title, ImagePath, DatePosted FROM stories ORDER BY DatePosted DESC";
try {
    $stmt = $conn->query($sql);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $allStories[] = $row;
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
    <title>All Stories - Ashesi Sports Insight</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Add styles similar to your previous pages */
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: white;
            padding-top: 80px;
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
            top: 80px;
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

        .stories-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            padding: 20px;
        }

        .story-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            padding: 20px;
            width: calc(33.333% - 40px);
            box-sizing: border-box;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            text-decoration: none;
        }

        .story-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .story-card img {
            width: 100%;
            height: auto;
            max-height: 200px;
            margin-bottom: 10px;
            object-fit: cover;
        }

        .story-card h3 {
            margin: 0;
            color: #4B0000;
            font-size: 1.2rem;
            text-decoration: underline;
        }

        .story-card p {
            color: #666;
            font-size: 0.9rem;
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
            margin-top: auto;
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
            <li><a href="homepage.php">HOME</a></li>
            <li><a href="basketballsport.php">Basketball Clubs</a></li>
        </ul>
    </div>
    <div class="main-content">
        <section id="all-stories" class="section">
            <h2>All Stories</h2>
            <div class="stories-container">
                <?php foreach ($allStories as $story): ?>
                    <a href="story_details.php?story_id=<?php echo htmlspecialchars($story['StoryID']); ?>" class="story-card">
                        <img src="<?php echo htmlspecialchars(getImagePath($story['ImagePath'])); ?>" alt="<?php echo htmlspecialchars($story['Title']); ?>">
                        <h3><?php echo htmlspecialchars($story['Title']); ?></h3>
                        <p>Posted on: <?php echo htmlspecialchars(date('F j, Y', strtotime($story['DatePosted']))); ?></p>
                    </a>
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
