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
$bookid = "";
$history = [];

// Handle form submission to fetch history
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookid = $_POST['bookid'];

    if (empty($bookid)) {
        $error_message = "Please select a book.";
    } else {
        // Fetch history for the selected book
        $history_query = "
            SELECT 
                b.title AS book_title, 
                s.name AS student_name,  
                c.promise_date, 
                c.return_date
            FROM checkout c
            JOIN book b ON c.bookid = b.bookid
            JOIN student s ON c.rocketid = s.rocketid
            WHERE c.bookid = ?
            ORDER BY c.promise_date DESC
        ";
        $stmt = $conn->prepare($history_query);
        $stmt->bind_param("i", $bookid);
        $stmt->execute();
        $result = $stmt->get_result();
        $history = $result->fetch_all(MYSQLI_ASSOC);

        if (empty($history)) {
            $error_message = "This book does not have any history.";
        }
    }
}

// Fetch all books for dropdown
$books_query = "SELECT bookid, title FROM book";
$books_result = $conn->query($books_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book History</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Checkout History of a Book</h1>

    <?php if (!empty($error_message)): ?>
        <div class="error"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <form method="POST" action="bookhistory.php">
        <label for="bookid">Select Book:</label>
        <select name="bookid" id="bookid" required>
            <option value="">-- Select Book --</option>
            <?php while ($row = $books_result->fetch_assoc()): ?>
                <option value="<?= $row['bookid'] ?>" <?= $row['bookid'] == $bookid ? 'selected' : '' ?>>
                    <?= htmlspecialchars($row['title']) ?> (ID: <?= htmlspecialchars($row['bookid']) ?>)
                </option>
            <?php endwhile; ?>
        </select>
        <button type="submit">View History</button>
    </form>

    <?php if (!empty($history)): ?>
        <h2>History for Book: <?= htmlspecialchars($history[0]['book_title']) ?></h2>
        <table border="1">
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Promise Date</th>
                    <th>Return Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($history as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['student_name']) ?></td>
                        <td><?= htmlspecialchars($row['promise_date']) ?></td>
                        <td><?= htmlspecialchars($row['return_date'] ?? 'Not Returned') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <a href="home.php">Back to Home</a>
</body>
</html>
