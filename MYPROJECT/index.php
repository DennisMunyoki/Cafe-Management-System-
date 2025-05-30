<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Café Management System - Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="title-box">
            <h1>Café Management System</h1>
            <p>~ BY DENNIS ~</p>
        </div>
        <div class="login-box">
            <h2>Café Kazuri</h2>
            <img src="coffee.jpg" alt="Cafe Image">
            <form action="login.php" method="POST">
                <label for="username">UserName</label>
                <input type="text" id="username" name="username" required>

                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>

                <button type="submit">Login</button>
            </form>
        </div>
    </div>
</body>
</html>
