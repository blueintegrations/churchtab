<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: /index.php?action=login');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>ChurchTab - Home</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="container">
        <nav class="nav">
            <ul class="nav-list">
                <li class="nav-item"><a href="/index.php">Home</a></li>
                <li class="nav-item"><a href="/index.php?action=tabs">Tabs</a></li>
                <?php if ($_SESSION['is_admin'] ?? false): ?>
                    <li class="nav-item"><a href="/index.php?action=add_tab">Add Tab</a></li>
                <?php endif; ?>
                <li class="nav-item"><a href="/index.php?action=schedule">Schedule</a></li>
                <?php if ($_SESSION['is_admin'] ?? false): ?>
                    <li class="nav-item"><a href="/index.php?action=admin">Admin</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <div class="welcome-content">
            <h1>Welcome to ChurchTab</h1>
            <p>Welcome <?php echo htmlspecialchars($_SESSION['username'] ?? 'Guest'); ?>!</p>
        </div>
        
        <div class="quick-actions">
            <h2>Quick Actions</h2>
            <div class="button-group">
                <a href="/index.php?action=tabs" class="button">View Tabs</a>
                <a href="/index.php?action=schedule" class="button">View Schedule</a>
            </div>
        </div>
    </div>

    <div class="footer">
        Application made with passion by <a href="https://blueintegrations.com" target="_blank" rel="noopener noreferrer">Blue Integrations</a>
    </div>
</body>
</html>
