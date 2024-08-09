<?php
session_start();
include '../../settings/connection.php';

// Ensure only admin can access this page
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../../login.php');
    exit();
}

// Handle form submissions for adding, editing, and deleting tournaments
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_tournament'])) {
        $name = $_POST['name'];
        $sport_id = $_POST['sport_id'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];

        try {
            $sql = "INSERT INTO tournaments (Name, SportID, StartDate, EndDate) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$name, $sport_id, $start_date, $end_date]);

            $_SESSION['success_message'] = 'Tournament added successfully.';
            header('Location: admin_manage_tournaments.php');
            exit();
        } catch (PDOException $e) {
            $_SESSION['error_message'] = 'Error adding tournament: ' . $e->getMessage();
            header('Location: admin_manage_tournaments.php');
            exit();
        }
    } elseif (isset($_POST['edit_tournament'])) {
        $tournament_id = $_POST['tournament_id'];
        $name = $_POST['name'];
        $sport_id = $_POST['sport_id'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];

        try {
            $sql = "UPDATE tournaments SET Name = ?, SportID = ?, StartDate = ?, EndDate = ? WHERE TournamentID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$name, $sport_id, $start_date, $end_date, $tournament_id]);

            $_SESSION['success_message'] = 'Tournament updated successfully.';
            header('Location: admin_manage_tournaments.php');
            exit();
        } catch (PDOException $e) {
            $_SESSION['error_message'] = 'Error updating tournament: ' . $e->getMessage();
            header('Location: admin_manage_tournaments.php');
            exit();
        }
    } elseif (isset($_POST['delete_tournament'])) {
        $tournament_id = $_POST['tournament_id'];

        try {
            $sql = "DELETE FROM tournaments WHERE TournamentID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$tournament_id]);

            $_SESSION['success_message'] = 'Tournament deleted successfully.';
            header('Location: admin_manage_tournaments.php');
            exit();
        } catch (PDOException $e) {
            $_SESSION['error_message'] = 'Error deleting tournament: ' . $e->getMessage();
            header('Location: admin_manage_tournaments.php');
            exit();
        }
    }
}

// Fetch all tournaments for display
$tournaments = [];
try {
    $stmt = $conn->query("SELECT t.TournamentID, t.Name, t.SportID, s.SportName, t.StartDate, t.EndDate 
                          FROM tournaments t
                          JOIN sports s ON t.SportID = s.SportID
                          ORDER BY t.StartDate DESC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $tournaments[] = $row;
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Error fetching tournaments: ' . $e->getMessage();
}

// Fetch all sports for the dropdown
$sports = [];
try {
    $stmt = $conn->query("SELECT SportID, SportName FROM sports ORDER BY SportName ASC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $sports[] = $row;
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Error fetching sports: ' . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Manage Tournaments</title>
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


        /* Additional styles for managing tournaments */
        .tournament-list {
            margin-top: 20px;
        }

        .tournament-item {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .tournament-item img {
            width: 100px;
            height: auto;
            border-radius: 5px;
        }

        .tournament-item-details {
            flex: 1;
            margin-left: 20px;
        }

        .tournament-item-actions {
            display: flex;
            gap: 10px;
        }

        .tournament-item-actions form {
            margin: 0;
        }

        .tournament-item-actions button {
            padding: 5px 10px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            color: white;
            background-color: #4B0000;
            transition: background-color 0.3s ease;
        }

        .tournament-item-actions button:hover {
            background-color: #333;
        }

        /* Enhanced form styles */
        .upload-story-form {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            width: 100%;
            max-width: 600px;
            margin: auto;
        }

        .upload-story-form h3 {
            color: #4B0000;
            font-size: 1.5rem;
            margin-bottom: 10px;
            text-align: center;
        }

        .upload-story-form label {
            display: block;
            margin-bottom: 5px;
            color: #333;
        }

        .upload-story-form input[type="text"],
        .upload-story-form input[type="date"],
        .upload-story-form select {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .upload-story-form button {
            width: 100%;
            padding: 10px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            color: white;
            background-color: #4B0000;
            transition: background-color 0.3s ease;
            font-size: 1.1rem;
        }

        .upload-story-form button:hover {
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
                    <!-- other navigation items -->
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
            <img src="https://via.placeholder.com/40" alt="Admin">
            <h2>Admin Panel</h2>
        </div>
        <ul>
            <li><a href="admin_adding_matches.php">Add Match</a></li>
            <li><a href="admin_upload_story.php">Manage Stories</a></li>
            <li><a href="admin_manage_tournaments.php">Manage Tournaments</a></li>
        </ul>
    </div>
    <div class="main-content">
        <section class="section">
            <h2>Manage Upcoming Tournaments</h2>
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

            <!-- Form to add a new tournament -->
            <div class="upload-story-form">
                <h3>Add New Tournament</h3>
                <form action="admin_manage_tournaments.php" method="post">
                    <label for="name">Tournament Name:</label>
                    <input type="text" name="name" id="name" required>

                    <label for="sport_id">Sport:</label>
                    <select name="sport_id" id="sport_id" required>
                        <option value="">Select a Sport</option>
                        <?php foreach ($sports as $sport): ?>
                            <option value="<?php echo htmlspecialchars($sport['SportID']); ?>">
                                <?php echo htmlspecialchars($sport['SportName']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label for="start_date">Start Date:</label>
                    <input type="date" name="start_date" id="start_date" required>

                    <label for="end_date">End Date:</label>
                    <input type="date" name="end_date" id="end_date" required>

                    <button type="submit" name="add_tournament">Add Tournament</button>
                </form>
            </div>

            <!-- Display the list of tournaments -->
            <div class="tournament-list">
                <?php foreach ($tournaments as $tournament): ?>
                    <div class="tournament-item">
                        <div class="tournament-item-details">
                            <h3><?php echo htmlspecialchars($tournament['Name']); ?></h3>
                            <p>Sport: <?php echo htmlspecialchars($tournament['SportName']); ?></p>
                            <p>Start Date: <?php echo htmlspecialchars($tournament['StartDate']); ?></p>
                            <p>End Date: <?php echo htmlspecialchars($tournament['EndDate']); ?></p>
                        </div>
                        <div class="tournament-item-actions">
                            <form action="admin_manage_tournaments.php" method="post">
                                <input type="hidden" name="tournament_id" value="<?php echo htmlspecialchars($tournament['TournamentID']); ?>">
                                <input type="text" name="name" value="<?php echo htmlspecialchars($tournament['Name']); ?>" required>
                                <select name="sport_id" required>
                                    <?php foreach ($sports as $sport): ?>
                                        <option value="<?php echo htmlspecialchars($sport['SportID']); ?>"
                                            <?php echo ($sport['SportID'] == $tournament['SportID']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($sport['SportName']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="date" name="start_date" value="<?php echo htmlspecialchars($tournament['StartDate']); ?>" required>
                                <input type="date" name="end_date" value="<?php echo htmlspecialchars($tournament['EndDate']); ?>" required>
                                <button type="submit" name="edit_tournament">Update</button>
                            </form>
                            <form action="admin_manage_tournaments.php" method="post">
                                <input type="hidden" name="tournament_id" value="<?php echo htmlspecialchars($tournament['TournamentID']); ?>">
                                <button type="submit" name="delete_tournament">Delete</button>
                            </form>
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
</body>
</html>
