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

// Fetch sales statistics
$total_sales_result = $conn->query("SELECT COUNT(*) AS total_sales FROM orders");
$total_revenue_result = $conn->query("SELECT SUM(order_amount) AS total_revenue FROM orders");
$sales_by_date_result = $conn->query("SELECT DATE(order_date) AS sale_date, SUM(order_amount) AS daily_revenue FROM orders GROUP BY DATE(order_date) ORDER BY sale_date ASC");

// Extract data for display
$total_sales = $total_sales_result->fetch_assoc()['total_sales'];
$total_revenue = $total_revenue_result->fetch_assoc()['total_revenue'];

// Prepare data for the bar chart
$dates = [];
$revenues = [];
while ($row = $sales_by_date_result->fetch_assoc()) {
    $dates[] = $row['sale_date'];
    $revenues[] = $row['daily_revenue'];
}

// Create the bar chart as an image
function createBarChart($labels, $values, $filePath) {
    $imageWidth = 800;
    $imageHeight = 500;
    $margin = 40;
    $barWidth = 30;
    $barSpacing = 20;

    $image = imagecreate($imageWidth, $imageHeight);

    // Define colors
    $white = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 0, 0, 0);
    $blue = imagecolorallocate($image, 54, 162, 235);
    $gray = imagecolorallocate($image, 200, 200, 200);

    // Draw background
    imagefilledrectangle($image, 0, 0, $imageWidth, $imageHeight, $white);

    // Draw axis lines
    imageline($image, $margin, $imageHeight - $margin, $imageWidth - $margin, $imageHeight - $margin, $black); // X-axis
    imageline($image, $margin, $margin, $margin, $imageHeight - $margin, $black); // Y-axis

    // Calculate scaling factor
    $maxValue = max($values);
    $scale = ($imageHeight - 2 * $margin) / $maxValue;

    // Draw bars
    foreach ($values as $index => $value) {
        $x1 = $margin + ($barWidth + $barSpacing) * $index;
        $y1 = $imageHeight - $margin;
        $x2 = $x1 + $barWidth;
        $y2 = $y1 - ($value * $scale);

        // Draw the bar
        imagefilledrectangle($image, $x1, $y1, $x2, $y2, $blue);

        // Add value labels
        imagestring($image, 3, $x1 + 5, $y2 - 15, number_format($value, 2), $black);

        // Add date labels
        imagestring($image, 3, $x1 - 5, $imageHeight - $margin + 5, substr($labels[$index], -5), $black);
    }

    // Save image
    imagepng($image, $filePath);
    imagedestroy($image);
}

// Generate the chart and save it as an image
$chartPath = 'daily_sales_chart.png';
createBarChart($dates, $revenues, $chartPath);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Sales Statistics</title>
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
        h1 {
            text-align: center;
            color: #333;
        }
        .stats {
            display: flex;
            justify-content: space-around;
            margin-top: 20px;
        }
        .stat-card {
            width: 200px;
            height: 100px;
            background: linear-gradient(to right, #4facfe, #00f2fe);
            border-radius: 10px;
            text-align: center;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
        }
        img {
            display: block;
            margin: 20px auto;
            border: 1px solid #ddd;
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
        .go-back {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #333;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
        }
        .go-back:hover {
            background: #555;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Sales Statistics</h1>

        <!-- Summary Stats -->
        <div class="stats">
            <div class="stat-card">
                <h2>Total Sales</h2>
                <p><?php echo $total_sales; ?></p>
            </div>
            <div class="stat-card">
                <h2>Total Revenue</h2>
                <p>$<?php echo number_format($total_revenue, 2); ?></p>
            </div>
        </div>

        <!-- Bar Chart -->
        <h2>Daily Sales Revenue</h2>
        <img src="<?php echo $chartPath; ?>" alt="Bar Chart of Daily Sales Revenue">

        <!-- Sales by Date Table -->
        <table>
            <tr>
                <th>Date</th>
                <th>Revenue</th>
            </tr>
            <?php foreach ($revenues as $index => $revenue): ?>
                <tr>
                    <td><?php echo $dates[$index]; ?></td>
                    <td>$<?php echo number_format($revenue, 2); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <!-- Go Back to Admin Dashboard -->
        <a href="admin_dashboard.php" class="go-back">Go Back</a>
    </div>
</body>
</html>