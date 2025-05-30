<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'seller') {
    header("Location: index.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "cafe_db");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['order_items'])) $_SESSION['order_items'] = [];
if (!isset($_SESSION['total_amount'])) $_SESSION['total_amount'] = 0;

$order_items = $_SESSION['order_items'];
$total_amount = $_SESSION['total_amount'];

$categories_result = $conn->query("SELECT DISTINCT category_name FROM items");

$selected_category = "";
if (isset($_POST['filter_category'])) {
    $selected_category = $_POST['category'];
    $items_result = $conn->query("SELECT * FROM items WHERE category_name='$selected_category'");
} else {
    $items_result = $conn->query("SELECT * FROM items");
}

if (isset($_POST['refresh_list'])) {
    $selected_category = "";
    $items_result = $conn->query("SELECT * FROM items");
}

if (isset($_POST['add_to_bill'])) {
    $item_id = $_POST['item_id'];
    $quantity = $_POST['quantity'];
    $seller_name = $_SESSION['username'];

    $item_result = $conn->query("SELECT * FROM items WHERE id=$item_id");
    $item = $item_result->fetch_assoc();

    if ($item['quantity'] >= $quantity) {
        $new_quantity = $item['quantity'] - $quantity;
        $conn->query("UPDATE items SET quantity=$new_quantity WHERE id=$item_id");

        $total = $item['price'] * $quantity;
        $_SESSION['order_items'][] = [
            'id' => $item['id'],
            'name' => $item['name'],
            'category' => $item['category_name'],
            'price' => $item['price'],
            'quantity' => $quantity,
            'total' => $total
        ];

        $_SESSION['total_amount'] += $total;

        header("Location: seller_dashboard.php");
        exit();
    } else {
        echo "<script>alert('Insufficient stock!');</script>";
    }
}

if (isset($_POST['print_bill'])) {
    $_SESSION['print_mode'] = true;
    header("Location: receipt.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Seller Dashboard</title>
    <style>
        body { font-family: Arial; background: #f0f0f0; }
        .container { width: 90%; margin: auto; background: white; padding: 20px; margin-top: 20px; border-radius: 10px; }
        table { width: 100%; margin-top: 20px; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 10px; }
        th { background: #444; color: white; }
        input, select, button { padding: 10px; margin: 5px; border-radius: 5px; }
        button { background: #444; color: white; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
    <div class="header">
            <h1>Welcome,<?php echo $_SESSION['username']; ?>!👋</h1>
        </div>
        
        <a href="index.php"><button>Go Back</button></a>

        <form method="POST">
            <select name="category">
                <option value="">Select Category</option>
                <?php while ($category = $categories_result->fetch_assoc()): ?>
                    <option value="<?php echo $category['category_name']; ?>" <?php echo $selected_category == $category['category_name'] ? 'selected' : ''; ?>>
                        <?php echo $category['category_name']; ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <button type="submit" name="filter_category">Filter</button>
            <button type="submit" name="refresh_list">Refresh</button>
        </form>

        <table>
            <tr>
                <th>ID</th><th>Name</th><th>Category</th><th>Price</th><th>Quantity</th><th>Action</th>
            </tr>
            <?php while ($item = $items_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $item['id']; ?></td>
                    <td><?php echo $item['name']; ?></td>
                    <td><?php echo $item['category_name']; ?></td>
                    <td><?php echo $item['price']; ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                            <input type="number" name="quantity" required>
                            <button type="submit" name="add_to_bill">Add to Bill</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>

        <h3>Your Order</h3>
        <table>
            <tr><th>ID</th><th>Name</th><th>Category</th><th>Price</th><th>Qty</th><th>Total</th></tr>
            <?php foreach ($_SESSION['order_items'] as $order): ?>
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

        <form method="POST">
            <button type="submit" name="print_bill">Print Receipt</button>
            <label>Total: Ksh <?php echo $_SESSION['total_amount']; ?></label>
        </form>
    </div>
</body>
</html>
