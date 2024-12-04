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
$student = null;

function clean_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['submit'])) {
    $rocketid = $_POST['rocketid'];

    if (empty($_POST["name"])) {
        $nameErr = "Name is required";
    } else {
        $name = clean_input($_POST["name"]);
    }

    if (empty($_POST["phone"])) {
        $phoneErr = "Phone is required";
    } else {
        $phone = clean_input($_POST["phone"]);
    }

    if (empty($_POST["address"])) {
        $addressErr = "Address is required";
    } else {
        $address = clean_input($_POST["address"]);
    }

    if (!empty($rocketid) && empty($nameErr) && empty($phoneErr) && empty($addressErr)) {
        $stmt = $conn->prepare("UPDATE student SET name = ?, phone = ?, address = ? WHERE rocketid = ?");
        if ($stmt === false) {
            die("Prepare failed: " . htmlspecialchars($conn->error));
        }

        $stmt->bind_param("ssss", $name, $phone, $address, $rocketid);

        if ($stmt->execute()) {
            header('Location: students.php');
            exit();
        } else {
            echo "Error: " . htmlspecialchars($stmt->error);
        }

        $stmt->close();
    }

    $conn->close();
}

if ($_SERVER['REQUEST_METHOD'] == "GET" && isset($_GET['rocketid'])) {
    $rocketid = $_GET['rocketid'];

    $stmt = $conn->prepare("SELECT rocketid, name, phone, address FROM student WHERE rocketid = ?");
    if ($stmt === false) {
        die("Prepare failed: " . htmlspecialchars($conn->error));
    }

    $stmt->bind_param("s", $rocketid);

    $stmt->execute();
    $resultset = $stmt->get_result();
    $student = $resultset->fetch_assoc();

    if ($student) {
        $name = $student['name'];
        $phone = $student['phone'];
        $address = $student['address'];
    } else {
        echo "<h2>Sorry, student not found.</h2>";
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Student</title>
    <style>
        .error { color: #FF0000; }
        /* Your CSS styles */
    </style>
</head>
<body>
    <h1>Edit Student</h1>
    <?php
    if (is_null($student) && $_SERVER['REQUEST_METHOD'] == "GET") {
        echo "<h2>Sorry, student not found.</h2>";
    } else {
    ?>
    <p><span class="error">* Required field</span></p>
    <form action="edit_student.php" method="POST">
        <input type="hidden" name="rocketid" value="<?php echo htmlspecialchars($rocketid); ?>" />

        <label for="name">Name:</label><br/>
        <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($name); ?>" />
        <span class="error">* <?php echo $nameErr; ?></span><br/><br/>

        <label for="phone">Phone:</label><br/>
        <input type="text" name="phone" id="phone" value="<?php echo htmlspecialchars($phone); ?>" />
        <span class="error">* <?php echo $phoneErr; ?></span><br/><br/>

        <label for="address">Address:</label><br/>
        <input type="text" name="address" id="address" value="<?php echo htmlspecialchars($address); ?>" />
        <span class="error">* <?php echo $addressErr; ?></span><br/><br/>

        <input type="submit" name="submit" value="Update Student" />
    </form>
    <?php
    }
    ?>
    <p><a href="students.php">Back to Students</a></p>
</body>
</html>
