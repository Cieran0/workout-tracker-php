<?php

session_start();

// Get user inputs from POST request
$user_name = $_POST["username"];
$password_hash =  $_POST["password"]; // Securely hash the password using SHA256

// Connect to SQLite database
$db = new SQLite3('workout.db');

try {
    error_log("Username: " . $user_name);
    error_log("Password Hash: " . $password_hash);
    // Prepare the SQL query to find the user
    $stmt = $db->prepare("SELECT UserID FROM User WHERE Username = :username AND PasswordSHA256 = :password");
    if ($stmt === false) {
        throw new Exception("Failed to prepare statement: " . $db->lastErrorMsg());
    }
    $stmt->bindValue(':username', $user_name, SQLITE3_TEXT);
    $stmt->bindValue(':password', $password_hash, SQLITE3_TEXT);

    // Execute the query
    $result = $stmt->execute();

    // Check if a row is returned
    $row = $result->fetchArray(SQLITE3_ASSOC);
    if ($row) {
        // User found, set session variables
        $_SESSION['user_id'] = $row['UserID'];
        error_log("User found: " . $_SESSION['user_id']);
        $_SESSION['username'] = $user_name;

        // Redirect to the main application or dashboard
        header("Location: index.php");
    } else {
        error_log("No User found");
        // User not found, redirect back to login with an error
        header("Location: login.php?login_failed=true");
    }

} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    // Redirect back to login in case of an exception
    header("Location: login.php?login_failed=true");
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workout Tracker - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="dark:bg-gray-900 h-screen flex items-center justify-center">

    <div class="bg-gray-800 p-8 rounded-lg shadow-lg w-96">
        <h2 class="text-2xl font-bold text-white text-center mb-6">Loading...</h2>
    </div>

</body>

</html>
