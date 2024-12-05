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
        body {
            background-color: blue;
            color: white;
            font-family: Arial, sans-serif;
        }
        h1, p {
            text-align: center;
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        li {
            text-align: center;
            margin: 10px 0;
        }
        a {
            display: inline-block;
            padding: 10px 20px;
            text-decoration: none;
            color: white;
            background-color: #4CAF50; /* Green button color */
            border: none;
            border-radius: 5px;
            font-size: 16px;
        }
        a:hover {
            background-color: #45a049; /* Darker green on hover */
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
        <li><a href="changepassword.php">Change Password</a></li>
        <li><a href="logout.php">Log Out</a></li>
    </ul>
</body>
</html>
