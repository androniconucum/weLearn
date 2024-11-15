<?php 
require 'db.php';
session_start();

// Check if user is logged in and is user
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit();
}

// Function to get appropriate success message
function getSuccessMessage($type) {
    switch ($type) {
        case 'note_created':
            return 'Note created successfully!';
        case 'group_deleted':
            return 'Group deleted successfully!';
        case 'note_updated':
            return 'Note updated successfully!';
        case 'group_created':
            return 'Group created successfully!';
        default:
            return 'Operation completed successfully!';
    }
}

// Fetch existing notes for display
$user_notes = [];
try {
    // Get the selected group ID from JavaScript (will be passed in URL)
    $selected_group = isset($_GET['group']) ? (int)$_GET['group'] : null;
    
    if ($selected_group) {
        // Fetch notes for specific group
        $stmt = $conn->prepare("
            SELECT n.* 
            FROM notes n
            JOIN note_group_items ngi ON n.id = ngi.note_id
            WHERE n.user_id = ? AND ngi.group_id = ?
            ORDER BY n.created_at DESC
        ");
        $stmt->bind_param("ii", $_SESSION['user_id'], $selected_group);
    } else {
        // Fetch all notes (your existing query)
        $stmt = $conn->prepare("SELECT * FROM notes WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->bind_param("i", $_SESSION['user_id']);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $user_notes[] = $row;
    }
    $stmt->close();

    // Fetch user's note groups
    $stmt = $conn->prepare("SELECT * FROM note_groups WHERE user_id = ? ORDER BY name");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $groups_result = $stmt->get_result();
    $note_groups = $groups_result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (Exception $e) {
    $error_message = 'Error fetching data: ' . $e->getMessage();
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
    <title>WeLearn - Dashboard</title>
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
                    <a href="create_group.php" class="bg-[#7567B0] px-5 py-2 rounded-lg font-semibold hover:bg-[#6557A0] transition">Create Group</a>
                    <a href="create_note.php" class="bg-[#7567B0] px-5 py-2 rounded-lg font-semibold hover:bg-[#6557A0] transition">Create Note</a>
                    <a href="logout.php" class="bg-red-500 px-5 py-2 rounded-lg font-semibold hover:bg-red-600 transition">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Updated Success Message Section -->
        <?php if (isset($_GET['success']) && isset($_GET['type'])): ?>
            <div class="bg-green-500 text-white px-6 py-3 rounded-lg mb-6">
                <?php echo htmlspecialchars(getSuccessMessage($_GET['type'])); ?>
            </div>
        <?php endif; ?>

        <!-- Group Selection Tabs -->
        <div class="mb-6 border-b border-white/20">
            <div class="flex space-x-4">
                <button onclick="showAllNotes()" 
                        class="px-4 py-2 text-sm font-medium <?php echo !$selected_group ? 'border-b-2 border-[#7567B0]' : ''; ?>">
                    All Notes
                </button>
                <?php foreach ($note_groups as $group): ?>
                    <button onclick="showGroup(<?php echo $group['id']; ?>)" 
                            class="px-4 py-2 text-sm font-medium <?php echo $selected_group == $group['id'] ? 'border-b-2 border-[#7567B0]' : ''; ?>">
                        <?php echo htmlspecialchars($group['name']); ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="bg-white/5 border border-white/20 rounded-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="font-worksans text-2xl font-bold">
                    <?php 
                    if ($selected_group) {
                        foreach ($note_groups as $group) {
                            if ($group['id'] == $selected_group) {
                                echo htmlspecialchars($group['name']) . " Notes";
                                break;
                            }
                        }
                    } else {
                        echo "Your Notes";
                    }
                    ?>
                </h2>
                <a href="create_note.php" class="bg-[#7567B0] px-5 py-2 rounded-lg font-semibold hover:bg-[#6557A0] transition">
                    Create New Note
                </a>
            </div>
            
            <?php if (empty($user_notes)): ?>
                <div class="text-center py-12">
                    <p class="text-white/60 mb-4">
                        <?php echo $selected_group ? 'No notes in this group yet.' : 'No notes yet. Create your first note!'; ?>
                    </p>
                    <a href="create_note.php" class="inline-block bg-[#7567B0] px-5 py-2 rounded-lg font-semibold hover:bg-[#6557A0] transition">
                        Create Note
                    </a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($user_notes as $note): ?>
                        <div class="relative group">
                            <div class="absolute right-2 top-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <a href="manage_groups.php?note_id=<?php echo $note['id']; ?>" 
                                   class="bg-[#7567B0] p-2 rounded-lg hover:bg-[#6557A0] transition inline-block">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                    </svg>
                                </a>
                            </div>
                            <a href="view_note.php?id=<?php echo $note['id']; ?>" 
                               class="block bg-white/5 border border-white/20 rounded-lg p-4 hover:bg-white/10 transition">
                                <h3 class="font-semibold text-lg mb-2"><?php echo htmlspecialchars($note['title']); ?></h3>
                                <p class="text-white/60 text-sm mb-2"><?php echo htmlspecialchars($note['category']); ?></p>
                                <p class="text-white/80 mb-4 line-clamp-3"><?php echo htmlspecialchars($note['content']); ?></p>
                                <div class="text-sm text-white/60">
                                    Created: <?php echo date('M j, Y', strtotime($note['created_at'])); ?>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Group filtering functions
        function showAllNotes() {
            window.location.href = 'user.dashboard.php';
        }

        function showGroup(groupId) {
            window.location.href = 'view_group.php?group=' + groupId;
        }
    </script>
</body>
</html>