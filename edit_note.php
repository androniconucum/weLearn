<?php
require 'db.php';
session_start();

// Check if user is logged in and is user
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit();
}

// Check if note ID is provided
if (!isset($_GET['id'])) {
    header('Location: user.dashboard.php');
    exit();
}

// Fetch the note
try {
    $stmt = $conn->prepare("SELECT * FROM notes WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $_GET['id'], $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $note = $result->fetch_assoc();
    $stmt->close();

    // If note doesn't exist or doesn't belong to user
    if (!$note) {
        header('Location: user.dashboard.php?error=Note not found');
        exit();
    }
} catch (Exception $e) {
    header('Location: user.dashboard.php?error=' . urlencode('Error fetching note: ' . $e->getMessage()));
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $category = trim($_POST['category']);
    
    $errors = [];
    
    // Validate input
    if (empty($title)) {
        $errors[] = "Title is required";
    }
    if (empty($content)) {
        $errors[] = "Content is required";
    }
    if (empty($category)) {
        $errors[] = "Category is required";
    }
    
    // If no errors, update the note
    if (empty($errors)) {
        try {
            $update_stmt = $conn->prepare("
                UPDATE notes 
                SET title = ?, content = ?, category = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ? AND user_id = ?
            ");
            $update_stmt->bind_param("sssii", $title, $content, $category, $_GET['id'], $_SESSION['user_id']);
            $update_stmt->execute();
            
            if ($update_stmt->affected_rows > 0) {
                header('Location: view_note.php?id=' . $_GET['id'] . '&success=Note updated successfully');
                exit();
            }
            $update_stmt->close();
        } catch (Exception $e) {
            $errors[] = 'Error updating note: ' . $e->getMessage();
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
    <title>WeLearn - Edit Note</title>
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
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white/5 border border-white/20 rounded-lg p-8">
            <!-- Back button -->
            <div class="mb-6">
                <a href="view_note.php?id=<?php echo $_GET['id']; ?>" class="text-white/60 hover:text-white flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                    Back to Note
                </a>
            </div>

            <h1 class="text-2xl font-bold mb-6">Edit Note</h1>

            <!-- Error Messages -->
            <?php if (!empty($errors)): ?>
                <div class="bg-red-500/20 border border-red-500/30 text-red-300 px-6 py-3 rounded-lg mb-6">
                    <ul class="list-disc list-inside">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Edit Form -->
            <form method="POST" class="space-y-6">
                <div>
                    <label for="title" class="block text-sm font-medium text-white/80 mb-2">Title</label>
                    <input type="text" 
                           id="title" 
                           name="title" 
                           value="<?php echo htmlspecialchars($note['title']); ?>"
                           class="w-full bg-white/5 border border-white/20 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-[#7567B0]">
                </div>

                <div>
                    <label for="category" class="block text-sm font-medium text-white/80 mb-2">Category</label>
                    <input type="text" 
                           id="category" 
                           name="category" 
                           value="<?php echo htmlspecialchars($note['category']); ?>"
                           class="w-full bg-white/5 border border-white/20 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-[#7567B0]">
                </div>

                <div>
                    <label for="content" class="block text-sm font-medium text-white/80 mb-2">Content</label>
                    <textarea id="content" 
                              name="content" 
                              rows="10"
                              class="w-full bg-white/5 border border-white/20 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-[#7567B0]"><?php echo htmlspecialchars($note['content']); ?></textarea>
                </div>

                <div class="flex gap-4">
                    <button type="submit" 
                            class="bg-[#7567B0] px-6 py-2 rounded-lg font-semibold hover:bg-[#6557A0] transition">
                        Update Note
                    </button>
                    <a href="view_note.php?id=<?php echo $_GET['id']; ?>" 
                       class="bg-white/10 px-6 py-2 rounded-lg font-semibold hover:bg-white/20 transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>