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

$rocketidErr = $nameErr = $phoneErr = $addressErr = "";
$rocketid = $name = $phone = $address = "";
$errcount = 0;

function clean_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    if (empty($_POST['rocketid'])) {
        $rocketidErr = "Rocket ID is required";
        $errcount++;
    } else {
        $rocketid = clean_input($_POST['rocketid']);
    }

    if (empty($_POST['name'])) {
        $nameErr = "Name is required";
        $errcount++;
    } else {
        $name = clean_input($_POST['name']);
    }

    if (empty($_POST['phone'])) {
        $phoneErr = "Phone is required";
        $errcount++;
    } else {
        $phone = clean_input($_POST['phone']);
    }

    if (empty($_POST['address'])) {
        $addressErr = "Address is required";
        $errcount++;
    } else {
        $address = clean_input($_POST['address']);
    }

    if ($errcount == 0) {
        $stmt = $conn->prepare("INSERT INTO student (rocketid, name, phone, address, active) VALUES (?, ?, ?, ?, TRUE)");
        if ($stmt === false) {
            die("Prepare failed: " . htmlspecialchars($conn->error));
        }

        $stmt->bind_param("ssss", $rocketid, $name, $phone, $address);

        if ($stmt->execute()) {
            header('Location: students.php');
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
    <title>Add New Student</title>
    <style>
        .error { color: #FF0000; }
        /* Your CSS styles */
    </style>
</head>
<body>
    <h1>Add New Student</h1>
    <p><span class="error">* Required field</span></p>
    <form action="add_student.php" method="POST">
        <label for="rocketid">Rocket ID:</label><br/>
        <input type="text" name="rocketid" id="rocketid" value="<?php echo htmlspecialchars($rocketid); ?>" />
        <span class="error">* <?php echo $rocketidErr; ?></span><br/><br/>

        <label for="name">Name:</label><br/>
        <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($name); ?>" />
        <span class="error">* <?php echo $nameErr; ?></span><br/><br/>

        <label for="phone">Phone:</label><br/>
        <input type="text" name="phone" id="phone" value="<?php echo htmlspecialchars($phone); ?>" />
        <span class="error">* <?php echo $phoneErr; ?></span><br/><br/>

        <label for="address">Address:</label><br/>
        <input type="text" name="address" id="address" value="<?php echo htmlspecialchars($address); ?>" />
        <span class="error">* <?php echo $addressErr; ?></span><br/><br/>

        <input type="submit" name="submit" value="Add Student" />
    </form>
    <p><a href="students.php">Back to Students</a></p>
</body>
</html>
