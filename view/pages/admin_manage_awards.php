<?php
session_start();
include '../../settings/connection.php';

// Ensure only admin can access this page
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../../login.php');
    exit();
}

// Handle form submission for creating a new award
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_award'])) {
    $eventName = $_POST['event_name'];
    $description = $_POST['description'];
    $eventDate = $_POST['event_date'];
    $sportID = $_POST['sport_id'];
    $eventType = $_POST['event_type'];
    $location = $_POST['location'];

    // Handle image upload
    $eventFlyerImage = 'default_event_image.png';
    if (isset($_FILES['event_flyer_image']) && $_FILES['event_flyer_image']['error'] == 0) {
        $eventFlyerImage = 'uploads/' . basename($_FILES['event_flyer_image']['name']);
        if (!move_uploaded_file($_FILES['event_flyer_image']['tmp_name'], '../../' . $eventFlyerImage)) {
            $_SESSION['error_message'] = 'Failed to upload event flyer image.';
            header('Location: admin_manage_awards.php');
            exit();
        }
    }

    try {
        $sql = "INSERT INTO events (EventName, Description, EventDate, SportID, Location, EventType, EventFlyerImage) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$eventName, $description, $eventDate, $sportID, $location, $eventType, $eventFlyerImage]);

        $_SESSION['success_message'] = 'Award event created successfully.';
        header('Location: admin_manage_awards.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Error creating award event: ' . $e->getMessage();
        header('Location: admin_manage_awards.php');
        exit();
    }
}

// Handle form submission for updating an award
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_award'])) {
    $eventID = $_POST['event_id'];
    $eventName = $_POST['event_name'];
    $description = $_POST['description'];
    $eventDate = $_POST['event_date'];
    $sportID = $_POST['sport_id'];
    $eventType = $_POST['event_type'];
    $location = $_POST['location'];

    // Handle image upload
    $eventFlyerImage = $_POST['existing_image'];
    if (isset($_FILES['event_flyer_image']) && $_FILES['event_flyer_image']['error'] == 0) {
        $eventFlyerImage = 'uploads/' . basename($_FILES['event_flyer_image']['name']);
        if (!move_uploaded_file($_FILES['event_flyer_image']['tmp_name'], '../../' . $eventFlyerImage)) {
            $_SESSION['error_message'] = 'Failed to upload event flyer image.';
            header('Location: admin_manage_awards.php');
            exit();
        }
    }

    try {
        $sql = "UPDATE events SET EventName = ?, Description = ?, EventDate = ?, SportID = ?, Location = ?, EventType = ?, EventFlyerImage = ? WHERE EventID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$eventName, $description, $eventDate, $sportID, $location, $eventType, $eventFlyerImage, $eventID]);

        $_SESSION['success_message'] = 'Award event updated successfully.';
        header('Location: admin_manage_awards.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Error updating award event: ' . $e->getMessage();
        header('Location: admin_manage_awards.php');
        exit();
    }
}

