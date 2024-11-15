<?php
require 'db.php';
session_start();

// Check if user is logged in and is user
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit();
}

// Check if note_id is provided
if (!isset($_GET['note_id'])) {
    header('Location: user.dashboard.php');
    exit();
}

$note_id = (int)$_GET['note_id'];

// Verify the note belongs to the current user
try {
    $stmt = $conn->prepare("SELECT title FROM notes WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $note_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if (!$result->fetch_assoc()) {
        header('Location: user.dashboard.php');
        exit();
    }
    $stmt->close();
} catch (Exception $e) {
    die('Error verifying note ownership: ' . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // First, remove all existing group associations for this note
        $stmt = $conn->prepare("DELETE FROM note_group_items WHERE note_id = ?");
        $stmt->bind_param("i", $note_id);
        $stmt->execute();
        
        // Then add new group associations
        if (isset($_POST['groups']) && is_array($_POST['groups'])) {
            $stmt = $conn->prepare("INSERT INTO note_group_items (note_id, group_id) VALUES (?, ?)");
            foreach ($_POST['groups'] as $group_id) {
                $stmt->bind_param("ii", $note_id, $group_id);
                $stmt->execute();
            }
        }
        
        header('Location: user.dashboard.php?group_update=success');
        exit();
    } catch (Exception $e) {
        $error_message = 'Error updating groups: ' . $e->getMessage();
    }
}

// Fetch all user's groups
try {
    $stmt = $conn->prepare("SELECT * FROM note_groups WHERE user_id = ? ORDER BY name");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $groups_result = $stmt->get_result();
    $note_groups = $groups_result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Fetch current group associations for this note
    $stmt = $conn->prepare("SELECT group_id FROM note_group_items WHERE note_id = ?");
    $stmt->bind_param("i", $note_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $current_groups = array();
    while ($row = $result->fetch_assoc()) {
        $current_groups[] = $row['group_id'];
    }
    $stmt->close();
} catch (Exception $e) {
    die('Error fetching groups: ' . $e->getMessage());
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
    <title>WeLearn - Manage Groups</title>
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
                    <a href="user.dashboard.php" class="bg-[#7567B0] px-5 py-2 rounded-lg font-semibold hover:bg-[#6557A0] transition">Back to Dashboard</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white/5 border border-white/20 rounded-lg p-6">
            <h2 class="text-2xl font-bold mb-6">Add to a Group</h2>
            
            <?php if (isset($error_message)): ?>
                <div class="bg-red-500 text-white px-6 py-3 rounded-lg mb-6">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="space-y-4">
                    <?php if (empty($note_groups)): ?>
                        <p class="text-white/60 mb-4">No groups created yet. <a href="create_group.php" class="text-[#7567B0] hover:underline">Create a group first</a>.</p>
                    <?php else: ?>
                        <?php foreach ($note_groups as $group): ?>
                            <div class="flex items-center space-x-3">
                                <input type="checkbox" 
                                       id="group_<?php echo $group['id']; ?>" 
                                       name="groups[]" 
                                       value="<?php echo $group['id']; ?>"
                                       <?php echo in_array($group['id'], $current_groups) ? 'checked' : ''; ?>
                                       class="h-4 w-4 rounded border-white/20 bg-white/5 text-[#7567B0] focus:ring-[#7567B0]">
                                <label for="group_<?php echo $group['id']; ?>" class="text-sm font-medium">
                                    <?php echo htmlspecialchars($group['name']); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="pt-6">
                            <button type="submit" class="w-full bg-[#7567B0] px-5 py-2 rounded-lg font-semibold hover:bg-[#6557A0] transition">
                                Save Changes
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</body>
</html>