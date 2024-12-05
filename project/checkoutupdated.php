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
$rocketid = $bookid = $promise_date = "";
$error_message = "";
$success_message = "";

function clean_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Handle form submission for book checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rocketid = clean_input($_POST['rocketid']);
    $bookid = clean_input($_POST['bookid']);
    $promise_date = clean_input($_POST['promise_date']);

    // Validate inputs
    if (empty($rocketid) || empty($bookid) || empty($promise_date)) {
        $error_message = "All fields are required.";
    } else {
        // Check if the book is already checked out
        $check_query = "SELECT * FROM checkout WHERE bookid = ? AND return_date IS NULL";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("i", $bookid);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error_message = "This book is already checked out.";
        } else {
            // Record the book checkout
            $insert_query = "INSERT INTO checkout (rocketid, bookid, promise_date) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("sis", $rocketid, $bookid, $promise_date);

            if ($stmt->execute()) {
                $success_message = "Book checkout successfully recorded.";
                // Clear form fields after successful submission
                $rocketid = $bookid = $promise_date = "";
            } else {
                $error_message = "Error recording the checkout. Please try again.";
            }
        }
    }
}

// Fetch active students for dropdown
$students = [];
$students_query = "SELECT rocketid, name FROM student WHERE active = TRUE";
$students_result = $conn->query($students_query);
if ($students_result && $students_result->num_rows > 0) {
    while ($row = $students_result->fetch_assoc()) {
        $students[] = $row;
    }
}

// Fetch available books for dropdown
$books = [];
$books_query = "SELECT bookid, title FROM book WHERE active = TRUE AND bookid NOT IN (SELECT bookid FROM checkout WHERE return_date IS NULL)";
$books_result = $conn->query($books_query);
if ($books_result && $books_result->num_rows > 0) {
    while ($row = $books_result->fetch_assoc()) {
        $books[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout Book</title>
    <style>
        /* Add your CSS styles here */
        .error { color: red; }
        .success { color: green; }
        label { display: block; margin-top: 10px; }
        select, input[type="date"], button { margin-top: 5px; }
    </style>
</head>
<body>
    <h1>Checkout a Book</h1>

    <?php if (!empty($error_message)): ?>
        <div class="error"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <?php if (!empty($success_message)): ?>
        <div class="success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>

    <form method="POST" action="checkout.php">
        <label for="rocketid">Select Student:</label>
        <select name="rocketid" id="rocketid" required>
            <option value="">-- Select Student --</option>
            <?php foreach ($students as $student): ?>
                <option value="<?= htmlspecialchars($student['rocketid']) ?>" <?= ($student['rocketid'] == $rocketid) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($student['name']) ?> (<?= htmlspecialchars($student['rocketid']) ?>)
                </option>
            <?php endforeach; ?>
        </select>

        <label for="bookid">Select Book:</label>
        <select name="bookid" id="bookid" required>
            <option value="">-- Select Book --</option>
            <?php foreach ($books as $book): ?>
                <option value="<?= htmlspecialchars($book['bookid']) ?>" <?= ($book['bookid'] == $bookid) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($book['title']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="promise_date">Promised Return Date:</label>
        <input type="date" name="promise_date" id="promise_date" required value="<?= htmlspecialchars($promise_date) ?>">

        <button type="submit">Checkout Book</button>
    </form>

    <p><a href="checkouts.php">Back to Checkouts</a></p>
    <p><a href="home.php">Back to Home</a></p>
</body>
</html>
