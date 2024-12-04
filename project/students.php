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

$sql = "SELECT rocketid, name, phone, address FROM student WHERE active = TRUE";
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Students</title>
    <style>
        /* Your CSS styles */
    </style>
</head>
<body>
    <h1>Manage Students</h1>
    <p><a href="add_student.php">Add New Student</a></p>
    <table border="1">
        <tr>
            <th>Rocket ID</th><th>Name</th><th>Phone</th><th>Address</th><th>Actions</th>
        </tr>
        <?php
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>".htmlspecialchars($row['rocketid'])."</td>";
                echo "<td>".htmlspecialchars($row['name'])."</td>";
                echo "<td>".htmlspecialchars($row['phone'])."</td>";
                echo "<td>".htmlspecialchars($row['address'])."</td>";
                echo "<td>";
                echo '<a href="edit_student.php?rocketid='.$row['rocketid'].'">Edit</a> | ';
                echo '<a href="delete_student.php?rocketid='.$row['rocketid'].'">Inactivate</a>';
                echo "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No students found.</td></tr>";
        }
        ?>
    </table>
    <p><a href="home.php">Back to Home</a></p>
</body>
</html>
<?php
$conn->close();
?>
