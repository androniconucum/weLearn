<?php
// delete_note.php
require 'db.php';
session_start();

// Check if user is logged in and is user
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit();
}

// Check if note ID is provided
if (!isset($_POST['id'])) {
    header('Location: user.dashboard.php');
    exit();
}

try {
    // First verify the note belongs to the user
    $stmt = $conn->prepare("SELECT user_id FROM notes WHERE id = ?");
    $stmt->bind_param("i", $_POST['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $note = $result->fetch_assoc();
    $stmt->close();

    if (!$note || $note['user_id'] !== $_SESSION['user_id']) {
        header('Location: user.dashboard.php?error=Unauthorized access');
        exit();
    }

    // Delete the note
    $stmt = $conn->prepare("DELETE FROM notes WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $_POST['id'], $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();

    header('Location: user.dashboard.php?success=Note deleted successfully');
    exit();
} catch (Exception $e) {
    header('Location: user.dashboard.php?error=' . urlencode('Error deleting note: ' . $e->getMessage()));
    exit();
}