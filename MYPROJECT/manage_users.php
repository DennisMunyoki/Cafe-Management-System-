<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "cafe_db");

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Logout process
if (isset($_POST['logout'])) {
    header("Location: admin_dashboard.php");
    exit();
}

// Fetch user details for editing
$user_data = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $result = $conn->query("SELECT * FROM users WHERE id=$edit_id");
    $user_data = $result->fetch_assoc();
}

// Insert user
if (isset($_POST['insert'])) {
    if (!empty($_POST['username']) && !empty($_POST['password']) && !empty($_POST['role'])) {
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $role = $_POST['role'];

        $conn->query("INSERT INTO users (username, password, role) VALUES ('$username', '$password', '$role')");
    } else {
        echo "<script>alert('Please fill out all fields!');</script>";
    }
}

// Delete user
if (isset($_POST['delete'])) {
    if (!empty($_POST['id'])) {
        $id = $_POST['id'];
        $conn->query("DELETE FROM users WHERE id=$id");
    } else {
        echo "<script>alert('Please provide a valid ID!');</script>";
    }
}

// Update user details
if (isset($_POST['update'])) {
    if (!empty($_POST['id']) && !empty($_POST['username']) && !empty($_POST['role'])) {
        $id = $_POST['id'];
        $username = $_POST['username'];
        $role = $_POST['role'];
        $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_BCRYPT) : null;

        $query = "UPDATE users SET username='$username', role='$role'";
        if ($password) {
            $query .= ", password='$password'";
        }
        $query .= " WHERE id=$id";

        $conn->query($query);
    } else {
        echo "<script>alert('Please fill out all required fields!');</script>";
    }
}

// Search users
$search_result = [];
if (isset($_POST['search']) && !empty($_POST['search_value'])) {
    $search_value = $_POST['search_value'];
    $search_query = $conn->query("SELECT * FROM users WHERE username LIKE '%$search_value%'");
    while ($row = $search_query->fetch_assoc()) {
        $search_result[] = $row;
    }
} else {
    // Fetch all users
    $all_users = $conn->query("SELECT * FROM users");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #ff9966, #ff5e62);
            margin: 0;
            padding: 0;
        }
        .container {
            width: 90%;
            margin: auto;
            background: white;
            margin-top: 20px;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #ff5e62;
            color: white;
        }
        form input, button {
            padding: 10px;
            margin: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        button {
            background: #ff5e62;
            color: white;
            cursor: pointer;
        }
        button:hover {
            transform: scale(1.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Manage Users</h1>

        <!-- Logout Button -->
        <form method="POST" style="text-align: right;">
            <button type="submit" name="logout">Go Back</button>
        </form>

        <!-- User Form -->
        <form method="POST">
            <input type="text" name="id" placeholder="ID (for Update/Delete)" value="<?php echo $user_data['id'] ?? ''; ?>" readonly>
            <input type="text" name="username" placeholder="Username" value="<?php echo $user_data['username'] ?? ''; ?>">
            <input type="password" name="password" placeholder="Password">
            <input type="text" name="role" placeholder="Role (admin/seller)" value="<?php echo $user_data['role'] ?? ''; ?>">
            <button type="submit" name="insert">Insert</button>
            <button type="submit" name="delete">Delete</button>
            <button type="submit" name="update">Update</button>
            <input type="reset" value="Reset">
        </form>

        <!-- Search Form -->
        <form method="POST">
            <input type="text" name="search_value" placeholder="Search username">
            <button type="submit" name="search">Search</button>
        </form>

        <!-- Display Users -->
        <table>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
            <?php if (!empty($search_result)): ?>
                <?php foreach ($search_result as $row): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['username']; ?></td>
                        <td><?php echo $row['role']; ?></td>
                        <td>
                            <a href="manage_users.php?edit_id=<?php echo $row['id']; ?>" style="color: #ff5e62;">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <?php while ($row = $all_users->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['username']; ?></td>
                        <td><?php echo $row['role']; ?></td>
                        <td>
                            <a href="manage_users.php?edit_id=<?php echo $row['id']; ?>" style="color: #ff5e62;">Edit</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </table>
    </div>
</body>
</html>