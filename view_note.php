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
    $stmt = $conn->prepare("SELECT n.*, u.username as creator_name 
                           FROM notes n
                           INNER JOIN users u ON n.user_id = u.id 
                           WHERE n.id = ? AND n.user_id = ?");
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
    <title>WeLearn - <?php echo htmlspecialchars($note['title']); ?></title>
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
                <a href="user.dashboard.php" class="text-white/60 hover:text-white flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                    Back to Dashboard
                </a>
            </div>

            <!-- Success/Error Messages -->
            <?php if (isset($_GET['success'])): ?>
                <div class="bg-green-500/20 border border-green-500/30 text-green-300 px-6 py-3 rounded-lg mb-6">
                    <?php echo htmlspecialchars($_GET['success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="bg-red-500/20 border border-red-500/30 text-red-300 px-6 py-3 rounded-lg mb-6">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>

            <!-- Note Content -->
            <div class="space-y-6">
                <div class="border-b border-white/20 pb-4">
                    <h1 class="font-worksans text-3xl font-bold mb-2">
                        <?php echo htmlspecialchars($note['title']); ?>
                    </h1>
                    <div class="flex gap-4 text-white/60 text-sm">
                        <span class="bg-white/10 px-3 py-1 rounded-full">
                            <?php echo htmlspecialchars($note['category']); ?>
                        </span>
                        <span>
                            Created: <?php echo date('F j, Y \a\t g:i A', strtotime($note['created_at'])); ?>
                        </span>
                    </div>
                </div>
                
                <div class="prose prose-invert max-w-none">
                    <div class="whitespace-pre-wrap">
                        <?php echo htmlspecialchars($note['content']); ?>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-4 pt-6 border-t border-white/20">
    <a href="edit_note.php?id=<?php echo $note['id']; ?>" 
       class="bg-[#7567B0] px-5 py-2 rounded-lg font-semibold hover:bg-[#6557A0] transition">
        Edit Note
    </a>
    <form action="delete_note.php" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this note? This action cannot be undone.');">
        <input type="hidden" name="id" value="<?php echo $note['id']; ?>">
        <button type="submit" 
                class="bg-red-500 px-5 py-2 rounded-lg font-semibold hover:bg-red-600 transition">
            Delete Note
        </button>
    </form>
</div>
            </div>
        </div>
    </div>
</body>
</html>