<?php
session_start();

if (!isset($_SESSION['valid_user'])) {
    header('Location: login.php');
    exit();
}

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

$titleErr = $authorErr = $publisherErr = "";
$title = $author = $publisher = "";
$errcount = 0;

function clean_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    if (empty($_POST['title'])) {
        $titleErr = "Title is required";
        $errcount++;
    } else {
        $title = clean_input($_POST['title']);
    }

    if (empty($_POST['author'])) {
        $authorErr = "Author is required";
        $errcount++;
    } else {
        $author = clean_input($_POST['author']);
    }

    if (empty($_POST['publisher'])) {
        $publisherErr = "Publisher is required";
        $errcount++;
    } else {
        $publisher = clean_input($_POST['publisher']);
    }

    if ($errcount == 0) {
        $stmt = $conn->prepare("INSERT INTO book (title, author, publisher, active) VALUES (?, ?, ?, TRUE)");
        if ($stmt === false) {
            die("Prepare failed: " . htmlspecialchars($conn->error));
        }

        $stmt->bind_param("sss", $title, $author, $publisher);

        if ($stmt->execute()) {
            header('Location: books.php');
            exit();
        } else {
            echo "Error: " . htmlspecialchars($stmt->error);
        }

        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add New Book</title>
    <style>
        .error { color: #FF0000; }
        /* Your CSS styles */
    </style>
</head>
<body>
    <h1>Add New Book</h1>
    <p><span class="error">* Required field</span></p>
    <form action="add_book.php" method="POST">
        <label for="title">Title:</label><br/>
        <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($title); ?>" />
        <span class="error">* <?php echo $titleErr; ?></span><br/><br/>

        <label for="author">Author:</label><br/>
        <input type="text" name="author" id="author" value="<?php echo htmlspecialchars($author); ?>" />
        <span class="error">* <?php echo $authorErr; ?></span><br/><br/>

        <label for="publisher">Publisher:</label><br/>
        <input type="text" name="publisher" id="publisher" value="<?php echo htmlspecialchars($publisher); ?>" />
        <span class="error">* <?php echo $publisherErr; ?></span><br/><br/>

        <input type="submit" name="submit" value="Add Book" />
    </form>
    <p><a href="books.php">Back to Books</a></p>
</body>
</html>
