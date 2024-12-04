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
$bookid = $title = $author = $publisher = "";
$book = null;

function clean_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['submit'])) {
    $bookid = $_POST['bookid'];

    if (empty($_POST["title"])) {
        $titleErr = "Title is required";
    } else {
        $title = clean_input($_POST["title"]);
    }

    if (empty($_POST["author"])) {
        $authorErr = "Author is required";
    } else {
        $author = clean_input($_POST["author"]);
    }

    if (empty($_POST["publisher"])) {
        $publisherErr = "Publisher is required";
    } else {
        $publisher = clean_input($_POST["publisher"]);
    }

    if (!empty($bookid) && empty($titleErr) && empty($authorErr) && empty($publisherErr)) {
        $stmt = $conn->prepare("UPDATE book SET title = ?, author = ?, publisher = ? WHERE bookid = ?");
        if ($stmt === false) {
            die("Prepare failed: " . htmlspecialchars($conn->error));
        }

        $stmt->bind_param("sssi", $title, $author, $publisher, $bookid);

        if ($stmt->execute()) {
            header('Location: books.php');
            exit();
        } else {
            echo "Error: " . htmlspecialchars($stmt->error);
        }

        $stmt->close();
    }

    $conn->close();
}

if ($_SERVER['REQUEST_METHOD'] == "GET" && isset($_GET['bookid'])) {
    $bookid = $_GET['bookid'];

    $stmt = $conn->prepare("SELECT bookid, title, author, publisher FROM book WHERE bookid = ?");
    if ($stmt === false) {
        die("Prepare failed: " . htmlspecialchars($conn->error));
    }

    $stmt->bind_param("i", $bookid);

    $stmt->execute();
    $resultset = $stmt->get_result();
    $book = $resultset->fetch_assoc();

    if ($book) {
        $title = $book['title'];
        $author = $book['author'];
        $publisher = $book['publisher'];
    } else {
        echo "<h2>Sorry, book not found.</h2>";
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Book</title>
    <style>
        .error { color: #FF0000; }
        /* Your CSS styles */
    </style>
</head>
<body>
    <h1>Edit Book</h1>
    <?php
    if (is_null($book) && $_SERVER['REQUEST_METHOD'] == "GET") {
        echo "<h2>Sorry, book not found.</h2>";
    } else {
    ?>
    <p><span class="error">* Required field</span></p>
    <form action="edit_book.php" method="POST">
        <input type="hidden" name="bookid" value="<?php echo htmlspecialchars($bookid); ?>" />

        <label for="title">Title:</label><br/>
        <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($title); ?>" />
        <span class="error">* <?php echo $titleErr; ?></span><br/><br/>

        <label for="author">Author:</label><br/>
        <input type="text" name="author" id="author" value="<?php echo htmlspecialchars($author); ?>" />
        <span class="error">* <?php echo $authorErr; ?></span><br/><br/>

        <label for="publisher">Publisher:</label><br/>
        <input type="text" name="publisher" id="publisher" value="<?php echo htmlspecialchars($publisher); ?>" />
        <span class="error">* <?php echo $publisherErr; ?></span><br/><br/>

        <input type="submit" name="submit" value="Update Book" />
    </form>
    <?php
    }
    ?>
    <p><a href="books.php">Back to Books</a></p>
</body>
</html>
