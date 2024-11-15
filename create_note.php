<?php 
require 'db.php';
session_start();

// Check if user is logged in and is user
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit();
}

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_note'])) {
    // Validate input
    if (empty($_POST['title']) || empty($_POST['content']) || empty($_POST['category'])) {
        $error_message = 'All fields are required';
    } else {
        // Sanitize input
        $title = htmlspecialchars($_POST['title']);
        $content = htmlspecialchars($_POST['content']);
        $category = htmlspecialchars($_POST['category']);
        $user_id = $_SESSION['user_id'];
        $created_at = date('Y-m-d H:i:s');

        try {
            // Prepare SQL statement
            $stmt = $conn->prepare("INSERT INTO notes (user_id, title, content, category, created_at) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $user_id, $title, $content, $category, $created_at);
            
            // Execute the statement
            if ($stmt->execute()) {
                // Redirect to dashboard after successful creation
                header('Location: user.dashboard.php?success=1');
                exit();
            } else {
                throw new Exception("Error executing statement");
            }
            
            $stmt->close();
        } catch (Exception $e) {
            $error_message = 'Database error: ' . $e->getMessage();
        }
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
    <title>WeLearn - Create Note</title>
</head>
<body class="bg-[#100a31] min-h-screen text-white">
    <!-- Navigation -->
    <nav class="border-b border-white/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex-shrink-0">
                    <img src="images/WeLearn.svg" alt="WeLearn" class="h-8">
                </div>
                <div class="flex gap-4">
                    <a href="user.dashboard.php" class="bg-[#7567B0] px-5 py-2 rounded-lg font-semibold hover:bg-[#6557A0] transition">Dashboard</a>
                    <a href="logout.php" class="bg-red-500 px-5 py-2 rounded-lg font-semibold hover:bg-red-600 transition">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?php if ($error_message): ?>
            <div class="bg-red-500 text-white px-6 py-3 rounded-lg mb-6">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="bg-white/5 border border-white/20 rounded-lg p-6">
            <h2 class="font-worksans text-2xl font-bold mb-6">Create New Note</h2>
            <form method="POST" action="" class="space-y-6">
                <div>
                    <label for="title" class="block text-sm font-medium mb-2">Title</label>
                    <input type="text" id="title" name="title" required
                           class="w-full bg-white/5 border border-white/20 rounded-lg px-4 py-2 focus:outline-none focus:border-[#7567B0]"
                           value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
                </div>
                
                <div>
                    <label for="content" class="block text-sm font-medium mb-2">Content</label>
                    <textarea id="content" name="content" rows="6" required
                              class="w-full bg-white/5 border border-white/20 rounded-lg px-4 py-2 focus:outline-none focus:border-[#7567B0]"><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?></textarea>
                </div>
                
                <div>
                    <label for="category" class="block text-sm font-medium mb-2">Category</label>
                    <select id="category" name="category" required
                            class="w-full bg-white/5 border border-white/20 rounded-lg px-4 py-2 focus:outline-none focus:border-[#7567B0]">
                        <?php
                        $categories = ['Science', 'Math', 'English', 'Programming', 'Data Analysis'];
                        foreach ($categories as $cat) {
                            $selected = (isset($_POST['category']) && $_POST['category'] === $cat) ? 'selected' : '';
                            echo "<option value=\"$cat\" $selected>$cat</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="flex gap-4">
                    <button type="submit" name="create_note"
                            class="flex-1 px-4 py-2 bg-[#7567B0] rounded-lg hover:bg-[#6557A0] transition">
                        Create Note
                    </button>
                    <a href="user.dashboard.php" 
                       class="flex-1 px-4 py-2 bg-gray-500 rounded-lg hover:bg-gray-600 transition text-center">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>