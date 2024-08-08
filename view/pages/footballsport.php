<?php
session_start();
include '../../settings/connection.php'; // Include your database connection file

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fetch teams from the database
$teams = [];
$sql = "SELECT t.TeamID, t.TeamName, t.CoachID, t.TeamGender, t.Logo, c.Name AS CoachName 
        FROM teams t
        JOIN coaches c ON t.CoachID = c.CoachID
        WHERE t.SportID = (SELECT SportID FROM sports WHERE SportName = 'Football')";
try {
    $stmt = $conn->query($sql);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $teams[] = $row;
    }
} catch (PDOException $e) {
    echo 'Query failed: ' . $e->getMessage();
    exit();
}

// Check if user is logged in and is a coach
$is_coach = isset($_SESSION['coach_id']);
$user_team_id = $is_coach && isset($_SESSION['team_id']) ? $_SESSION['team_id'] : null;

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
    <title>Football - Ashesi Sports Insight</title>
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

        .toggle-buttons {
            margin-bottom: 20px;
        }

        .toggle-buttons button {
            padding: 10px 20px;
            margin-right: 10px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            color: white;
        }

        #male-button.active, #female-button.active {
            background-color: #4B0000;
        }

        #male-button, #female-button {
            background-color: #ccc;
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
            text-decoration: underline; /* Make club names underlined */
        }

        .card p {
            color: #666;
            font-size: 0.9rem; /* Smaller font size */
            text-align: center;
        }

        .actions {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
        }

        .upload-logo, .delete-logo {
            background-color: #1e90ff;
            color: white;
            border: none;
            padding: 8px 16px;
            cursor: pointer;
            border-radius: 5px;
            display: inline-block;
            width: 100%;
            text-align: center;
        }

        .delete-logo {
            background-color: #ff4b4b;
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
    <script>
        function toggleView(view) {
            document.getElementById('male-clubs').style.display = view === 'male' ? 'flex' : 'none';
            document.getElementById('female-clubs').style.display = view === 'female' ? 'flex' : 'none';

            document.getElementById('male-button').classList.toggle('active', view === 'male');
            document.getElementById('female-button').classList.toggle('active', view === 'female');
        }

        document.addEventListener('DOMContentLoaded', function() {
            const buttons = document.querySelectorAll('.toggle-buttons button');
            buttons.forEach(button => {
                button.addEventListener('click', function() {
                    buttons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                });
            });

            const uploadForms = document.querySelectorAll('.upload-logo-form');
            uploadForms.forEach(form => {
                form.addEventListener('submit', function(event) {
                    const fileInput = this.querySelector('input[type="file"]');
                    if (!fileInput.files.length) {
                        event.preventDefault();
                        alert('Please choose a file to upload.');
                    }
                });
            });
        });
    </script>
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
        <section id="welcome" class="section">
            <h2>Welcome to the Football Insight of Ashesi</h2>
            <p>Discover all the latest updates on your favorite football teams at Ashesi.</p>
        </section>

        <section id="clubs" class="section">
            <h2>Football Clubs at Ashesi</h2>
            <div class="toggle-buttons">
                <button id="male-button" onclick="toggleView('male')" class="active">Male</button>
                <button id="female-button" onclick="toggleView('female')">Female</button>
            </div>

            <div id="male-clubs" class="card-container">
                <?php foreach ($teams as $team): ?>
                    <?php if ($team['TeamGender'] === 'Male'): ?>
                        <div class="card">
                            <img src="<?php echo htmlspecialchars(getLogoPath($team['Logo'])); ?>" alt="Team Logo" style="width:100px; height:100px;">
                            <h3><a href="footballclub.php?team_id=<?php echo $team['TeamID']; ?>"><?php echo htmlspecialchars($team['TeamName']); ?></a></h3>
                            <p>Coached by <?php echo htmlspecialchars($team['CoachName']); ?></p>
                            <?php if ($is_coach && $user_team_id == $team['TeamID']): ?>
                                <div class="actions">
                                    <form class="upload-logo-form" action="../../action/upload_logo.php" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="team_id" value="<?php echo $team['TeamID']; ?>">
                                        <input type="file" name="team_logo">
                                        <button type="submit" class="upload-logo">Upload Logo</button>
                                    </form>
                                    <form action="../../action/delete_logo.php" method="POST">
                                        <input type="hidden" name="team_id" value="<?php echo $team['TeamID']; ?>">
                                        <button type="submit" class="delete-logo">Delete Logo</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <div id="female-clubs" class="card-container" style="display:none;">
                <?php foreach ($teams as $team): ?>
                    <?php if ($team['TeamGender'] === 'Female'): ?>
                        <div class="card">
                            <img src="<?php echo htmlspecialchars(getLogoPath($team['Logo'])); ?>" alt="Team Logo" style="width:100px; height:100px;">
                            <h3><a href="footballclub.php?team_id=<?php echo $team['TeamID']; ?>"><?php echo htmlspecialchars($team['TeamName']); ?></a></h3>
                            <p>Coached by <?php echo htmlspecialchars($team['CoachName']); ?></p>
                            <?php if ($is_coach && $user_team_id == $team['TeamID']): ?>
                                <div class="actions">
                                    <form class="upload-logo-form" action="../../action/upload_logo.php" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="team_id" value="<?php echo $team['TeamID']; ?>">
                                        <input type="file" name="team_logo">
                                        <button type="submit" class="upload-logo">Upload Logo</button>
                                    </form>
                                    <form action="../../action/delete_logo.php" method="POST">
                                        <input type="hidden" name="team_id" value="<?php echo $team['TeamID']; ?>">
                                        <button type="submit" class="delete-logo">Delete Logo</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
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
