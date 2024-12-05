<?php
session_start();

if (!isset($_SESSION['valid_user'])) {
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Library Application - Home</title>
    <style type="text/css">
        /* Your CSS styles */
        ul {
            list-style-type: none;
        }
        li {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['valid_user']); ?>!</h1>
    <p>Select an option below:</p>
    <ul>
        <li><a href="books.php">Manage Books</a></li>
        <li><a href="students.php">Manage Students</a></li>
        <li><a href="checkoutsdata.php">Checkouts and Returns</a></li>
        <li><a href="logout.php">Log Out</a></li>
    </ul>
</body>
</html>
