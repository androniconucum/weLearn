<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_GET['note_id']) && isset($_GET['group_id'])) {
    $note_id = (int)$_GET['note_id'];
    $group_id = (int)$_GET['group_id'];
    $user_id = $_SESSION['user_id'];

    try {
        // First verify the note belongs to the user
        $note_check = $conn->prepare("SELECT id FROM notes WHERE id = ? AND user_id = ?");
        $note_check->bind_param("ii", $note_id, $user_id);
        $note_check->execute();
        $note_result = $note_check->get_result();

        // Then verify the group belongs to the user
        $group_check = $conn->prepare("SELECT id FROM note_groups WHERE id = ? AND user_id = ?");
        $group_check->bind_param("ii", $group_id, $user_id);
        $group_check->execute();
        $group_result = $group_check->get_result();

        if ($note_result->num_rows > 0 && $group_result->num_rows > 0) {
            // Check if the note is already in the group
            $existing_check = $conn->prepare("SELECT id FROM note_group_items WHERE note_id = ? AND group_id = ?");
            $existing_check->bind_param("ii", $note_id, $group_id);
            $existing_check->execute();
            $existing_result = $existing_check->get_result();

            if ($existing_result->num_rows === 0) {
                // Add note to group only if it's not already there
                $insert = $conn->prepare("INSERT INTO note_group_items (note_id, group_id) VALUES (?, ?)");
                $insert->bind_param("ii", $note_id, $group_id);
                
                if ($insert->execute()) {
                    // Redirect back to the specific group view
                    header("Location: dashboard.php?group=" . $group_id . "&success=Note added to group successfully");
                    exit();
                } else {
                    throw new Exception("Failed to add note to group");
                }
            } else {
                // Note is already in the group
                header("Location: dashboard.php?group=" . $group_id . "&info=Note is already in this group");
                exit();
            }
        } else {
            header('Location: dashboard.php?error=Invalid note or group');
            exit();
        }
        
    } catch (Exception $e) {
        error_log("Error in add_to_group.php: " . $e->getMessage());
        header('Location: dashboard.php?error=' . urlencode('Failed to add note to group: ' . $e->getMessage()));
        exit();
    }
}

// If we get here, required parameters were missing
header('Location: dashboard.php?error=Missing required parameters');
exit();