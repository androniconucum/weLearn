<?php
require 'db.php';
session_start();

// Check if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin.dashboard.php');
    } else {
        header('Location: user.dashboard.php');
    }
    exit();
}

$error = '';

// Login process
if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        // Check user credentials
        $query = "SELECT id, username, password, role FROM users WHERE username = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($user = mysqli_fetch_assoc($result)) {
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header('Location: admin.dashboard.php');
                } else {
                    header('Location: user.dashboard.php');
                }
                exit();
            } else {
                $error = 'Invalid username or password';
            }
        } else {
            $error = 'Invalid username or password';
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <title>WeLearn</title>
</head>
<body class="bg-[#100a31]">
    <div class="flex items-center justify-center h-screen text-white">
        <div class="flex flex-col font-worksans">
            <img src="images/WeLearn.svg" alt="" width="390" class="mb-5">
            <form method="POST" action="">
                <fieldset class="border-2 border-white px-10 flex flex-col gap-3 py-7">
                    <legend class="font-worksans text-[60px] text-center text-white font-black tracking-wider">LOGIN</legend>
                    
                    <?php if (!empty($error)): ?>
                        <div class="text-red-500 text-sm text-center"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <input type="text" id="username" name="username" placeholder="Username" class="text-black pl-3 w-80 rounded-lg py-2 focus:outline-none">
                    <input type="password" id="password" name="password" placeholder="Password" class="text-black pl-3 w-80 rounded-lg py-2 focus:outline-none">
                    <div class="flex justify-end">
                        <button type="submit" name="login" id="login" class="bg-[#7567B0] py-[0.5rem] px-5 font-semibold rounded-lg border">Login</button>
                    </div>
                    <hr>
                    <div class="justify-center flex">
                        <p class="text-start tracking-wider">Don't have an account yet? <a href="signup.php" class="text-blue-500 underline font-medium">SignUp</a></p>
                    </div>
                </fieldset>
            </form>
        </div>
    </div>
</body>
</html>