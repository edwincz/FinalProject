<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['valid_user'])) {
    header("Location: login.php");
    exit();
}

// Read database configuration
$inifile = parse_ini_file("myproperties.ini", true);

if ($inifile === false) {
    die("Error reading database configuration file.");
}

$dbhost = $inifile["DB"]["DBHOST"];
$dbuser = $inifile["DB"]["DBUSER"];
$dbpass = $inifile["DB"]["DBPASS"];
$dbname = $inifile["DB"]["DBNAME"];

// Create connection
$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . htmlspecialchars($conn->connect_error));
}

// Initialize variables
$error_message = "";
$success_message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate inputs
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_message = "All fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "New password and confirm password do not match.";
    } else {
        // Get the logged-in user's username
        $username = $_SESSION['valid_user'];

        // Fetch the current hashed password from the database
        $query = "SELECT passwordhash FROM user_authentication WHERE username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($hashed_password);
        $stmt->fetch();
        $stmt->close();

        // Verify the current password
        if (!password_verify($current_password, $hashed_password)) {
            $error_message = "Current password is incorrect.";
        } else {
            // Hash the new password
            $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Update the password in the database
            $update_query = "UPDATE user_authentication SET passwordhash = ? WHERE username = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("ss", $new_hashed_password, $username);

            if ($stmt->execute()) {
                $success_message = "Password changed successfully.";
            } else {
                $error_message = "Error updating the password. Please try again.";
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Change Password</h1>

    <?php if (!empty($error_message)): ?>
        <div class="error"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <?php if (!empty($success_message)): ?>
        <div class="success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>

    <form method="POST" action="changepassword.php">
        <label for="current_password">Current Password:</label>
        <input type="password" name="current_password" id="current_password" required><br>

        <label for="new_password">New Password:</label>
        <input type="password" name="new_password" id="new_password" required><br>

        <label for="confirm_password">Confirm New Password:</label>
        <input type="password" name="confirm_password" id="confirm_password" required><br>

        <button type="submit">Change Password</button>
    </form>

    <a href="home.php">Back to Home</a>
</body>
</html>
