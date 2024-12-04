<?php
session_start();

if (!isset($_SESSION['valid_user'])) {
    header('Location: login.php');
    exit();
}

if (isset($_GET['bookid'])) {
    $bookid = $_GET['bookid'];

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

    $stmt = $conn->prepare("UPDATE book SET active = FALSE WHERE bookid = ?");
    if ($stmt === false) {
        die("Prepare failed: " . htmlspecialchars($conn->error));
    }

    $stmt->bind_param("i", $bookid);

    if ($stmt->execute()) {
        header('Location: books.php');
        exit();
    } else {
        echo "Error: " . htmlspecialchars($stmt->error);
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request.";
}
?>
