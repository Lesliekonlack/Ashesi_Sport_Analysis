<?php
session_start();
include '../../settings/connection.php'; // Include your database connection file

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fetch team details
$team = [];
$team_id = isset($_GET['team_id']) ? (int)$_GET['team_id'] : null;

if ($team_id) {
    $sql = "SELECT t.*, c.Name AS CoachName, c.CoachImage 
            FROM teams t 
            LEFT JOIN coaches c ON t.CoachID = c.CoachID 
            WHERE t.TeamID = ?";
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute([$team_id]);
        $team = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo 'Query failed: ' . $e->getMessage();
        exit();
    }
}

// Fetch players for the team
$players = [];
if ($team_id) {
    $sql = "SELECT * FROM players WHERE TeamID = ?";
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute([$team_id]);
        $players = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo 'Query failed: ' . $e->getMessage();
        exit();
    }
}

// Check if user is logged in and is a coach
$is_coach = isset($_SESSION['coach_id']);
$user_team_id = $is_coach && isset($_SESSION['team_id']) ? $_SESSION['team_id'] : null;

// Handle file uploads and deletions
function handleFileUpload($fieldName, $uploadDir) {
    if (isset($_FILES[$fieldName]) && $_FILES[$fieldName]['error'] == UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES[$fieldName]['tmp_name'];
        $fileName = $_FILES[$fieldName]['name'];
        $fileSize = $_FILES[$fieldName]['size'];
        $fileType = $_FILES[$fieldName]['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
        $uploadFileDir = $uploadDir;
        $dest_path = $uploadFileDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            return $newFileName;
        }
    }
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = [];

    // Add new player
    if (isset($_POST['new_player_name']) && $team_id == $user_team_id) {
        $player_name = $_POST['new_player_name'];
        $player_position = $_POST['new_player_position'];
        $player_age = $_POST['new_player_age'];
        $player_height = $_POST['new_player_height'];
        $player_nationality = $_POST['new_player_nationality'];
        $uploadedFileName = handleFileUpload('new_player_image', '../../uploads/');

        $sql = "INSERT INTO players (Name, Position, Age, Height, Nationality, Image, TeamID, SportID) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        try {
            $stmt = $conn->prepare($sql);
            $stmt->execute([$player_name, $player_position, $player_age, $player_height, $player_nationality, $uploadedFileName, $team_id, $team['SportID']]);

            $player_id = $conn->lastInsertId();
            if (isset($_POST['new_player_trophies']) && is_array($_POST['new_player_trophies'])) {
                foreach ($_POST['new_player_trophies'] as $trophy) {
                    if (!empty($trophy)) {
                        list($year, $name) = explode('-', $trophy, 2);
                        $sql = "INSERT INTO trophies (PlayerID, Year, Name) VALUES (?, ?, ?)";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([$player_id, $year, $name]);
                    }
                }
            }

            $response['status'] = 'success';
            $response['message'] = 'Player added successfully.';
            $response['player_id'] = $player_id;
            $response['player_name'] = $player_name;
            $response['player_position'] = $player_position;
            $response['player_image'] = $uploadedFileName;
        } catch (PDOException $e) {
            $response['status'] = 'error';
            $response['message'] = 'Insert failed: ' . $e->getMessage();
        }

        echo json_encode($response);
        exit();
    }

    // Change player picture
    if (isset($_POST['change_player_id']) && $team_id == $user_team_id) {
        $player_id = (int)$_POST['change_player_id'];
        $uploadedFileName = handleFileUpload('change_player_image', '../../uploads/');
        if ($uploadedFileName) {
            $sql = "UPDATE players SET Image = ? WHERE PlayerID = ?";
            try {
                $stmt = $conn->prepare($sql);
                $stmt->execute([$uploadedFileName, $player_id]);
                $response['status'] = 'success';
                $response['message'] = 'Player picture updated successfully.';
                $response['image'] = $uploadedFileName;
            } catch (PDOException $e) {
                $response['status'] = 'error';
                $response['message'] = 'Update failed: ' . $e->getMessage();
            }
        } else {
            $response['status'] = 'error';
            $response['message'] = 'File upload failed.';
        }

        echo json_encode($response);
        exit();
    }

    // Delete player
    if (isset($_POST['delete_player_id']) && $team_id == $user_team_id) {
        $player_id = (int)$_POST['delete_player_id'];
        $sql = "DELETE FROM players WHERE PlayerID = ?";
        try {
            $stmt = $conn->prepare($sql);
            $stmt->execute([$player_id]);
            $response['status'] = 'success';
            $response['message'] = 'Player deleted successfully.';
            $response['player_id'] = $player_id;
        } catch (PDOException $e) {
            $response['status'] = 'error';
            $response['message'] = 'Delete failed: ' . $e->getMessage();
        }

        echo json_encode($response);
        exit();
    }

    // Handle other uploads and deletions
    if (isset($_FILES['team_image']) && $team_id == $user_team_id) {
        $uploadedFileName = handleFileUpload('team_image', '../../uploads/');
        if ($uploadedFileName) {
            $sql = "UPDATE teams SET TeamPhoto = ? WHERE TeamID = ?";
            try {
                $stmt = $conn->prepare($sql);
                $stmt->execute([$uploadedFileName, $team_id]);
                $response['status'] = 'success';
                $response['message'] = 'Team photo updated successfully.';
                $response['image'] = $uploadedFileName;
            } catch (PDOException $e) {
                $response['status'] = 'error';
                $response['message'] = 'Update failed: ' . $e->getMessage();
            }
        } else {
            $response['status'] = 'error';
            $response['message'] = 'File upload failed.';
        }

        echo json_encode($response);
        exit();
    }

    if (isset($_POST['delete_team_image']) && $team_id == $user_team_id) {
        $sql = "UPDATE teams SET TeamPhoto = NULL WHERE TeamID = ?";
        try {
            $stmt = $conn->prepare($sql);
            $stmt->execute([$team_id]);
            $response['status'] = 'success';
            $response['message'] = 'Team photo deleted successfully.';
        } catch (PDOException $e) {
            $response['status'] = 'error';
            $response['message'] = 'Update failed: ' . $e->getMessage();
        }

        echo json_encode($response);
        exit();
    }

    if (isset($_FILES['coach_image']) && $team_id == $user_team_id) {
        $uploadedFileName = handleFileUpload('coach_image', '../../uploads/');
        if ($uploadedFileName) {
            $sql = "UPDATE coaches SET CoachImage = ? WHERE CoachID = ?";
            try {
                $stmt = $conn->prepare($sql);
                $stmt->execute([$uploadedFileName, $team['CoachID']]);
                $response['status'] = 'success';
                $response['message'] = 'Coach photo updated successfully.';
                $response['image'] = $uploadedFileName;
            } catch (PDOException $e) {
                $response['status'] = 'error';
                $response['message'] = 'Update failed: ' . $e->getMessage();
            }
        } else {
            $response['status'] = 'error';
            $response['message'] = 'File upload failed.';
        }

        echo json_encode($response);
        exit();
    }

    if (isset($_POST['delete_coach_image']) && $team_id == $user_team_id) {
        $sql = "UPDATE coaches SET CoachImage = NULL WHERE CoachID = ?";
        try {
            $stmt = $conn->prepare($sql);
            $stmt->execute([$team['CoachID']]);
            $response['status'] = 'success';
            $response['message'] = 'Coach photo deleted successfully.';
        } catch (PDOException $e) {
            $response['status'] = 'error';
            $response['message'] = 'Update failed: ' . $e->getMessage();
        }

        echo json_encode($response);
        exit();
    }
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
    <title>Club Details - Ashesi Sports Insight</title>
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

        .team-photo {
            background-color: #4B0000;
            background-image: url('https://www.transparenttextures.com/patterns/asfalt-dark.png'), linear-gradient(90deg, white 2px, transparent 2px), linear-gradient(white 2px, transparent 2px);
            background-size: cover, 50px 50px, 50px 50px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 40px;
            padding: 20px;
            display: inline-block;
            max-width: 58%;
            margin-left: 260px;
        }

        .team-photo img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
        }

        .animation-container {
            position: relative;
            width: 98%;
            margin-left: -10px;
            height: 145vh;
            overflow: visible; /* Ensure content is not clipped */
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #88C057;
            background-image: url('https://www.transparenttextures.com/patterns/asfalt-dark.png'), linear-gradient(90deg, white 2px, transparent 2px), linear-gradient(white 2px, transparent 2px);
            background-size: cover, 50px 50px, 50px 50px;
        }

        
        .central-logo {
            position: absolute;
            width: 200px;
            height: 200px;
            z-index: 10;
            text-align: center;
            font-size: 1.5rem;
            color: #4B0000;
        }

        .orbiting-element {
            position: absolute;
            width: 310px;
            height: 280px;
            overflow: visible; /* Ensure content is not clipped */
            transform-origin: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            text-align: center;
            opacity: 0;
            transition: opacity 0.5s;
        }

        .orbiting-element img {
            width: 50%;
            height: auto;
            border-radius: 50%;
            margin-bottom: 10px;
            z-index: 2;
            position: relative;
        }

        .orbiting-element p.bottom {
            margin: 0;
            font-size: 1.5rem;
            color: #4B0000;
            text-align: center;
            background-color: rgba(75, 5, 0, 0.3);
            padding: 5px;
            border-radius: 5px;
            width: 50%;
            position: absolute;
            z-index: 1;
        }

        .orbiting-element p.top {
            margin: 0;
            font-size: 1.5rem;
            color: #4B0000;
            text-align: center;
            background-color: rgba(75, 5, 0, 0.3);
            padding: 5px;
            border-radius: 5px;
            width: 50%;
            position: absolute;
            z-index: 1;
        }

        .orbiting-element p.bottom {
            top: 68%;
        }

        .orbiting-element p.top {
            top: -100px; /* Adjust this value to move the text further up */
        }

        .orbiting-element img:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(255, 255, 0, 0.6), 0 10px 20px rgba(255, 255, 255, 0.5);
            filter: brightness(1.2);
        }

        .orbiting-element.player4 p.top,
        .orbiting-element.player7 p.top,
        .orbiting-element.player15 p.top,
        .orbiting-element.player18 p.top {
            top: -60px; /* Ensure the text is visible above the images */
        }

        .outer {
            transform-origin: 500px center;
        }

        .arrow {
            position: absolute;
            top: 50%;
            z-index: 100;
            cursor: pointer;
            font-size: 2rem;
            color: white;
            padding: 10px;
            background-color: rgba(0, 0, 0, 0.5);
            border-radius: 50%;
        }

        .arrow.left {
            left: 10px;
        }

        .arrow.right {
            right: 10px;
        }

        .card-container1 {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 40px;
        }

        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            padding: 20px;
            margin-bottom: 20px;
            width: 200px;
            text-align: center;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .card img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-bottom: 10px;
        }

        .card h3 {
            margin: 0;
            color: #4B0000;
            font-size: 1.2rem;
        }

        .card p {
            color: #666;
            font-size: 0.9rem;
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

        /* Modal Styles */
        .modal {
            display: none; /* Hidden by default */
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5); /* Black background with opacity */
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            border-radius: 10px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .player-info {
            text-align: center;
        }

        .player-info h2 {
            color: #4B0000;
            font-size: 2rem;
        }

        .player-info p {
            font-size: 1.2rem;
        }

        .see-more {
            color: #4B0000;
            font-weight: bold;
            cursor: pointer;
            text-decoration: underline;
        }

        .form-container {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }

        .form-container input[type="file"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
        }

        .form-container button {
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            color: white;
            background-color: #4B0000;
            transition: background-color 0.3s ease;
        }

        .form-container button:hover {
            background-color: #333;
        }
        /* Dropdown styling similar to footballsport.php */
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
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo-container">
                <img src="https://rawcdn.githack.com/naomikonlack/WEBTECHGITDEMO/246c29d2a7c8bff15a8f6206d9f7084c6018fa5a/Untitled_Artwork%204.png" alt="Ashesi Sports Insight Logo" class="logo">
                <div class="site-title">Ashesi Sports Insight</div>
                <nav>
                <ul>
                    <li>
                        <a style="margin-left: 820px;"href="footballsport.php">Go Back To View All Clubs</a>
                       
                    </li>
                   
                    <li><a href="homepage.php">HOME</a></li>  
                </nav>
            </div>
            <div class="nav-icons">
                <?php if (isset($_SESSION['coach_id'])): ?>
                    <?php if ($is_coach && $user_team_id == $team_id): ?>
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
            <img src="<?php echo getImagePath($team['Logo'] ?? null, 'default_logo.png'); ?>" alt="Team Logo">
            <h2><?php echo htmlspecialchars($team['TeamName'] ?? 'Football'); ?></h2>
        </div>
        <ul>
            <li><a href="team_stories.php?team_id=<?php echo htmlspecialchars($team_id); ?>">Team Stories</a></li>
            <li><a href="teamstatistics.php?team_id=<?php echo htmlspecialchars($team['TeamID']); ?>">Team Stats</a></li>
            <li><a href="players.php?team_id=<?php echo htmlspecialchars($team_id); ?>">Players</a></li>
            <li><a href="upcoming_matches.php?team_id=<?php echo htmlspecialchars($team_id); ?>">Upcoming Matches</a></li>
            <li><a href="awards.php?team_id=<?php echo htmlspecialchars($team_id); ?>">Awards</a></li>
        </ul>
    </div>
    <div class="main-content">
        <section id="club-info" class="section">
            <h2><?php echo htmlspecialchars($team['TeamName'] ?? 'Club Name'); ?></h2>
            <div class="team-photo">
                <img src="<?php echo getImagePath($team['TeamPhoto'] ?? null, 'default_team_photo.jpg'); ?>" alt="Team Photo" id="teamPhoto">
            </div>
            <?php if ($is_coach && $team && $team['TeamID'] == $user_team_id): ?>
                <div class="form-container">
                    <form id="teamPhotoForm" method="post" enctype="multipart/form-data">
                        <input type="file" name="team_image" id="teamImageInput">
                        <button type="submit">Upload Team Photo</button>
                        <button type="button" id="deleteTeamPhotoButton">Delete Team Photo</button>
                    </form>
                </div>
            <?php endif; ?>
            <h2>Players</h2>
            <div class="animation-container">
                <?php if (count($players) > 11): ?>
                    <div class="arrow left" onclick="showPreviousSet()">&#8592;</div>
                <?php endif; ?>
                <div class="central-logo">
                    <p><?php echo htmlspecialchars($team['TeamName'] ?? 'Team Name'); ?></p>
                </div>

                <?php
                foreach ($players as $index => $player):
                    $set_class = $index < 11 ? 'set1' : 'set2';
                    $circle_class = $index < 4 || ($index >= 11 && $index < 15) ? '' : 'outer';
                    $position_class = in_array($index, [3, 6, 14, 17]) ? 'top' : 'bottom';
                    ?>
                    <div class="orbiting-element <?php echo $set_class . ' ' . $circle_class; ?>" data-player-id="<?php echo htmlspecialchars($player['PlayerID']); ?>" style="--i: <?php echo $index % 11; ?>;">
                        <img src="<?php echo getImagePath($player['Image'] ?? null, 'placeholder.png'); ?>" alt="<?php echo htmlspecialchars($player['Name']); ?>">
                        <p class="<?php echo $position_class; ?>"><?php echo htmlspecialchars($player['Name']); ?><br><?php echo htmlspecialchars($player['Position']); ?></p>
                    </div>
                <?php endforeach; ?>

                <?php if (count($players) > 11): ?>
                    <div class="arrow right" onclick="showNextSet()">&#8594;</div>
                <?php endif; ?>
            </div>

            <?php if ($is_coach && $team && $team['TeamID'] == $user_team_id): ?>
                <div class="form-container">
                    <button onclick="openAddPlayerModal()">Add Player</button>
                    <form id="deletePlayerForm" method="post">
                        <select name="delete_player_id" id="playerSelect">
                            <?php foreach ($players as $player): ?>
                                <option value="<?php echo htmlspecialchars($player['PlayerID']); ?>"><?php echo htmlspecialchars($player['Name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" id="deletePlayerButton">Delete Player</button>
                    </form>
                    <form id="changePlayerPictureForm" method="post" enctype="multipart/form-data">
                        <select name="change_player_id" id="changePlayerSelect">
                            <?php foreach ($players as $player): ?>
                                <option value="<?php echo htmlspecialchars($player['PlayerID']); ?>"><?php echo htmlspecialchars($player['Name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="file" name="change_player_image" required>
                        <button type="submit">Change Player Picture</button>
                    </form>
                </div>
            <?php endif; ?>

            <h2>Coach</h2>
            <div class="card-container1">
                <div class="card">
                    <img src="<?php echo getImagePath($team['CoachImage'] ?? null, 'default_coach_photo.png'); ?>" alt="Coach Photo" id="coachPhoto">
                    <h3><?php echo htmlspecialchars($team['CoachName'] ?? 'Coach Name'); ?></h3>
                    <p>Coach</p>
                </div>
            </div>

            <?php if ($is_coach && $team && $team['TeamID'] == $user_team_id): ?>
                <div class="form-container">
                    <form id="coachPhotoForm" method="post" enctype="multipart/form-data">
                        <input type="file" name="coach_image" id="coachImageInput">
                        <button type="submit">Upload Coach Photo</button>
                        <button type="button" id="deleteCoachPhotoButton">Delete Coach Photo</button>
                    </form>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <!-- Modal for player details -->
    <div id="playerModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div class="player-info">
                <img id="modalPlayerImage" src="" alt="Player Image" style="width: 100%; max-width: 200px; margin-bottom: 15px; border-radius: 50%;">
                <h2 id="modalPlayerName">Player Name</h2>
                <p id="modalPlayerPosition">Position</p>
                <p id="modalPlayerDescription">Detailed description of the player, stats, history, etc.</p>
                <a id="modalSeeMore" class="see-more" href="#" target="_blank">See More</a>
            </div>
        </div>
    </div>

    <!-- Modal for adding a player -->
    <div id="addPlayerModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeAddPlayerModal()">&times;</span>
            <div class="player-info">
                <h2>Add New Player</h2>
                <form id="addPlayerForm" method="post" enctype="multipart/form-data">
                    <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                        <input type="text" name="new_player_name" placeholder="Player Name" required style="flex: 1; min-width: 200px;">
                        <input type="text" name="new_player_position" placeholder="Player Position" required style="flex: 1; min-width: 200px;">
                        <input type="number" name="new_player_age" placeholder="Player Age" required style="flex: 1; min-width: 100px;">
                        <input type="text" name="new_player_height" placeholder="Player Height" required style="flex: 1; min-width: 100px;">
                        <input type="text" name="new_player_nationality" placeholder="Player Country Of Origin" required style="flex: 1; min-width: 200px;">
                    </div>
                    <h3 style="margin-top: 20px;">Trophies</h3>
                    <div id="trophiesContainer">
                        <input type="text" name="new_player_trophies[]" placeholder="Trophies (format: year-name)" style="width: calc(100% - 100px);">
                    </div>
                    <button type="button" onclick="addTrophyField()" style="margin-top: 10px;">Add Trophy</button>
                    <br>
                    <input type="file" name="new_player_image" required style="margin-top: 20px;">
                    <br>
                    <button type="submit" style="margin-top: 20px;">Add Player</button>
                </form>
            </div>
        </div>
    </div>

    <footer>
        <div class="footer-container">
            <p>&copy; 2024 Ashesi Sports Insight. All rights reserved.</p>
        </div>
    </footer>
    <script>
    function addTrophyField() {
        var container = document.getElementById('trophiesContainer');
        var input = document.createElement('input');
        input.type = 'text';
        input.name = 'new_player_trophies[]';
        input.placeholder = 'Trophies (format: year-name)';
        input.style = 'width: calc(100% - 100px); margin-top: 10px;';
        container.appendChild(input);
    }
    </script>
    <script>
    document.getElementById('addTrophyButton').addEventListener('click', function() {
        const trophyContainer = document.getElementById('trophiesContainer');
        const newTrophyField = document.createElement('div');
        newTrophyField.classList.add('trophy-field');
        newTrophyField.innerHTML = `
            <input type="text" name="new_player_trophies[]" placeholder="Trophies (format: number-year-trophy_name)">
            <button type="button" class="removeTrophyButton">Remove</button>
        `;
        trophyContainer.appendChild(newTrophyField);
    });

    document.getElementById('trophiesContainer').addEventListener('click', function(event) {
        if (event.target && event.target.classList.contains('removeTrophyButton')) {
            event.target.parentNode.remove();
        }
    });
    </script>


    <script>
        const set1 = document.querySelectorAll('.orbiting-element.set1');
        const set2 = document.querySelectorAll('.orbiting-element.set2');
        let showingSet1 = true;

        function showNextSet() {
            if (showingSet1) {
                set1.forEach(el => {
                    el.style.opacity = 0;
                    el.style.pointerEvents = 'none'; // Disable interactions for hidden set
                });
                set2.forEach(el => {
                    el.style.opacity = 1;
                    el.style.pointerEvents = 'auto'; // Enable interactions for visible set
                });
                showingSet1 = false;
            }
        }

        function showPreviousSet() {
            if (!showingSet1) {
                set1.forEach(el => {
                    el.style.opacity = 1;
                    el.style.pointerEvents = 'auto';
                });
                set2.forEach(el => {
                    el.style.opacity = 0;
                    el.style.pointerEvents = 'none';
                });
                showingSet1 = true;
            }
        }

        window.addEventListener('scroll', function () {
            const scrollPosition = window.scrollY;
            const windowHeight = window.innerHeight;
            const animationContainer = document.querySelector('.animation-container');
            const containerTop = animationContainer.offsetTop;
            const containerHeight = animationContainer.offsetHeight;

            if (scrollPosition + windowHeight > containerTop && scrollPosition < containerTop + containerHeight) {
                const scrollFraction = (scrollPosition + windowHeight - containerTop) / (containerHeight + windowHeight);
                const innerElements1 = document.querySelectorAll('.orbiting-element.set1:not(.outer)');
                const outerElements1 = document.querySelectorAll('.orbiting-element.set1.outer');
                const innerElements2 = document.querySelectorAll('.orbiting-element.set2:not(.outer)');
                const outerElements2 = document.querySelectorAll('.orbiting-element.set2.outer');

                const numInnerElements1 = innerElements1.length;
                const numOuterElements1 = outerElements1.length;
                const numInnerElements2 = innerElements2.length;
                const numOuterElements2 = outerElements2.length;

                const angleStepInner1 = 360 / numInnerElements1;
                const angleStepOuter1 = 360 / numOuterElements1;
                const angleStepInner2 = 360 / numInnerElements2;
                const angleStepOuter2 = 360 / numOuterElements2;

                innerElements1.forEach((element, index) => {
                    const orbitRotation = scrollFraction * 360 + angleStepInner1 * index;
                    element.style.transform = `rotate(${orbitRotation}deg) translate(300px) rotate(-${orbitRotation}deg)`;
                });

                outerElements1.forEach((element, index) => {
                    const orbitRotation = scrollFraction * 360 + angleStepOuter1 * index;
                    element.style.transform = `rotate(${orbitRotation}deg) translate(500px) rotate(-${orbitRotation}deg)`;
                });

                innerElements2.forEach((element, index) => {
                    const orbitRotation = scrollFraction * 360 + angleStepInner2 * index;
                    element.style.transform = `rotate(${orbitRotation}deg) translate(300px) rotate(-${orbitRotation}deg)`;
                });

                outerElements2.forEach((element, index) => {
                    const orbitRotation = scrollFraction * 360 + angleStepOuter2 * index;
                    element.style.transform = `rotate(${orbitRotation}deg) translate(500px) rotate(-${orbitRotation}deg)`;
                });
            }
        });

        // Initialize the visibility of the elements
        set1.forEach(el => {
            el.style.opacity = 1;
            el.style.pointerEvents = 'auto';
        });
        set2.forEach(el => {
            el.style.opacity = 0;
            el.style.pointerEvents = 'none';
        });

        // Modal functionality
        const modal = document.getElementById("playerModal");
        const closeModal = document.querySelector(".modal .close");

        

        // Function to open modal with player info
        function openModal(playerName, playerPosition, playerDescription, playerImage, playerMoreLink) {
            document.getElementById("modalPlayerName").textContent = playerName;
            document.getElementById("modalPlayerPosition").textContent = playerPosition;
            document.getElementById("modalPlayerDescription").textContent = playerDescription;
            document.getElementById("modalPlayerImage").src = playerImage;
            document.getElementById("modalSeeMore").href = playerMoreLink;
            modal.style.display = "block";
        }

        // Close the modal
        closeModal.onclick = function() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        // Attach click event listeners to each player element
        document.querySelectorAll('.orbiting-element').forEach(player => {
            player.addEventListener('click', () => {
                const playerName = player.getAttribute('data-player-name');
                const playerPosition = player.getAttribute('data-player-position');
                const playerDescription = player.getAttribute('data-player-description');
                const playerImage = player.querySelector('img').src; // Assuming the image source is the same
                const playerId = player.getAttribute('data-player-id'); // Unique identifier for the player

                // Set the correct URL path for the "See More" link
                const playerMoreLink = `http://localhost/Ashesi_Sport_Analysis/view/pages/seemore.php?player=${playerId}`;

                if ((showingSet1 && player.classList.contains('set1')) || (!showingSet1 && player.classList.contains('set2'))) {
                    openModal(playerName, playerPosition, playerDescription, playerImage, playerMoreLink);
                }
            });
        });

        // Functionality for Add Player Modal
        const addPlayerModal = document.getElementById("addPlayerModal");
        const closeAddPlayerModalBtn = document.querySelector("#addPlayerModal .close");

        function openAddPlayerModal() {
            addPlayerModal.style.display = "block";
        }

        function closeAddPlayerModal() {
            addPlayerModal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == addPlayerModal) {
                addPlayerModal.style.display = "none";
            }
        }

        // Handle form submissions with AJAX to avoid page reloads
        document.getElementById('teamPhotoForm').addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(this);
            if (!document.getElementById('teamImageInput').files.length) {
                alert('Please choose a file to upload.');
                return;
            }
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message);
                    document.getElementById('teamPhoto').src = `../../uploads/${data.image}`;
                } else {
                    alert(data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        });

        document.getElementById('deleteTeamPhotoButton').addEventListener('click', function() {
            const teamPhoto = document.getElementById('teamPhoto').src;
            if (teamPhoto.includes('default_team_photo.jpg')) {
                alert('No team photo to delete.');
                return;
            }
            const formData = new FormData();
            formData.append('delete_team_image', '1');
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message);
                    document.getElementById('teamPhoto').src = 'default_team_photo.jpg';
                } else {
                    alert(data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        });

        document.getElementById('coachPhotoForm').addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(this);
            if (!document.getElementById('coachImageInput').files.length) {
                alert('Please choose a file to upload.');
                return;
            }
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message);
                    document.getElementById('coachPhoto').src = `../../uploads/${data.image}`;
                } else {
                    alert(data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        });

        document.getElementById('deleteCoachPhotoButton').addEventListener('click', function() {
            const coachPhoto = document.getElementById('coachPhoto').src;
            if (coachPhoto.includes('default_coach_photo.png')) {
                alert('No coach photo to delete.');
                return;
            }
            const formData = new FormData();
            formData.append('delete_coach_image', '1');
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message);
                    document.getElementById('coachPhoto').src = 'default_coach_photo.png';
                } else {
                    alert(data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        });

        document.getElementById('deletePlayerForm').addEventListener('submit', function(event) {
            event.preventDefault();
            if (confirm('Are you sure you want to delete this player? This action cannot be undone and will delete everything about this player.')) {
                const formData = new FormData(this);
                if (!validateSelection('playerSelect', 'No player selected to delete.')) {
                    return;
                }
                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert(data.message);
                        const playerElement = document.querySelector(`.orbiting-element[data-player-id='${data.player_id}']`);
                        if (playerElement) {
                            playerElement.remove();
                        }
                        document.querySelector(`#playerSelect option[value='${data.player_id}']`).remove();
                        document.querySelector(`#changePlayerSelect option[value='${data.player_id}']`).remove();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
            }
        });

        document.getElementById('changePlayerPictureForm').addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(this);
            if (!validateSelection('changePlayerSelect', 'No player selected to change picture.')) {
                return;
            }
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message);
                    const playerElement = document.querySelector(`.orbiting-element[data-player-id='${formData.get('change_player_id')}'] img`);
                    if (playerElement) {
                        playerElement.src = `../../uploads/${data.image}`;
                    }
                } else {
                    alert(data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        });

        document.getElementById('addPlayerForm').addEventListener('submit', function(event) {
    event.preventDefault();
    const formData = new FormData(this);
    if (!validateFileInput('new_player_image', 'Please select an image for the new player.')) { // Make sure this ID is correct
        return;
    }

    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert(data.message);
            window.location.reload(); // Reload the page to show the new player
        } else {
            alert(data.message);
        }
    })
    .catch(error => console.error('Error:', error));
});

        // Replace this function:
function validateFileInput(inputId, message) {
    const input = document.querySelector(`input[name="${inputId}"]`);
    if (!input || !input.files.length) { // Updated condition to check if a file has been selected
        alert(message);
        return false;
    }
    return true;
}

// Ensure the correct ID is used when calling this function during the form submission.

        function validateSelection(selectId, message) {
            const select = document.getElementById(selectId);
            if (!select || !select.value) {
                alert(message);
                return false;
            }
            return true;
        }
    </script>
</body>
</html>
