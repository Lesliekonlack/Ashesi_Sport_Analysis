<?php
session_start();
include '../../settings/connection.php';

// Ensure only admin can access this page
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../../login.php');
    exit();
}

// Fetch all teams for the dropdown
$teams = [];
try {
    $stmt = $conn->query("SELECT TeamID, TeamName FROM teams ORDER BY TeamName ASC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $teams[] = $row;
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Error fetching teams: ' . $e->getMessage();
}

// Handle form submissions for adding, editing, and deleting stories
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_story'])) {
        $title = $_POST['title'];
        $content = $_POST['content'];
        $team_id = !empty($_POST['team_id']) ? $_POST['team_id'] : null; // Set to null if empty
        $image = $_FILES['image'];

        // Handle image upload
        $imagePath = '';
        if ($image['error'] == 0) {
            $imagePath = 'uploads/' . basename($image['name']);
            if (!move_uploaded_file($image['tmp_name'], '../../' . $imagePath)) {
                $_SESSION['error_message'] = 'Failed to upload image.';
                header('Location: admin_upload_story.php');
                exit();
            }
        } else {
            $_SESSION['error_message'] = 'Please upload an image.';
            header('Location: admin_upload_story.php');
            exit();
        }

        try {
            $sql = "INSERT INTO stories (Title, Content, TeamID, ImagePath) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$title, $content, $team_id, $imagePath]);

            $_SESSION['success_message'] = 'Story uploaded successfully.';
            header('Location: admin_upload_story.php');
            exit();
        } catch (PDOException $e) {
            $_SESSION['error_message'] = 'Error uploading story: ' . $e->getMessage();
            header('Location: admin_upload_story.php');
            exit();
        }
    } elseif (isset($_POST['edit_story'])) {
        $storyID = $_POST['story_id'];
        $title = $_POST['title'];
        $content = $_POST['content'];
        $team_id = !empty($_POST['team_id']) ? $_POST['team_id'] : null; // Set to null if empty
        $image = $_FILES['image'];
        $imagePath = $_POST['existing_image']; // Keep existing image by default

        // Handle image upload if a new one is provided
        if ($image['error'] == 0) {
            $imagePath = 'uploads/' . basename($image['name']);
            if (!move_uploaded_file($image['tmp_name'], '../../' . $imagePath)) {
                $_SESSION['error_message'] = 'Failed to upload new image.';
                header('Location: admin_upload_story.php');
                exit();
            }
        }

        try {
            $sql = "UPDATE stories SET Title = ?, Content = ?, TeamID = ?, ImagePath = ? WHERE StoryID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$title, $content, $team_id, $imagePath, $storyID]);

            $_SESSION['success_message'] = 'Story updated successfully.';
            header('Location: admin_upload_story.php');
            exit();
        } catch (PDOException $e) {
            $_SESSION['error_message'] = 'Error updating story: ' . $e->getMessage();
            header('Location: admin_upload_story.php');
            exit();
        }
    } elseif (isset($_POST['delete_story'])) {
        $storyID = $_POST['story_id'];

        try {
            $sql = "DELETE FROM stories WHERE StoryID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$storyID]);

            $_SESSION['success_message'] = 'Story deleted successfully.';
            header('Location: admin_upload_story.php');
            exit();
        } catch (PDOException $e) {
            $_SESSION['error_message'] = 'Error deleting story: ' . $e->getMessage();
            header('Location: admin_upload_story.php');
            exit();
        }
    }
}

