<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
include_once '../db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    // Redirect to login page if not logged in or not an admin
    header("Location: ../login.php");
    exit();
}

// Update message status if requested
if (isset($_GET['mark_as_read']) && is_numeric($_GET['mark_as_read'])) {
    $message_id = (int)$_GET['mark_as_read'];
    $stmt = $conn->prepare("UPDATE contact_messages SET status = 'read' WHERE id = ?");
    $stmt->bind_param("i", $message_id);
    $stmt->execute();
    header("Location: contact_messages.php");
    exit();
}

if (isset($_GET['mark_as_replied']) && is_numeric($_GET['mark_as_replied'])) {
    $message_id = (int)$_GET['mark_as_replied'];
    $stmt = $conn->prepare("UPDATE contact_messages SET status = 'replied' WHERE id = ?");
    $stmt->bind_param("i", $message_id);
    $stmt->execute();
    header("Location: contact_messages.php");
    exit();
}

// Delete message if requested
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $message_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = ?");
    $stmt->bind_param("i", $message_id);
    $stmt->execute();
    header("Location: contact_messages.php");
    exit();
}

// Determine current page for pagination
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($current_page - 1) * $records_per_page;

// Get filter status if any
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$where_clause = '';
if ($status_filter) {
    $where_clause = "WHERE status = '$status_filter'";
}

