<?php
session_start();

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli("localhost", "root", "", "cafe_db");

// Connection check
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Login process
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Fetch user details
    $query = "SELECT * FROM users WHERE username='$username'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Verify password using password_verify()
        if (password_verify($password, $row['password'])) {
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $row['role'];

            // Redirect based on role
            if ($row['role'] == 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: seller_dashboard.php");
            }
            exit();
        } else {
            echo "<script>alert('Invalid password!'); window.location.href='index.php';</script>";
        }
    } else {
        echo "<script>alert('Invalid username!'); window.location.href='index.php';</script>";
    }
}
?>