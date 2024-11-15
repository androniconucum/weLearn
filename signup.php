<?php
session_start();

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin.dashboard.php');
    } else {
        header('Location: user.dashboard.php');
    }
    exit();
}

// Database connection
require 'db.php';

// Initialize an array to hold error messages
$errors = [
    'username' => '',
    'email' => '',
    'password' => '',
    'repassword' => ''
];

// Success message
$message = '';
$messageClass = '';

// Signup process
if (isset($_POST['signup'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $repassword = $_POST['repassword'];
    $role = 'user'; // Set default role as user
    $valid = true;

    // Username validation
    if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*[\W.]).{1,}$/', $username)) {
        $errors['username'] = 'Username must contain at least one uppercase letter,<br> one lowercase letter, and one special character.';
        $valid = false;
    }

    // Check if username already exists
    $username_query = "SELECT * FROM users WHERE username = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $username_query);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $username_result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($username_result) > 0) {
        $errors['username'] .= 'Username is already taken.<br>';
        $valid = false;
    }
    mysqli_stmt_close($stmt);

    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] .= 'Please enter a valid email address.<br>';
        $valid = false;
    }

    // Check if email already exists
    $email_query = "SELECT * FROM users WHERE email = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $email_query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $email_result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($email_result) > 0) {
        $errors['email'] .= 'Email is already registered.<br>';
        $valid = false;
    }
    mysqli_stmt_close($stmt);

    // Password validation
    if (!preg_match('/[A-Z]/', $password) || 
        !preg_match('/[a-z]/', $password) || 
        !preg_match('/[0-9]/', $password) || 
        strlen($password) < 8) {
        $errors['password'] .= 'Password must contain an uppercase letter, a <br>lowercase letter, and be at least 8 characters long.<br>';
        $valid = false;
    }

    // Password match validation
    if ($password !== $repassword) {
        $errors['repassword'] .= 'Passwords do not match.<br>';
        $valid = false;
    }

    // Insert new user if all validations pass
    if ($valid) {
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssss", $username, $email, $password_hashed, $role);

        if (mysqli_stmt_execute($stmt)) {
            $message = 'Signup successful! You can now login.';
            $messageClass = 'text-green-600';
            
            // Optional: Automatically redirect to login page after successful signup
            header("refresh:2;url=login.php");
        } else {
            $message = 'Error: ' . mysqli_error($conn);
            $messageClass = 'text-red-600';
        }
        mysqli_stmt_close($stmt);
    }
}

// Move database connection close here, outside the if block
mysqli_close($conn);
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
            <form action="" method="POST">
                <fieldset class="border-2 border-white px-10 flex flex-col gap-3 py-7">
                    <legend class="font-worksans text-[60px] text-center text-white font-black tracking-wider">SIGNUP</legend>

                    <!-- Success or Error Message -->
                    <?php if (!empty($message)): ?>
                        <div class="<?php echo $messageClass; ?> text-center mb-3"><?php echo $message; ?></div>
                    <?php endif; ?>

                    <!-- Form Fields -->
                    <input type="text" id="username" name="username" placeholder="Username" class="text-black pl-3 w-80 rounded-lg py-2 focus:outline-none">
                    <?php if (!empty($errors['username'])): ?>
                        <div class="text-red-500 text-sm"><?php echo $errors['username']; ?></div>
                    <?php endif; ?>

                    <input type="email" id="email" name="email" placeholder="Email" class="text-black pl-3 w-80 rounded-lg py-2 focus:outline-none">
                    <?php if (!empty($errors['email'])): ?>
                        <div class="text-red-500 text-sm"><?php echo $errors['email']; ?></div>
                    <?php endif; ?>

                    <input type="password" id="password" name="password" placeholder="Password" class="text-black pl-3 w-80 rounded-lg py-2 focus:outline-none">
                    <?php if (!empty($errors['password'])): ?>
                        <div class="text-red-500 text-sm"><?php echo $errors['password']; ?></div>
                    <?php endif; ?>

                    <input type="password" id="repassword" name="repassword" placeholder="Repeat Password" class="text-black pl-3 w-80 rounded-lg py-2 focus:outline-none">
                    <?php if (!empty($errors['repassword'])): ?>
                        <div class="text-red-500 text-sm"><?php echo $errors['repassword']; ?></div>
                    <?php endif; ?>

                    <div class="flex justify-between">
                        <div class="flex items-start text-center leading-none"></div>
                        <div>
                            <button type="submit" name="signup" id="signup" class="bg-[#7567B0] py-[0.5rem] px-3 font-semibold rounded-lg border">SignUp</button>
                        </div>
                    </div>
                    <hr>
                    <div class="justify-center flex">
                    <p class="text-start tracking-wider">Already have an account? <a href="login.php" class="text-blue-500 underline font-medium">Login</a></p>
                    </div>
                </fieldset>
            </form>
        </div>
    </div>
</body>
</html>
