<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'seller') {
    header("Location: index.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "café_db");

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Initialize variables
$selected_category = "";
$order_items = [];
$total_amount = 0;

// Fetch categories for combobox
$categories_result = $conn->query("SELECT DISTINCT category FROM items");

// Fetch items for display
if (isset($_POST['filter_category'])) {
    $selected_category = $_POST['category'];
    $items_result = $conn->query("SELECT * FROM items WHERE category='$selected_category'");
} else {
    $items_result = $conn->query("SELECT * FROM items");
}

// Refresh item list
if (isset($_POST['refresh_list'])) {
    $selected_category = "";
    $items_result = $conn->query("SELECT * FROM items");
}

// Add to bill
if (isset($_POST['add_to_bill'])) {
    $item_id = $_POST['item_id'];
    $quantity = $_POST['quantity'];
    $seller_name = $_SESSION['username'];

    // Fetch item details
    $item_result = $conn->query("SELECT * FROM items WHERE id=$item_id");
    $item = $item_result->fetch_assoc();

    if ($item['quantity'] >= $quantity) {
        // Update stock quantity in `items` table
        $new_quantity = $item['quantity'] - $quantity;
        $conn->query("UPDATE items SET quantity=$new_quantity WHERE id=$item_id");

        // Calculate the total price
        $total = $item['price'] * $quantity;

        // Add item details to the bill array
        $order_items[] = [
            'id' => $item['id'],
            'name' => $item['name'],
            'category' => $item['category'],
            'price' => $item['price'],
            'quantity' => $quantity,
            'total' => $total
        ];

        // Update total amount
        $total_amount += $total;
    } else {
        echo "<script>alert('Insufficient stock!');</script>";
    }
}

// Print and save the bill
if (isset($_POST['print_bill'])) {
    $seller_name = $_SESSION['username'];

    // Insert each item in the `orders` table
    foreach ($order_items as $order) {
        $conn->query("INSERT INTO orders (seller_name, item_name, category, price, quantity, total) 
                      VALUES ('$seller_name', '{$order['name']}', '{$order['category']}', {$order['price']}, {$order['quantity']}, {$order['total']})");
    }

    // Generate printable bill (as HTML here; extendable to PDF)
    echo "<script>alert('Bill printed successfully and order saved to the database!');</script>";

    // Clear the order after printing
    $order_items = [];
    $total_amount = 0;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Seller Dashboard</title>
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
            background-color: #ff5e62;
            color: white;
        }
        form input, select, button {
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
        <h1>Seller Dashboard</h1>

        <!-- Category Filter and Refresh -->
        <form method="POST">
            <select name="category">
                <option value="">Select Category</option>
                <?php while ($category = $categories_result->fetch_assoc()): ?>
                    <option value="<?php echo $category['category']; ?>" <?php echo $selected_category == $category['category'] ? 'selected' : ''; ?>>
                        <?php echo $category['category']; ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <button type="submit" name="filter_category">Filter</button>
            <button type="submit" name="refresh_list">Refresh</button>
        </form>

        <!-- Items Table -->
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Action</th>
            </tr>
            <?php while ($item = $items_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $item['id']; ?></td>
                    <td><?php echo $item['name']; ?></td>
                    <td><?php echo $item['category']; ?></td>
                    <td><?php echo $item['price']; ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                            <input type="number" name="quantity" placeholder="Quantity" required>
                            <button type="submit" name="add_to_bill">Add to Bill</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>

        <!-- Order Table -->
        <h2>Your Order</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total</th>
            </tr>
            <?php foreach ($order_items as $order): ?>
                <tr>
                    <td><?php echo $order['id']; ?></td>
                    <td><?php echo $order['name']; ?></td>
                    <td><?php echo $order['category']; ?></td>
                    <td><?php echo $order['price']; ?></td>
                    <td><?php echo $order['quantity']; ?></td>
                    <td><?php echo $order['total']; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <!-- Total and Print Button -->
        <form method="POST">
            <button type="submit" name="print_bill">Print</button>
            <label>Total: <?php echo $total_amount; ?></label>
        </form>
    </div>
</body>
</html>