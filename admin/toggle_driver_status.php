<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

include('../db.php');

if(isset($_POST['driver_id']) && isset($_POST['status'])) {
    $driver_id = intval($_POST['driver_id']);
    $status = intval($_POST['status']);
    
    // Update driver status
    $stmt = $conn->prepare("UPDATE drivers SET status = ? WHERE ID = ?");
    $stmt->bind_param('ii', $status, $driver_id);
    $result = $stmt->execute();
    
    if($result) {
        echo json_encode(['success' => true, 'message' => 'Driver status updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update driver status: ' . $conn->error]);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request parameters']);
}
?>