// Fetch all stories for display
$stories = [];
try {
    $stmt = $conn->query("SELECT * FROM stories ORDER BY DatePosted DESC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $stories[] = $row;
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Error fetching stories: ' . $e->getMessage();
}

// Function to get image path, ensuring it exists
function getImagePath($imagePath) {
    $defaultImagePath = '../../uploads/default_image.png'; // Replace with your default image path
    if ($imagePath && file_exists(__DIR__ . '/../../' . $imagePath)) {
        return '../../' . $imagePath;
    }
    return $defaultImagePath;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Manage Stories</title>
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
            width: 180px;
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

        .upload-story-form {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .upload-story-form h3 {
            color: #4B0000;
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .upload-story-form label {
            display: block;
            margin-bottom: 5px;
            color: #333;
        }

        .upload-story-form input[type="text"],
        .upload-story-form textarea,
        .upload-story-form select {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .upload-story-form button {
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            color: white;
            background-color: #4B0000;
            transition: background-color 0.3s ease;
        }

        .upload-story-form button:hover {
            background-color: #333;
        }

        .story-list {
            margin-top: 20px;
        }

        .story-item {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .story-item img {
            width: 100px;
            height: auto;
            border-radius: 5px;
        }

        .story-item-details {
            flex: 1;
            margin-left: 20px;
        }

        .story-item-actions {
            display: flex;
            gap: 10px;
        }

        .story-item-actions form {
            margin: 0;
        }

        .story-item-actions button {
            padding: 5px 10px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            color: white;
            background-color: #4B0000;
            transition: background-color 0.3s ease;
        }

        .story-item-actions button:hover {
            background-color: #333;
        }

        footer {
            background-color: #4B0000;
            color: white;
            text-align: center;
            padding: 10px 0;
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
        </ul>
    </div>
    <div class="main-content">
        <section class="section">
            <h2>Upload a New Story</h2>
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
            <div class="upload-story-form">
                <form action="admin_upload_story.php" method="post" enctype="multipart/form-data">
                    <label for="title">Story Title:</label>
                    <input type="text" name="title" id="title" required>

                    <label for="content">Story Content:</label>
                    <textarea name="content" id="content" rows="10" required></textarea>

                    <label for="team_id">Associate with a Team (Optional):</label>
                    <select name="team_id" id="team_id">
                        <option value="">-- None --</option>
                        <?php foreach ($teams as $team): ?>
                            <option value="<?php echo htmlspecialchars($team['TeamID']); ?>"><?php echo htmlspecialchars($team['TeamName']); ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label for="image">Upload Image:</label>
                    <input type="file" name="image" id="image" accept="image/*" required>

                    <button type="submit" name="add_story">Upload Story</button>
                </form>
            </div>
        </section>

        <section class="section story-list">
            <h2>Manage Existing Stories</h2>
            <?php foreach ($stories as $story): ?>
                <div class="story-item">
                    <img src="<?php echo htmlspecialchars(getImagePath($story['ImagePath'])); ?>" alt="<?php echo htmlspecialchars($story['Title']); ?>">
                    <div class="story-item-details">
                        <h3><?php echo htmlspecialchars($story['Title']); ?></h3>
                        <p><?php echo htmlspecialchars(substr($story['Content'], 0, 100)); ?>...</p>
                    </div>
                    <div class="story-item-actions">
                        <form action="admin_upload_story.php" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="story_id" value="<?php echo htmlspecialchars($story['StoryID']); ?>">
                            <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($story['ImagePath']); ?>">
                            <input type="text" name="title" value="<?php echo htmlspecialchars($story['Title']); ?>" required>
                            <textarea name="content" rows="3" required><?php echo htmlspecialchars($story['Content']); ?></textarea>
                            <select name="team_id" id="team_id_<?php echo htmlspecialchars($story['StoryID']); ?>">
                                <option value="">-- None --</option>
                                <?php foreach ($teams as $team): ?>
                                    <option value="<?php echo htmlspecialchars($team['TeamID']); ?>" <?php echo $story['TeamID'] == $team['TeamID'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($team['TeamName']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="file" name="image" accept="image/*">
                            <button type="submit" name="edit_story">Update</button>
                        </form>
                        <form action="admin_upload_story.php" method="post">
                            <input type="hidden" name="story_id" value="<?php echo htmlspecialchars($story['StoryID']); ?>">
                            <button type="submit" name="delete_story">Delete</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>
    </div>
</body>
</html>