// Handle award deletion
if (isset($_GET['delete_award'])) {
    $eventID = $_GET['delete_award'];
    try {
        $sql = "DELETE FROM events WHERE EventID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$eventID]);

        $_SESSION['success_message'] = 'Award event deleted successfully.';
        header('Location: admin_manage_awards.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Error deleting award event: ' . $e->getMessage();
        header('Location: admin_manage_awards.php');
        exit();
    }
}

// Fetch existing awards from the database
$awards = [];
try {
    $sql = "SELECT * FROM events";
    $stmt = $conn->query($sql);
    $awards = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Error fetching awards: ' . $e->getMessage();
    header('Location: admin_manage_awards.php');
    exit();
}

// Fetch available sports
$sports = [];
try {
    $sql = "SELECT SportID, SportName FROM sports";
    $stmt = $conn->query($sql);
    $sports = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Error fetching sports: ' . $e->getMessage();
    header('Location: admin_manage_awards.php');
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Manage Awards</title>
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

        .error-message, .success-message {
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 5px;
            color: white;
        }

        .error-message {
            background-color: #f44336;
        }

        .success-message {
            background-color: #4CAF50;
        }

        .upload-award-form {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .upload-award-form h3 {
            color: #4B0000;
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .upload-award-form label {
            display: block;
            margin-bottom: 5px;
            color: #333;
        }

        .upload-award-form input[type="text"],
        .upload-award-form textarea,
        .upload-award-form select {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .upload-award-form button {
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            color: white;
            background-color: #4B0000;
            transition: background-color 0.3s ease;
        }

        .upload-award-form button:hover {
            background-color: #333;
        }

        .award-list table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .award-list th,
        .award-list td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: left;
        }

        .award-list th {
            background-color: #4B0000;
            color: white;
        }

        .award-list td {
            background-color: #f9f9f9;
        }

        .award-list input[type="text"],
        .award-list textarea,
        .award-list select {
            width: 100%;
            padding: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .award-list button {
            padding: 5px 10px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            color: white;
            background-color: #4B0000;
            transition: background-color 0.3s ease;
        }

        .award-list button:hover {
            background-color: #333;
        }

        .award-list .delete-button {
            background-color: #f44336;
        }

        .award-list .delete-button:hover {
            background-color: #d32f2f;
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
                    <!-- Other navigation items can be added here -->
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
            <li><a href="admin_upload_story.php">Upload Story</a></li>
            <li><a href="admin_manage_tournaments.php">Manage Tournaments</a></li>
            <li><a href="admin_manage_awards.php">Manage Awards</a></li>
        </ul>
    </div>
    <div class="main-content">
        <section class="section">
            <h2>Manage Awards</h2>
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

            <div class="upload-award-form">
                <h3>Create New Award Event</h3>
                <form action="admin_manage_awards.php" method="post" enctype="multipart/form-data">
                    <label for="event_name">Event Name:</label>
                    <input type="text" name="event_name" id="event_name" required>

                    <label for="description">Description:</label>
                    <textarea name="description" id="description" rows="5" required></textarea>

                    <label for="event_date">Event Date:</label>
                    <input type="date" name="event_date" id="event_date" required>

                    <label for="sport_id">Sport:</label>
                    <select name="sport_id" id="sport_id" required>
                        <?php foreach ($sports as $sport): ?>
                            <option value="<?php echo htmlspecialchars($sport['SportID']); ?>"><?php echo htmlspecialchars($sport['SportName']); ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label for="location">Location:</label>
                    <input type="text" name="location" id="location" required>

                    <label for="event_type">Event Type:</label>
                    <input type="text" name="event_type" id="event_type" required>

                    <label for="event_flyer_image">Upload Event Flyer Image:</label>
                    <input type="file" name="event_flyer_image" id="event_flyer_image" accept="image/*">

                    <button type="submit" name="create_award">Create Award</button>
                </form>
            </div>

            <div class="award-list">
                <h3>Existing Award Events</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Event Name</th>
                            <th>Description</th>
                            <th>Event Date</th>
                            <th>Sport</th>
                            <th>Location</th>
                            <th>Event Type</th>
                            <th>Flyer Image</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($awards as $award): ?>
                            <tr>
                                <form action="admin_manage_awards.php" method="post" enctype="multipart/form-data">
                                    <td><input type="text" name="event_name" value="<?php echo htmlspecialchars($award['EventName']); ?>" required></td>
                                    <td><textarea name="description" required><?php echo htmlspecialchars($award['Description']); ?></textarea></td>
                                    <td><input type="date" name="event_date" value="<?php echo htmlspecialchars($award['EventDate']); ?>" required></td>
                                    <td>
                                        <select name="sport_id" required>
                                            <?php foreach ($sports as $sport): ?>
                                                <option value="<?php echo htmlspecialchars($sport['SportID']); ?>" <?php if ($sport['SportID'] == $award['SportID']) echo 'selected'; ?>>
                                                    <?php echo htmlspecialchars($sport['SportName']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td><input type="text" name="location" value="<?php echo htmlspecialchars($award['Location']); ?>" required></td>
                                    <td><input type="text" name="event_type" value="<?php echo htmlspecialchars($award['EventType']); ?>" required></td>
                                    <td>
                                        <img src="../../<?php echo htmlspecialchars($award['EventFlyerImage']); ?>" alt="Flyer" width="100">
                                        <input type="file" name="event_flyer_image" accept="image/*">
                                        <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($award['EventFlyerImage']); ?>">
                                    </td>
                                    <td>
                                        <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($award['EventID']); ?>">
                                        <button type="submit" name="update_award">Update</button>
                                        <a href="admin_manage_awards.php?delete_award=<?php echo htmlspecialchars($award['EventID']); ?>" class="delete-button">Delete</a>
                                    </td>
                                </form>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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
