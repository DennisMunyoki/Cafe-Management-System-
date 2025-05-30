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

// Initialize form variables
$item_id = $item_name = $item_category = $item_price = $item_quantity = "";

// Add new category
if (isset($_POST['add_category'])) {
    if (!empty($_POST['category_name'])) {
        $category_name = $_POST['category_name'];
        $conn->query("INSERT INTO categories (name) VALUES ('$category_name')");
        echo "<script>alert('Category added successfully!');</script>";
    } else {
        echo "<script>alert('Please enter a category name!');</script>";
    }
}

// Add item
if (isset($_POST['add_item'])) {
    if (!empty($_POST['item_name']) && !empty($_POST['item_category']) && !empty($_POST['item_price']) && !empty($_POST['item_quantity'])) {
        $name = $_POST['item_name'];
        $category_name = $_POST['item_category'];
        $price = $_POST['item_price'];
        $quantity = $_POST['item_quantity'];

        $conn->query("INSERT INTO items (name, category_name, price, quantity) VALUES ('$name', '$category_name', $price, $quantity)");

        // Automatically clear the variables
        $item_id = $item_name = $item_category = $item_price = $item_quantity = "";
    } else {
        echo "<script>alert('Please fill out all fields!');</script>";
    }
}

// Edit item
if (isset($_POST['edit_item'])) {
    if (!empty($_POST['item_id']) && !empty($_POST['item_name']) && !empty($_POST['item_category']) && !empty($_POST['item_price']) && !empty($_POST['item_quantity'])) {
        $id = $_POST['item_id'];
        $name = $_POST['item_name'];
        $category_name = $_POST['item_category'];
        $price = $_POST['item_price'];
        $quantity = $_POST['item_quantity'];

        $conn->query("UPDATE items SET name='$name', category_name='$category_name', price=$price, quantity=$quantity WHERE id=$id");

        // Automatically clear the variables
        $item_id = $item_name = $item_category = $item_price = $item_quantity = "";
    } else {
        echo "<script>alert('Please fill out all fields!');</script>";
    }
}

// Delete item
if (isset($_POST['delete_item'])) {
    if (!empty($_POST['item_id'])) {
        $id = $_POST['item_id'];
        $conn->query("DELETE FROM items WHERE id=$id");

        // Automatically clear the variables
        $item_id = $item_name = $item_category = $item_price = $item_quantity = "";
    } else {
        echo "<script>alert('Please provide a valid Item ID!');</script>";
    }
}

// Fetch categories for combobox
$categories_result = $conn->query("SELECT name FROM categories");

// Fetch items to display
$items_result = $conn->query("SELECT * FROM items");

// Fetch specific item for editing
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $item_result = $conn->query("SELECT * FROM items WHERE id=$edit_id");
    if ($item_data = $item_result->fetch_assoc()) {
        $item_id = $item_data['id'];
        $item_name = $item_data['name'];
        $item_category = $item_data['category_name'];
        $item_price = $item_data['price'];
        $item_quantity = $item_data['quantity'];
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Items</title>
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
        <h1>Manage Items</h1>

        <!-- Go Back to Admin Dashboard -->
        <a href="admin_dashboard.php" style="text-decoration: none;">
            <button type="button" style="background: black; color: white; padding: 10px 20px;">Go Back</button>
        </a>

        <!-- Add New Category -->
        <form method="POST">
            <input type="text" name="category_name" placeholder="New Category">
            <button type="submit" name="add_category">Add Category</button>
        </form>

        <!-- Item Form -->
        <form method="POST">
            <input type="hidden" name="item_id" value="<?php echo $item_id; ?>">
            <input type="text" name="item_name" placeholder="Item Name" value="<?php echo $item_name; ?>">
            <select name="item_category">
                <option value="">Select Category</option>
                <?php while ($category = $categories_result->fetch_assoc()): ?>
                    <option value="<?php echo $category['name']; ?>"
                        <?php echo $item_category == $category['name'] ? 'selected' : ''; ?>>
                        <?php echo $category['name']; ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <input type="number" name="item_price" placeholder="Item Price" value="<?php echo $item_price; ?>">
            <input type="number" name="item_quantity" placeholder="Item Quantity" value="<?php echo $item_quantity; ?>">
            <button type="submit" name="add_item">Add Item</button>
            <button type="submit" name="edit_item">Edit Item</button>
            <button type="submit" name="delete_item">Delete Item</button>
            <input type="reset" value="Reset" onclick="window.location.href='manage_items.php';">
        </form>

        <!-- Items Table -->
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $items_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['name']; ?></td>
                    <td><?php echo $row['category_name']; ?></td>
                    <td><?php echo $row['price']; ?></td>
                    <td><?php echo $row['quantity']; ?></td>
                    <td>
                        <a href="manage_items.php?edit_id=<?php echo $row['id']; ?>" style="color: #ff5e62;">Edit</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>