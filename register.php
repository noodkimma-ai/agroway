<?php
session_start();
require 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $name  = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $role  = mysqli_real_escape_string($conn, $_POST['role']);
    $pass  = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (name, email, phone, role, password_hash)
            VALUES ('$name', '$email', '$phone', '$role', '$pass')";

    if (mysqli_query($conn, $sql)) {
        header("Location: login.php");
        exit;
    } else {
        $error = "Registration failed!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Register</title>

<style>
body{
    font-family:Poppins,sans-serif;
    background:#f0fdf4; /* light green background */
    min-height:100vh;
    margin:0;
    display:flex;
    justify-content:center;
    align-items:center;
}

/* Container */
.card{
    width:380px;
    background:#D8E2DC; /* soft green */
    padding:30px;
    border-radius:15px;
    box-shadow:0 10px 25px rgba(0,0,0,0.1);
    color:#065f46;
}

/* Heading */
.card h2{
    text-align:center;
    margin-bottom:20px;
    color:#15803d;
}

/* Inputs */
input, select{
    width:100%;
    padding:12px;
    margin-bottom:12px;
    border-radius:10px;
    border:1px solid #22c55e;
    outline:none;
    background:#f0fdf4;
}

/* Button */
button{
    width:100%;
    padding:12px;
    background:#22c55e;
    color:white;
    border:none;
    border-radius:10px;
    font-weight:600;
    cursor:pointer;
    transition:0.3s;
}
button:hover{
    background:#15803d;
}

/* Error */
.error{
    color:#dc2626;
    text-align:center;
    margin-bottom:10px;
}

/* Link */
.link{
    text-align:center;
    margin-top:10px;
}
.link a{
    color:#15803d;
    text-decoration:none;
    font-weight:500;
}
.link a:hover{
    text-decoration:underline;
}
</style>

</head>
<body>

<div class="card">
<h2>🌾 Create Account</h2>

<?php if($error): ?>
<div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="post">
    <input name="name" placeholder="Full Name" required>
    <input name="email" type="email" placeholder="Email" required>
    <input name="phone" placeholder="Phone">
    
    <select name="role">
        <option value="buyer">Buyer</option>
        <option value="farmer">Farmer</option>
    </select>

    <input name="password" type="password" placeholder="Password" required>

    <button type="submit">Register</button>
</form>

<div class="link">
Already have account? <a href="login.php">Login</a>
</div>

</div>

</body>
</html>