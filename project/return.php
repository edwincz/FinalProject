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

// Initialize variables
$error_message = "";
$success_message = "";

// Handle form submission for returning a book
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookid = $_POST['bookid'];
    $studentid = $_POST['studentid'];

    // Validate inputs
    if (empty($bookid) || empty($studentid)) {
        $error_message = "Both book ID and student name are required.";
    } else {
        // Update the return date in the database
        $return_date = date("Y-m-d"); // Automatically set return date to today
        $update_query = "
            UPDATE checkout 
            SET return_date = ? 
            WHERE bookid = ? AND rocketid = ? AND return_date IS NULL
        ";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("sis", $return_date, $bookid, $studentid);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $success_message = "Book successfully returned and database updated.";
        } else {
            $error_message = "Error: Either the book is not checked out or the student/book combination is incorrect.";
        }
    }
}

// Fetch books and students for dropdowns
$books_query = "
    SELECT b.bookid, b.title 
    FROM book b 
    JOIN checkout c ON b.bookid = c.bookid 
    WHERE c.return_date IS NULL
";
$books_result = $conn->query($books_query);

$students_query = "
    SELECT DISTINCT s.rocketid, s.name 
    FROM student s 
    JOIN checkout c ON s.rocketid = c.rocketid 
    WHERE c.return_date IS NULL
";
$students_result = $conn->query($students_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return a Book</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Return a Book</h1>

    <?php if (!empty($error_message)): ?>
        <div class="error"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <?php if (!empty($success_message)): ?>
        <div class="success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>

    <form method="POST" action="return.php">
        <label for="bookid">Select Book:</label>
        <select name="bookid" id="bookid" required>
            <option value="">-- Select Book --</option>
            <?php while ($row = $books_result->fetch_assoc()): ?>
                <option value="<?= $row['bookid'] ?>">
                    <?= htmlspecialchars($row['title']) ?> (ID: <?= htmlspecialchars($row['bookid']) ?>)
                </option>
            <?php endwhile; ?>
        </select>

        <label for="studentid">Select Student:</label>
        <select name="studentid" id="studentid" required>
            <option value="">-- Select Student --</option>
            <?php while ($row = $students_result->fetch_assoc()): ?>
                <option value="<?= $row['rocketid'] ?>">
                    <?= htmlspecialchars($row['name']) ?> (ID: <?= htmlspecialchars($row['rocketid']) ?>)
                </option>
            <?php endwhile; ?>
        </select>

        <button type="submit">Record Return</button>
    </form>

    <a href="home.php">Back to Home</a>
</body>
</html>
