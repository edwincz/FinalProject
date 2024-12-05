<?php
session_start();

// Ensure user is logged in
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

// Query to fetch all checked-out books along with student details
$query = "
    SELECT 
        c.checkoutid,
        b.title AS book_title,
        s.name AS student_name,
        s.phone AS student_phone,
        c.promise_date
    FROM checkout c
    INNER JOIN book b ON c.bookid = b.bookid
    INNER JOIN student s ON c.rocketid = s.rocketid
    WHERE c.return_date IS NULL
    ORDER BY c.promise_date ASC;
";

$result = $conn->query($query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checked-Out Books</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Checked-Out Books</h1>

    <table border="1">
        <thead>
            <tr>
                <th>Book Title</th>
                <th>Student Name</th>
                <th>Student Phone</th>
                <th>Promised Return Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['book_title']) ?></td>
                        <td><?= htmlspecialchars($row['student_name']) ?></td>
                        <td><?= htmlspecialchars($row['student_phone']) ?></td>
                        <td><?= htmlspecialchars($row['promise_date']) ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">No books are currently checked out.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <ul>
    <br><a href="checkout.php">Checkout a book</a><br>
    <br><a href="return.php">Return a book</a><br>
    <br><a href="home.php">Back to Home</a><br>
    </ul>
</body>
</html>