// Get total number of messages
$total_query = "SELECT COUNT(*) as total FROM contact_messages $where_clause";
$total_result = $conn->query($total_query);
$total_records = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Get messages for current page with filter if applied
$query = "SELECT * FROM contact_messages $where_clause ORDER BY date_submitted DESC LIMIT $offset, $records_per_page";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Messages</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #ffbf00;
            --primary-dark: #e6ac00;
            --secondary-color: #4a4a4a;
            --text-color: #333;
            --light-bg: #f8f9fa;
            --card-bg: #ffffff;
            --border-radius: 12px;
            --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--light-bg);
            color: var(--text-color);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-color);
        }

        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 8px 16px;
            background-color: var(--card-bg);
            border: 1px solid #e0e0e0;
            border-radius: 30px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .filter-btn:hover, .filter-btn.active {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        .messages-container {
            display: grid;
            gap: 20px;
        }

        .message-card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 20px;
            position: relative;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .message-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }

        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
        }

        .message-info {
            display: flex;
            flex-direction: column;
        }

        .message-name {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .message-email {
            font-size: 0.9rem;
            color: var(--secondary-color);
        }

        .message-date {
            font-size: 0.8rem;
            color: #888;
        }

        .message-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-unread {
            background-color: rgba(244, 67, 54, 0.1);
            color: #f44336;
        }

        .status-read {
            background-color: rgba(33, 150, 243, 0.1);
            color: #2196f3;
        }

        .status-replied {
            background-color: rgba(76, 175, 80, 0.1);
            color: #4caf50;
        }

        .message-content {
            margin-bottom: 20px;
        }

        .message-subject {
            font-weight: 600;
            margin-bottom: 8px;
        }

        .message-text {
            color: var(--text-color);
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            white-space: pre-line;
        }

        .message-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .action-btn {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            border: none;
        }

        .btn-read {
            background-color: #2196f3;
            color: white;
        }

        .btn-replied {
            background-color: #4caf50;
            color: white;
        }

        .btn-delete {
            background-color: #f44336;
            color: white;
        }

        .btn-read:hover, .btn-replied:hover, .btn-delete:hover {
            opacity: 0.9;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 40px;
        }

        .pagination a, .pagination span {
            display: inline-block;
            padding: 8px 16px;
            background-color: var(--card-bg);
            border-radius: 8px;
            text-decoration: none;
            color: var(--text-color);
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .pagination a:hover, .pagination span.current {
            background-color: var(--primary-color);
            color: white;
        }

        .back-btn {
            text-decoration: none;
            color: var(--text-color);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            color: var(--primary-color);
        }

        .count-badge {
            font-size: 0.8rem;
            margin-left: 5px;
            background-color: #f0f0f0;
            padding: 2px 6px;
            border-radius: 30px;
        }

        .contact-details {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
        }

        .detail-label {
            font-size: 0.8rem;
            color: #888;
            margin-bottom: 5px;
        }

        .detail-value {
            font-weight: 500;
        }

        .no-messages {
            background-color: var(--card-bg);
            padding: 30px;
            text-align: center;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        .no-messages p {
            color: #888;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .filters {
                width: 100%;
                overflow-x: auto;
                padding-bottom: 10px;
            }
            
            .message-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .message-actions {
                margin-top: 15px;
                width: 100%;
            }
            
            .action-btn {
                flex: 1;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
<div class="main-content">
    <div class="container">
        <div class="header">
            <h1>Contact Form Messages</h1>
            <a href="../admin/index.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>
        
        <div class="filters">
            <a href="contact_messages.php" class="filter-btn <?php echo !$status_filter ? 'active' : ''; ?>">
                All <span class="count-badge"><?php echo $conn->query("SELECT COUNT(*) as count FROM contact_messages")->fetch_assoc()['count']; ?></span>
            </a>
            <a href="contact_messages.php?status=unread" class="filter-btn <?php echo $status_filter === 'unread' ? 'active' : ''; ?>">
                Unread <span class="count-badge"><?php echo $conn->query("SELECT COUNT(*) as count FROM contact_messages WHERE status='unread'")->fetch_assoc()['count']; ?></span>
            </a>
            <a href="contact_messages.php?status=read" class="filter-btn <?php echo $status_filter === 'read' ? 'active' : ''; ?>">
                Read <span class="count-badge"><?php echo $conn->query("SELECT COUNT(*) as count FROM contact_messages WHERE status='read'")->fetch_assoc()['count']; ?></span>
            </a>
            <a href="contact_messages.php?status=replied" class="filter-btn <?php echo $status_filter === 'replied' ? 'active' : ''; ?>">
                Replied <span class="count-badge"><?php echo $conn->query("SELECT COUNT(*) as count FROM contact_messages WHERE status='replied'")->fetch_assoc()['count']; ?></span>
            </a>
        </div>
        
        <div class="messages-container">
            <?php
            if ($result && $result->num_rows > 0) {
                while ($message = $result->fetch_assoc()) {
                    $status_class = 'status-' . $message['status'];
                    $formatted_date = date('M d, Y h:i A', strtotime($message['date_submitted']));
                    ?>
                    <div class="message-card">
                        <div class="message-header">
                            <div class="message-info">
                                <div class="message-name"><?php echo htmlspecialchars($message['name']); ?></div>
                                <div class="message-email"><?php echo htmlspecialchars($message['email']); ?></div>
                                <div class="message-date"><?php echo $formatted_date; ?></div>
                            </div>
                            <span class="message-status <?php echo $status_class; ?>">
                                <?php echo ucfirst($message['status']); ?>
                            </span>
                        </div>
                        
                        <div class="contact-details">
                            <?php if (!empty($message['phone'])): ?>
                            <div class="detail-item">
                                <div class="detail-label">Phone:</div>
                                <div class="detail-value"><?php echo htmlspecialchars($message['phone']); ?></div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($message['subject'])): ?>
                            <div class="detail-item">
                                <div class="detail-label">Subject:</div>
                                <div class="detail-value"><?php echo htmlspecialchars($message['subject']); ?></div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="message-content">
                            <div class="message-text"><?php echo htmlspecialchars($message['message']); ?></div>
                        </div>
                        
                        <div class="message-actions">
                            <?php if ($message['status'] === 'unread'): ?>
                            <a href="contact_messages.php?mark_as_read=<?php echo $message['id']; ?>" class="action-btn btn-read">
                                <i class="fas fa-check"></i> Mark as Read
                            </a>
                            <?php endif; ?>
                            
                            <?php if ($message['status'] !== 'replied'): ?>
                            <a href="contact_messages.php?mark_as_replied=<?php echo $message['id']; ?>" class="action-btn btn-replied">
                                <i class="fas fa-reply"></i> Mark as Replied
                            </a>
                            <?php endif; ?>
                            
                            <a href="contact_messages.php?delete=<?php echo $message['id']; ?>" class="action-btn btn-delete" onclick="return confirm('Are you sure you want to delete this message?')">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </div>
                    </div>
                    <?php
                }
            } else {
                ?>
                <div class="no-messages">
                    <i class="fas fa-inbox fa-3x" style="color: #ddd; margin-bottom: 20px;"></i>
                    <p>No messages found.</p>
                    <a href="contact_messages.php" class="filter-btn">View All Messages</a>
                </div>
                <?php
            }
            ?>
        </div>
        
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($current_page > 1): ?>
            <a href="contact_messages.php?page=<?php echo $current_page - 1; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?>">
                <i class="fas fa-chevron-left"></i> Previous
            </a>
            <?php endif; ?>
            
            <?php
            $start_page = max(1, $current_page - 2);
            $end_page = min($start_page + 4, $total_pages);
            
            for ($i = $start_page; $i <= $end_page; $i++): 
            ?>
                <?php if ($i == $current_page): ?>
                <span class="current"><?php echo $i; ?></span>
                <?php else: ?>
                <a href="contact_messages.php?page=<?php echo $i; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?>">
                    <?php echo $i; ?>
                </a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($current_page < $total_pages): ?>
            <a href="contact_messages.php?page=<?php echo $current_page + 1; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?>">
                Next <i class="fas fa-chevron-right"></i>
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div></body>
</html> 