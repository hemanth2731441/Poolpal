<?php
// Script to fix profile photo and phone number issues
session_start();
include 'db.php';

echo "<h1>PoolPal Profile Data Fix</h1>";

try {
    // 1. Check if the user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo "<p>You need to be logged in to run this fix. <a href='login.php'>Click here to login</a></p>";
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    
    // 2. Get user data from database
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo "<p>Error: User not found in database.</p>";
        exit;
    }
    
    $user = $result->fetch_assoc();
    
    echo "<h2>Current User Data</h2>";
    echo "<p>User ID: " . htmlspecialchars($user_id) . "</p>";
    echo "<p>Name: " . htmlspecialchars($user['full_name']) . "</p>";
    echo "<p>Email: " . htmlspecialchars($user['email']) . "</p>";
    
    // 3. Fix profile photo
    $profile_fixed = false;
    if (!empty($user['profile_photo']) && (!isset($_SESSION['profile_photo']) || empty($_SESSION['profile_photo']))) {
        $_SESSION['profile_photo'] = $user['profile_photo'];
        $profile_fixed = true;
        echo "<p class='success'>✅ Profile photo fixed: Set from database.</p>";
    } else if (empty($user['profile_photo']) && isset($_SESSION['profile_photo']) && !empty($_SESSION['profile_photo'])) {
        // Update the database with the session value
        $stmt = $conn->prepare("UPDATE users SET profile_photo = ? WHERE id = ?");
        $stmt->bind_param("si", $_SESSION['profile_photo'], $user_id);
        $stmt->execute();
        $profile_fixed = true;
        echo "<p class='success'>✅ Profile photo fixed: Updated database from session.</p>";
    } else if (!empty($user['profile_photo'])) {
        echo "<p>Profile photo already set correctly.</p>";
    } else {
        echo "<p class='warning'>⚠️ No profile photo available in database or session.</p>";
    }
    
    // 4. Fix phone number
    $phone_fixed = false;
    if (!empty($user['phone']) && (!isset($_SESSION['phone']) || empty($_SESSION['phone']))) {
        $_SESSION['phone'] = $user['phone'];
        $phone_fixed = true;
        echo "<p class='success'>✅ Phone number fixed: Set from database.</p>";
    } else if (empty($user['phone']) && isset($_SESSION['phone']) && !empty($_SESSION['phone'])) {
        // Update the database with the session value
        $stmt = $conn->prepare("UPDATE users SET phone = ? WHERE id = ?");
        $stmt->bind_param("si", $_SESSION['phone'], $user_id);
        $stmt->execute();
        $phone_fixed = true;
        echo "<p class='success'>✅ Phone number fixed: Updated database from session.</p>";
    } else if (!empty($user['phone'])) {
        echo "<p>Phone number already set correctly.</p>";
    } else {
        echo "<p class='warning'>⚠️ No phone number available in database or session.</p>";
    }
    
    // 5. Show current status after fixes
    echo "<h2>Session Data After Fix</h2>";
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
    
    // 6. Provide testing links
    echo "<h2>Next Steps</h2>";
    if ($profile_fixed || $phone_fixed) {
        echo "<p class='success'>Fixes have been applied! Please visit your profile to verify the changes.</p>";
    } else {
        echo "<p>No fixes were needed. Your profile data appears to be correct.</p>";
    }
    
    echo "<div class='links'>";
    echo "<a href='profile.php' class='btn'>View Your Profile</a>";
    echo "<a href='profile_debug.php' class='btn'>Run Debug Tool</a>";
    echo "<a href='dashboard.php' class='btn'>Go to Dashboard</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
}
?>

<style>
    body {
        font-family: Arial, sans-serif;
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
        line-height: 1.6;
    }
    h1, h2 {
        color: #333;
    }
    .success {
        color: #155724;
        background-color: #d4edda;
        padding: 10px;
        border-radius: 5px;
    }
    .warning {
        color: #856404;
        background-color: #fff3cd;
        padding: 10px;
        border-radius: 5px;
    }
    .error {
        color: #721c24;
        background-color: #f8d7da;
        padding: 10px;
        border-radius: 5px;
    }
    pre {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
        overflow: auto;
    }
    .links {
        margin-top: 30px;
        display: flex;
        gap: 15px;
    }
    .btn {
        display: inline-block;
        background-color: #ffbf00;
        color: white;
        padding: 10px 15px;
        text-decoration: none;
        border-radius: 5px;
        font-weight: bold;
    }
    .btn:hover {
        background-color: #e6ac00;
    }
</style> 