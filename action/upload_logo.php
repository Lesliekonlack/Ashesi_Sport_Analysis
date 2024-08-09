<?php
session_start();
include '../settings/connection.php'; // Include your database connection file

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['team_logo']) && isset($_SESSION['coach_id'])) {
    $coachId = $_SESSION['coach_id'];
    $teamId = $_POST['team_id'];
    $uploadDir = '../uploads/'; // Change to a relative path
    $imageFileType = strtolower(pathinfo($_FILES['team_logo']['name'], PATHINFO_EXTENSION));
    $targetFile = $uploadDir . $teamId . '.' . $imageFileType; // Save as team_id.extension

    // Create directory if it doesn't exist and ensure permissions are correct
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            $response['message'] = 'Failed to create upload directory.';
            echo json_encode($response);
            exit();
        }
    }

    // Ensure the upload directory is writable
    if (!is_writable($uploadDir)) {
        $response['message'] = 'Upload directory is not writable. Please check permissions.';
        echo json_encode($response);
        exit();
    }

    // Set umask to ensure files are created with the correct permissions
    umask(0022);

    // Check if image file is an actual image or fake image
    $check = getimagesize($_FILES['team_logo']['tmp_name']);
    if ($check === false) {
        $response['message'] = 'File is not an image.';
        echo json_encode($response);
        exit();
    }

    // Check file size
    if ($_FILES['team_logo']['size'] > 5000000) {
        $response['message'] = 'Sorry, your file is too large.';
        echo json_encode($response);
        exit();
    }

    // Allow certain file formats
    if ($imageFileType != 'jpg' && $imageFileType != 'jpeg' && $imageFileType != 'png' && $imageFileType != 'gif') {
        $response['message'] = 'Sorry, only JPG, JPEG, PNG & GIF files are allowed.';
        echo json_encode($response);
        exit();
    }

    // Remove old file if it exists
    foreach (glob($uploadDir . $teamId . '.*') as $file) {
        if (file_exists($file)) {
            if (!unlink($file)) {
                $response['message'] = 'Error removing old team logo.';
                echo json_encode($response);
                exit();
            }
        }
    }

    // Move uploaded file to target location
    if (move_uploaded_file($_FILES['team_logo']['tmp_name'], $targetFile)) {
        // Update the team's logo path in the database
        $sql = "UPDATE teams SET Logo = ? WHERE TeamID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$teamId . '.' . $imageFileType, $teamId]); // Save just the filename

        $response['success'] = true;
        $response['message'] = 'File uploaded successfully.';
    } else {
        $response['message'] = 'Sorry, there was an error uploading your file.';
    }
} else {
    $response['message'] = 'Invalid request.';
}

echo json_encode($response);
?>

