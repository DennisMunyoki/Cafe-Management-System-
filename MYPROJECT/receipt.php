<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['print_mode'])) {
    header("Location: seller_dashboard.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "cafe_db");

$seller = $_SESSION['username'];
$order_date = date("Y-m-d H:i:s");
$total_amount = $_SESSION['total_amount'];
$order_items = $_SESSION['order_items'];

// Save to orders table
$conn->query("INSERT INTO orders (order_date, order_amount, seller) VALUES ('$order_date', $total_amount, '$seller')");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Receipt</title>
    <style>
        body { font-family: monospace; background: white; }
        .receipt { width: 300px; margin: auto; padding: 20px; border: 1px dashed black; }
        h2, h4, p { text-align: center; margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 5px; border-bottom: 1px solid #ddd; }
    </style>
    <script>
        window.onload = function() {
            window.print();
            setTimeout(() => {
                window.location.href = 'seller_dashboard.php';
            }, 1000);
        };
    </script>
</head>
<body>
    <div class="receipt">
        
        <h2>Café Receipt</h2>
        <h4>Date: <?php echo $order_date; ?></h4>
        <h4>Seller: <?php echo $seller; ?></h4>
        <hr>
        <table>
            <tr><th>Item</th><th>Qty</th><th>Total</th></tr>
            <?php foreach ($order_items as $item): ?>
                <tr>
                    <td><?php echo $item['name']; ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>Ksh <?php echo $item['total']; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <hr>
        <h3>Total: Ksh <?php echo $total_amount; ?></h3>
        <p>Thank you!</p>
    </div>
</body>
</html>

<?php
// Clear session data after printing
unset($_SESSION['order_items']);
unset($_SESSION['total_amount']);
unset($_SESSION['print_mode']);
?>
