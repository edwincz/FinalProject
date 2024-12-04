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

$sql = "SELECT bookid, title, author, publisher FROM book WHERE active = TRUE";
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Books</title>
    <style>
        /* Your CSS styles */
    </style>
</head>
<body>
    <h1>Manage Books</h1>
    <p><a href="add_book.php">Add New Book</a></p>
    <table border="1">
        <tr>
            <th>Title</th><th>Author</th><th>Publisher</th><th>Actions</th>
        </tr>
        <?php
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>".htmlspecialchars($row['title'])."</td>";
                echo "<td>".htmlspecialchars($row['author'])."</td>";
                echo "<td>".htmlspecialchars($row['publisher'])."</td>";
                echo "<td>";
                echo '<a href="edit_book.php?bookid='.$row['bookid'].'">Edit</a> | ';
                echo '<a href="delete_book.php?bookid='.$row['bookid'].'">Remove</a>';
                echo "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='4'>No books found.</td></tr>";
        }
        ?>
    </table>
    <p><a href="home.php">Back to Home</a></p>
</body>
</html>
<?php
$conn->close();
?>
