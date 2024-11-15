<?php
require 'db.php';
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Handle user deletion
if (isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    
    // Prevent admin from deleting themselves
    if ($user_id != $_SESSION['user_id']) {
        $delete_query = "DELETE FROM users WHERE id = ?";
        $stmt = mysqli_prepare($conn, $delete_query);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "User deleted successfully!";
        } else {
            $error_message = "Error deleting user: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    } else {
        $error_message = "You cannot delete your own account!";
    }
}

// Fetch all users
$query = "SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
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
    <title>Admin Dashboard - WeLearn</title>
</head>
<body class="bg-[#100a31] min-h-screen">
    <!-- Navigation Bar -->
    <nav class="bg-[#7567B0] p-4">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center">
                <img src="images/WeLearn.svg" alt="WeLearn Logo" class="h-8 mr-2">
                <span class="text-white text-xl font-bold">Admin Dashboard</span>
            </div>
            <div class="flex items-center">
                <span class="text-white mr-4">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto p-6">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold mb-4">User Management</h2>

            <!-- Success/Error Messages -->
            <?php if (isset($success_message)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <!-- Users Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-6 py-3 border-b border-gray-200 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 border-b border-gray-200 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Username</th>
                            <th class="px-6 py-3 border-b border-gray-200 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 border-b border-gray-200 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-3 border-b border-gray-200 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                            <th class="px-6 py-3 border-b border-gray-200 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php while ($user = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-no-wrap text-sm leading-5 text-gray-500">
                                    <?php echo htmlspecialchars($user['id']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-no-wrap text-sm leading-5 text-gray-900">
                                    <?php echo htmlspecialchars($user['username']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-no-wrap text-sm leading-5 text-gray-900">
                                    <?php echo htmlspecialchars($user['email']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-no-wrap text-sm leading-5">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php echo $user['role'] === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800'; ?>">
                                        <?php echo htmlspecialchars($user['role']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-no-wrap text-sm leading-5 text-gray-900">
                                    <?php echo htmlspecialchars($user['created_at']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-no-wrap text-sm leading-5 font-medium">
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <form method="POST" action="" class="inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <button type="submit" name="delete_user" class="text-red-600 hover:text-red-900">Delete</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-gray-400">Current User</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>