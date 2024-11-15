<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $group_name = $_POST['group_name'];

    try {
        $stmt = $conn->prepare("INSERT INTO note_groups (user_id, name) VALUES (?, ?)");
        $stmt->bind_param("is", $_SESSION['user_id'], $group_name);
        $stmt->execute();
        $stmt->close();

        // Redirect back to the dashboard with a success message
        header('Location: user.dashboard.php?group_created=true');
        exit();
    } catch (Exception $e) {
        $error_message = 'Error creating group: ' . $e->getMessage();
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
    <title>WeLearn - Create Group</title>
</head>
<body class="bg-[#100a31] min-h-screen text-white">
    <!-- Navigation -->
    <nav class="border-b border-white/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex-shrink-0">
                    <img src="images/WeLearn.svg" alt="WeLearn" class="h-8">
                </div>
                <a href="user.dashboard.php" class="bg-[#7567B0] px-5 py-2 rounded-lg font-semibold hover:bg-[#6557A0] transition">
                    Back to Dashboard
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white/5 border border-white/20 rounded-lg p-6">
            <h2 class="font-worksans text-2xl font-bold mb-6">Create New Group</h2>

            <?php if (isset($error_message)): ?>
                <div class="bg-red-500 text-white px-6 py-3 rounded-lg mb-6">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-4">
                <input type="text" name="group_name" placeholder="Enter group name" required
                       class="w-full px-4 py-2 rounded-lg bg-white/10 border border-white/20 text-white placeholder-white/60">
                <button type="submit" class="bg-[#7567B0] px-5 py-2 rounded-lg font-semibold hover:bg-[#6557A0] transition">
                    Create Group
                </button>
            </form>
        </div>
    </div>
</body>
</html>