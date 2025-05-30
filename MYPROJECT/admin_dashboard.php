<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admin_styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #ff9966, #ff5e62);
            margin: 0;
            padding: 0;
        }
        .dashboard-container {
            width: 90%;
            margin: auto;
            margin-top: 30px;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #ff5e62;
        }
        .feature-section {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }
        .card {
            background: linear-gradient(to right, #ff9966, #ff5e62);
            width: 250px;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            text-align: center;
            color: white;
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: scale(1.1);
        }
        .card img {
            width: 50px;
            margin-bottom: 10px;
        }
        .logout {
            display: block;
            margin: 20px auto;
            width: 150px;
            background: black;
            color: white;
            padding: 10px;
            text-align: center;
            border-radius: 5px;
            text-decoration: none;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: background 0.2s ease;
        }
        .logout:hover {
            background: #ff5e62;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="header">
            <h1>Welcome,<?php echo $_SESSION['username']; ?>!👋</h1>
        </div>
        <div class="feature-section">
            <!-- Manage Users -->
            <div class="card">
                <img src="manage_users.png" alt="Manage Users">
                <h2>Manage Users</h2>
                <p><a href="manage_users.php" style="color:white; text-decoration:none;">Go to User Management</a></p>
            </div>

            <!-- Manage Items -->
            <div class="card">
                <img src="manage_items.png" alt="Manage Items">
                <h2>Manage Items</h2>
                <p><a href="manage_items.php" style="color:white; text-decoration:none;">Go to Item Management</a></p>
            </div>

            <!-- View Orders -->
            <div class="card">
                <img src="orders.png" alt="View Orders">
                <h2>View Orders</h2>
                <p><a href="view_orders.php" style="color:white; text-decoration:none;">Track and Manage Orders</a></p>
            </div>

            <!-- Reports -->
            <div class="card">
                <img src="reports.png" alt="Reports">
                <h2>Reports</h2>
                <p><a href="view_reports.php" style="color:white; text-decoration:none;">View Sales Statistics</a></p>
            </div>
        </div>

        <!-- Logout -->
        <a href="logout.php" class="logout">Logout</a>
    </div>
</body>
</html>