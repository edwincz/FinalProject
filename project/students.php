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

// Fetch all students, both active and inactive
$sql = "SELECT rocketid, name, phone, address, active FROM student";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Students</title>
    <style>
        /* Your CSS styles */
        table {
            border-collapse: collapse;
        }
        th, td {
            padding: 8px;
            border: 1px solid #000;
        }
        th {
            background-color: #f2f2f2;
        }
        label {
            font-weight: bold;
        }
        select, input[type="submit"] {
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <h1>Manage Students</h1>
    <p><a href="add_student.php">Add New Student</a></p>
    <table>
        <tr>
            <th>Rocket ID</th>
            <th>Name</th>
            <th>Phone</th>
            <th>Address</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Determine if the student is active or inactive
                $isActive = $row['active'] ? 1 : 0;
                
                echo "<tr>";
                echo "<td>".htmlspecialchars($row['rocketid'])."</td>";
                echo "<td>".htmlspecialchars($row['name'])."</td>";
                echo "<td>".htmlspecialchars($row['phone'])."</td>";
                echo "<td>".htmlspecialchars($row['address'])."</td>";

                // Status dropdown form
                echo "<td>";
                echo '<form method="POST" action="update_student_status.php">';
                echo '<input type="hidden" name="rocketid" value="'.htmlspecialchars($row['rocketid']).'">';
                echo '<select name="active">';
                echo '<option value="1"'.($isActive ? ' selected' : '').'>Active</option>';
                echo '<option value="0"'.(!$isActive ? ' selected' : '').'>Inactive</option>';
                echo '</select>';
                echo ' <input type="submit" value="Save" />';
                echo '</form>';
                echo "</td>";

                // Edit actions only
                echo "<td>";
                echo '<a href="edit_student.php?rocketid='.htmlspecialchars($row['rocketid']).'">Edit</a>';
                // Removed the inactivate link as requested
                echo "</td>";

                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='6'>No students found.</td></tr>";
        }
        ?>
    </table>
    <p><a href="home.php">Back to Home</a></p>
</body>
</html>
<?php
$conn->close();
?>
