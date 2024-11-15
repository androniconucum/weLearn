<?php
require 'db.php';
session_start();

// Check if user is logged in and is user
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit();
}

// Handle group deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_group'])) {
    try {
        // Start transaction
        $conn->begin_transaction();

        $group_id = (int)$_GET['group'];

        // First verify the group belongs to the user
        $verify_stmt = $conn->prepare("SELECT id FROM note_groups WHERE id = ? AND user_id = ?");
        $verify_stmt->bind_param("ii", $group_id, $_SESSION['user_id']);
        $verify_stmt->execute();
        if (!$verify_stmt->get_result()->fetch_assoc()) {
            throw new Exception("Unauthorized access");
        }
        $verify_stmt->close();

        // Delete note_group_items entries
        $delete_items_stmt = $conn->prepare("DELETE FROM note_group_items WHERE group_id = ?");
        $delete_items_stmt->bind_param("i", $group_id);
        $delete_items_stmt->execute();
        $delete_items_stmt->close();

        // Delete the group
        $delete_group_stmt = $conn->prepare("DELETE FROM note_groups WHERE id = ? AND user_id = ?");
        $delete_group_stmt->bind_param("ii", $group_id, $_SESSION['user_id']);
        $delete_group_stmt->execute();
        $delete_group_stmt->close();

        // Commit transaction
        $conn->commit();

        header('Location: user.dashboard.php?success=' . urlencode('Group deleted successfully'));
        exit();
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        header('Location: user.dashboard.php?error=' . urlencode('Error deleting group: ' . $e->getMessage()));
        exit();
    }
}

// Check if group ID is provided
if (!isset($_GET['group'])) {
    header('Location: user.dashboard.php');
    exit();
}

// Fetch group details and its notes
try {
    // First fetch group details
    $group_stmt = $conn->prepare("
        SELECT ng.*, u.username as creator_name 
        FROM note_groups ng
        INNER JOIN users u ON ng.user_id = u.id
        WHERE ng.id = ? AND ng.user_id = ?
    ");
    $group_stmt->bind_param("ii", $_GET['group'], $_SESSION['user_id']);
    $group_stmt->execute();
    $group_result = $group_stmt->get_result();
    $group = $group_result->fetch_assoc();
    $group_stmt->close();

    if (!$group) {
        header('Location: user.dashboard.php?error=Group not found');
        exit();
    }

    // Then fetch all notes in this group
    $notes_stmt = $conn->prepare("
        SELECT n.* 
        FROM notes n
        JOIN note_group_items ngi ON n.id = ngi.note_id
        WHERE ngi.group_id = ? AND n.user_id = ?
        ORDER BY n.created_at DESC
    ");
    $notes_stmt->bind_param("ii", $_GET['group'], $_SESSION['user_id']);
    $notes_stmt->execute();
    $notes_result = $notes_stmt->get_result();
    $notes = $notes_result->fetch_all(MYSQLI_ASSOC);
    $notes_stmt->close();

} catch (Exception $e) {
    header('Location: user.dashboard.php?error=' . urlencode('Error fetching group data: ' . $e->getMessage()));
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
    <title>WeLearn - <?php echo htmlspecialchars($group['name']); ?></title>
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
        <!-- Back button -->
        <div class="mb-6">
            <a href="user.dashboard.php" class="text-white/60 hover:text-white flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                Back to Dashboard
            </a>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="bg-green-500/20 border border-green-500/30 text-green-300 px-6 py-3 rounded-lg mb-6">
                <?php echo htmlspecialchars($_GET['success']); ?>
            </div>
        <?php endif; ?>

        <div class="bg-white/5 border border-white/20 rounded-lg p-8">
            <!-- Group Header -->
            <div class="border-b border-white/20 pb-6 mb-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="font-worksans text-3xl font-bold mb-2">
                            <?php echo htmlspecialchars($group['name']); ?>
                        </h1>
                        <p class="text-white/60">
                            Created by <?php echo htmlspecialchars($group['creator_name']); ?>
                            on <?php echo date('F j, Y', strtotime($group['created_at'])); ?>
                        </p>
                    </div>
                    <!-- Delete Group Button -->
                    <button 
                        onclick="confirmDelete()"
                        class="bg-red-500 px-4 py-2 rounded-lg font-semibold hover:bg-red-600 transition flex items-center gap-2"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        Delete Group
                    </button>
                    <!-- Hidden form for delete submission -->
                    <form id="deleteForm" method="POST" class="hidden">
                        <input type="hidden" name="delete_group" value="1">
                    </form>
                </div>
            </div>

            <!-- Notes List -->
            <?php if (empty($notes)): ?>
                <div class="text-center py-12">
                    <p class="text-white/60 mb-4">No notes in this group yet.</p>
                    <a href="user.dashboard.php" class="inline-block bg-[#7567B0] px-5 py-2 rounded-lg font-semibold hover:bg-[#6557A0] transition">
                        Return to Dashboard
                    </a>
                </div>
            <?php else: ?>
                <div class="space-y-6">
                    <?php foreach ($notes as $note): ?>
                        <a href="view_note.php?id=<?php echo $note['id']; ?>" 
                           class="block bg-white/5 border border-white/20 rounded-lg p-6 hover:bg-white/10 transition">
                            <h2 class="font-semibold text-xl mb-2">
                                <?php echo htmlspecialchars($note['title']); ?>
                            </h2>
                            <p class="text-white/60 text-sm mb-2">
                                <?php echo htmlspecialchars($note['category']); ?>
                            </p>
                            <p class="text-white/80 mb-4 line-clamp-3">
                                <?php echo htmlspecialchars($note['content']); ?>
                            </p>
                            <div class="text-sm text-white/60">
                                Created: <?php echo date('F j, Y \a\t g:i A', strtotime($note['created_at'])); ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    function confirmDelete() {
        if (confirm('Are you sure you want to delete this group? This action cannot be undone.')) {
            document.getElementById('deleteForm').submit();
        }
    }
    </script>
</body>
</html>