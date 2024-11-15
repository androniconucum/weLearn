<?php
session_start();

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin.dashboard.php');
    } else {
        header('Location: user.dashboard.php');
    }
    exit();
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
    <title>WeLearn - Welcome</title>
</head>
<body class="bg-[#100a31]">
    <div class="min-h-screen text-white">
        <!-- Navigation -->
        <nav class="border-b border-white/20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex-shrink-0">
                        <img src="images/WeLearn.svg" alt="WeLearn" class="h-8">
                    </div>
                    <div class="flex gap-4">
                        <a href="login.php" class="bg-[#7567B0] px-5 py-2 rounded-lg border font-semibold">Login</a>
                        <a href="signup.php" class="px-5 py-2 rounded-lg border font-semibold">Sign Up</a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row items-center justify-between py-20">
                <!-- Left Column -->
                <div class="md:w-1/2 mb-10 md:mb-0">
                    <h1 class="font-worksans text-6xl font-black tracking-wider mb-6">
                        Learn Together,<br>
                        Grow Together
                    </h1>
                    <p class="text-lg mb-8 text-white/80">
                        Join our community of learners and discover a new way to master your skills. 
                        WeLearn provides interactive learning experiences tailored to your needs.
                    </p>
                    <div>
                        <a href="signup.php" class="bg-[#7567B0] px-8 py-3 rounded-lg border font-semibold inline-block">
                            Get Started
                        </a>
                        <a href="" class="px-8 py-3 rounded-lg border font-semibold inline-block">
                            Developers
                        </a>
                    </div>
                </div>
                
                <!-- Right Column -->
                <div class="md:w-1/2 md:pl-12">
                    <div class="border-2 border-white rounded-lg bg-white/5 p-5">
                        <h2 class="font-worksans text-3xl font-bold mb-4">Why Choose WeLearn?</h2>
                        <div class="grid grid-cols-1 gap-2">
                            <div class="flex items-center space-x-2">
                                <span class="w-2 h-2 bg-[#7567B0] rounded-full"></span>
                                <span>Interactive Learning Experiences</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="w-2 h-2 bg-[#7567B0] rounded-full"></span>
                                <span>Expert-led Professional Courses</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="w-2 h-2 bg-[#7567B0] rounded-full"></span>
                                <span>Flexible Learning Schedule</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="w-2 h-2 bg-[#7567B0] rounded-full"></span>
                                <span>Supportive Learning Community</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>