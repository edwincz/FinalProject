<?php
session_start();
//require_once 'db_config.php'; // Include database configuration

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
$book_id = $student_id = $promise_date = "";
$error_message = "";


// Handle form submission for book checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'];
    $book_id = $_POST['book_id'];
    $promise_date = $_POST['promise_date'];

    // Validate inputs
    if (empty($student_id) || empty($book_id) || empty($promise_date)) {
        $error_message = "All fields are required.";
    } else {
        // Check if the book is already checked out
        $check_query = "SELECT * FROM checkout WHERE bookid = ? AND promise_date IS NULL";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("i", $book_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error_message = "This book is already checked out.";
        } else {
            // Record the book checkout
            $insert_query = "INSERT INTO checkout (rocketid, bookid, promise_date) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("iis", $student_id, $book_id, $promise_date);

            if ($stmt->execute()) {
                $success_message = "Book checkout successfully recorded.";
            } else {
                $error_message = "Error recording the checkout. Please try again.";
            }
        }
    }
}

// Fetch students and books for dropdowns
$students_query = "SELECT rocketid, name FROM student WHERE active = 1";
$students_result = $conn->query($students_query);

$books_query = "SELECT bookid, title FROM book WHERE bookid NOT IN (SELECT bookid FROM checkout WHERE promise_date IS NULL)";
$books_result = $conn->query($books_query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout Book</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Checkout a Book</h1>

    <?php if (isset($error_message)): ?>
        <div class="error"><?= $error_message ?></div>
    <?php endif; ?>

    <?php if (isset($success_message)): ?>
        <div class="success"><?= $success_message ?></div>
    <?php endif; ?>

    <form method="POST" action="checkout.php">
        <label for="student_id">Select Student:</label>
        <select name="student_id" id="student_id" required>
            <option value="">-- Select Student --</option>
            <?php while ($student = $students_result->fetch_assoc()): ?>
                <option value="<?= $student['id'] ?>"><?= $student['name'] ?></option>
            <?php endwhile; ?>
        </select>

        <label for="book_id">Select Book:</label>
        <select name="book_id" id="book_id" required>
            <option value="">-- Select Book --</option>
            <?php while ($book = $books_result->fetch_assoc()): ?>
                <option value="<?= $book['id'] ?>"><?= $book['title'] ?></option>
            <?php endwhile; ?>
        </select>

        <label for="promise_date">Promised Return Date:</label>
        <input type="date" name="promise_date" id="promise_date" required>

        <button type="submit">Checkout Book</button>
    </form>

    <a href="home.php">Back to Home</a>
</body>
</html>
