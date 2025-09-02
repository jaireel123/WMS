<?php
session_start();
include("../includes/db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = md5($_POST['password']); // Consider using password_hash() for better security

    $sql = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['role'] = $row['role'];
        header("Location: ../dashboard/home.php");
        exit();
    } else {
        $error = "Invalid credentials!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - WaterMonitor</title>
<link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">

<style>
body {
    margin: 0;
    font-family: 'Segoe UI', Roboto, sans-serif;
    background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

.login-card {
    background: #fff;
    padding: 2rem 2.5rem;
    border-radius: 16px;
    box-shadow: 0 12px 30px rgba(0,0,0,0.15);
    width: 100%;
    max-width: 400px;
    text-align: center;
}

.login-card h2 {
    margin-bottom: 1.5rem;
    color: #0d6efd;
    font-weight: 700;
}

.login-card form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.login-card input {
    padding: 0.75rem 1rem;
    border-radius: 10px;
    border: 1px solid #ced4da;
    font-size: 1rem;
    transition: all 0.2s ease;
}

.login-card input:focus {
    outline: none;
    border-color: #0dcaf0;
    box-shadow: 0 0 0 3px rgba(13,202,240,0.15);
}

.login-card button {
    padding: 0.75rem 1rem;
    border: none;
    border-radius: 10px;
    background: #0d6efd;
    color: #fff;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.login-card button:hover {
    background: #0dcaf0;
}

.login-card .error {
    color: #dc3545;
    font-size: 0.9rem;
}

.login-card a {
    display: inline-block;
    margin-top: 0.75rem;
    font-size: 0.9rem;
    color: #0d6efd;
    text-decoration: none;
    transition: color 0.2s ease;
}

.login-card a:hover {
    color: #0dcaf0;
}
</style>
</head>
<body>

<div class="login-card">
    <h2>WaterMonitor Login</h2>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
    <?php if(isset($error)): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>
    <a href="forgot.php">Forgot Password?</a>
</div>

</body>
</html>
