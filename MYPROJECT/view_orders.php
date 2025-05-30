<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "cafe_db");

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Fetch all orders
$orders_result = $conn->query("SELECT * FROM orders ORDER BY order_date DESC");

// Filter orders by date or seller
if (isset($_POST['filter_orders'])) {
    $filter_seller = $_POST['filter_seller'];
    $filter_date = $_POST['filter_date'];

    $query = "SELECT * FROM orders WHERE 1=1";
    if (!empty($filter_seller)) {
        $query .= " AND seller = '$filter_seller'";
    }
    if (!empty($filter_date)) {
        $query .= " AND DATE(order_date) = '$filter_date'";
    }
    $query .= " ORDER BY order_date DESC";

    $orders_result = $conn->query($query);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Track Orders</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #74ebd5, #acb6e5);
            margin: 0;
            padding: 0;
        }
        .container {
            width: 90%;
            margin: auto;
            margin-top: 20px;
            background: white;
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
            background-color: #74ebd5;
            color: black;
        }
        form input, select, button {
            padding: 10px;
            margin: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        button {
            background: #74ebd5;
            color: black;
            cursor: pointer;
        }
        button:hover {
            transform: scale(1.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Track and View Orders</h1>

        <!-- Go Back to Admin Dashboard -->
        <a href="admin_dashboard.php" style="text-decoration: none;">
            <button style="background: black; color: white; padding: 10px 20px;">Go Back</button>
        </a>

        <!-- Filter Options -->
        <form method="POST">
            <input type="date" name="filter_date" placeholder="Filter by Date">
            <input type="text" name="filter_seller" placeholder="Filter by Seller">
            <button type="submit" name="filter_orders">Filter</button>
        </form>

        <!-- Orders Table -->
        <table>
            <tr>
                <th>Order ID</th>
                <th>Order Date</th>
                <th>Order Amount</th>
                <th>Seller</th>
            </tr>
            <?php while ($order = $orders_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $order['order_id']; ?></td>
                    <td><?php echo $order['order_date']; ?></td>
                    <td><?php echo $order['order_amount']; ?></td>
                    <td><?php echo $order['seller']; ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>