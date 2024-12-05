<?php
session_start();

function clean_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

if (isset($_POST['userid']) && isset($_POST['password'])) {
    $userid = clean_input($_POST['userid']);
    $password = clean_input($_POST['password']);
    $isAuthenticated = false;

    $inifile = parse_ini_file("myproperties.ini", true);
    $dbhost = $inifile["DB"]["DBHOST"];
    $dbuser = $inifile["DB"]["DBUSER"];
    $dbpass = $inifile["DB"]["DBPASS"];
    $dbname = $inifile["DB"]["DBNAME"];

    $connection = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
    if ($connection->connect_error) {
        die("Connection failed: " . htmlspecialchars($connection->connect_error));
    }

    $stmt = $connection->prepare("SELECT passwordhash FROM user_authentication WHERE username = ?");
    $stmt->bind_param("s", $userid);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($passwordhash);
        $stmt->fetch();

        if (password_verify($password, $passwordhash)) {
            $isAuthenticated = true;
        }
    }

    $stmt->close();
    $connection->close();

    if ($isAuthenticated) {
        $_SESSION['valid_user'] = $userid;
        header('Location: home.php'); // Redirect to home page upon successful login
        exit();
    } else {
        $loginError = "Invalid username or password.";
        unset($_SESSION['valid_user']);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login Page</title>
    <style type="text/css">
        /* Your CSS styles */
    </style>
</head>
<body>
    <h1>Login</h1>

    <?php
    if (isset($loginError)) {
        echo '<p style="color:red;">' . htmlspecialchars($loginError) . '</p>';
    }
    ?>

    <form action="login.php" method="post">
        <p>
            <label for="userid">UserID:</label>
            <input type="text" name="userid" id="userid" size="30" required value="<?php echo isset($userid) ? htmlspecialchars($userid) : ''; ?>" />
        </p>
        <p>
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" size="30" required />
        </p>
        <button type="submit" name="login">Login</button>
    </form>

    <p><a href="changepassword.php">Change Password</a></p>
</body>
</html>
