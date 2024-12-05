<?php
session_start();

// Check if user is logged in
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

$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . htmlspecialchars($conn->connect_error));
}

// Validate POST data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rocketid = isset($_POST['rocketid']) ? trim($_POST['rocketid']) : '';
    $active = isset($_POST['active']) ? trim($_POST['active']) : '';

    // Basic validation
    if (empty($rocketid) || ($active !== '0' && $active !== '1')) {
        // Invalid input, display error
        $error_message = "Invalid input. Please try again.";
    } else {
        // Update the student's active status
        $stmt = $conn->prepare("UPDATE student SET active = ? WHERE rocketid = ?");
        if ($stmt === false) {
            die("Prepare failed: " . htmlspecialchars($conn->error));
        }

        // 'active' is stored as a boolean (which MySQL typically treats as TINYINT), so bind as integer
        $active_value = (int)$active;
        $stmt->bind_param("is", $active_value, $rocketid);

        if ($stmt->execute()) {
            // Redirect back to students page after successful update
            $stmt->close();
            $conn->close();
            header("Location: students.php");
            exit();
        } else {
            $error_message = "Error updating student status. Please try again.";
        }

        $stmt->close();
    }
} else {
    $error_message = "Invalid request method.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Student Status</title>
    <style>
        .error { color: red; }
        body { font-family: Arial, sans-serif; }
    </style>
</head>
<body>
    <h1>Update Student Status</h1>
    <?php if (!empty($error_message)): ?>
        <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
        <p><a href="students.php">Back to Students</a></p>
    <?php endif; ?>
</body>
</html>
