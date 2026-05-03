<?php
session_start();
require 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if(empty($email) || empty($pass)){
        $error = "Please fill all fields";
    } else {

        $res = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");

        if($res && mysqli_num_rows($res) > 0){

            $row = mysqli_fetch_assoc($res);

            // Check password
            if(password_verify($pass, $row['password_hash'])){

                // Check if blocked
                if($row['status'] === 'blocked'){
                    $error = "Your account is blocked. Contact admin.";
                } else {
                    $_SESSION['user_id'] = $row['user_id'];
                    $_SESSION['name']    = $row['name'];
                    $_SESSION['role']    = $row['role'];

                    // Redirect by role
                    if($row['role'] == 'admin'){
                        header("Location: dashboard.php");
                    } elseif($row['role'] == 'farmer'){
                        header("Location: farmer_dashboard.php");
                    } else {
                        header("Location: index.php");
                    }
                    exit;
                }

            } else {
                $error = "Wrong password";
            }

        } else {
            $error = "User not found";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Login | AgroWay</title>

<style>
body {
    font-family:Poppins, sans-serif;
    background: #ffffff;
    min-height:100vh;
    margin:0;
    display:flex;
    justify-content:center;
    align-items:center;
}

/* Container */
.container{
    width:380px;
    background:#e8f5e9; /* soft green */
    padding:30px;
    border-radius:16px;
    box-shadow:0 15px 35px rgba(0,0,0,0.2);
    color:#1b5e20; /* dark green text */
}

/* Heading */
.container h3{
    text-align:center;
    margin-bottom:25px;
    color:#2e7d32;
}

/* Inputs */
.input-group{
    position:relative;
    margin-bottom:15px;
}

input{
    width:100%;
    padding:12px;
    border-radius:10px;
    border:1px solid #c8e6c9;
    outline:none;
    font-size:14px;
}

/* Show/hide password toggle */
.toggle-pass{
    position:absolute;
    right:12px;
    top:50%;
    transform:translateY(-50%);
    cursor:pointer;
    user-select:none;
    font-size:18px;
    color:#2e7d32;
}

/* Button */
button{
    width:100%;
    padding:12px;
    background:#66bb6a; /* light green */
    color:white;
    border:none;
    border-radius:10px;
    font-weight:600;
    cursor:pointer;
    transition:0.3s;
    font-size:16px;
}
button:hover{
    background:#4caf50; /* darker green on hover */
}

/* Error */
.error{
    color:#ef4444;
    text-align:center;
    margin-bottom:12px;
    font-weight:600;
}

/* Link */
.link{
    text-align:center;
    margin-top:15px;
}
.link a{
    color:#2e7d32;
    text-decoration:none;
    font-weight:500;
}
.link a:hover{
    text-decoration:underline;
}
</style>
</head>
<body>

<div class="container">
<h3>🌾 AgroWay Login</h3>

<?php if(!empty($error)): ?>
<div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="post">
    <div class="input-group">
        <input name="email" type="email" placeholder="Email" required>
    </div>

    <div class="input-group">
        <input id="password" name="password" type="password" placeholder="Password" required>
        <span id="togglePass" class="toggle-pass">👁️</span>
    </div>

    <button type="submit">Login</button>
</form>

<div class="link">
    <a href="register.php">Don't have an account? Register</a>
</div>
</div>

<script>
const toggle = document.getElementById('togglePass');
const passInput = document.getElementById('password');

toggle.addEventListener('click', () => {
    if(passInput.type === 'password'){
        passInput.type = 'text';
        toggle.textContent = '🙈';
    } else {
        passInput.type = 'password';
        toggle.textContent = '👁️';
    }
});
</script>

</body>
</html>