<?php
require_once __DIR__ . '/../models/User.php';

if (!isset($_SESSION['user_id']) || !($_SESSION['is_admin'] ?? false)) {
    header('Location: /index.php');
    exit;
}

$user = new User();
$users = $user->getAllUsers();
?>
<!DOCTYPE html>
<html>
<head>
    <title>ChurchTab - Admin</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="container">
        <nav class="nav">
            <ul class="nav-list">
                <li class="nav-item"><a href="/index.php">Home</a></li>
                <li class="nav-item"><a href="/index.php?action=tabs">Tabs</a></li>
                <li class="nav-item"><a href="/index.php?action=schedule">Schedule</a></li>
                <li class="nav-item"><a href="/index.php?action=admin">Admin</a></li>
            </ul>
        </nav>

        <h1>Admin Panel</h1>
        
        <div class="admin-section">
            <h2>User Management</h2>
            <div class="user-list">
                <table>
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Admin Status</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($u['username']); ?></td>
                                <td>
                                    <?php if ($u['is_admin']): ?>
                                        <span class="badge admin">Admin</span>
                                    <?php else: ?>
                                        <span class="badge user">User</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('Y-m-d H:i', strtotime($u['created_at'])); ?></td>
                                <td>
                                    <form method="post" action="/index.php?action=admin" style="display: inline;">
                                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                        <input type="hidden" name="action" value="toggle_admin">
                                        <button type="submit" class="btn-small">
                                            <?php echo $u['is_admin'] ? 'Remove Admin' : 'Make Admin'; ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="admin-section">
            <h2>Add New User</h2>
            <form method="post" action="/auth.php">
                <input type="hidden" name="action" value="register">
                <input type="hidden" name="admin_created" value="1">
                <div class="form-group">
                    <label>Username:</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Password:</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label>Confirm Password:</label>
                    <input type="password" name="confirm_password" required>
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_admin" value="1">
                        Make this user an admin
                    </label>
                </div>
                <button type="submit">Add User</button>
            </form>
        </div>
    </div>

    <style>
    .admin-section {
        margin-bottom: 40px;
        padding: 20px;
        background-color: var(--black-light);
        border: 1px solid var(--gold-dark);
        border-radius: 8px;
    }
    
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    
    th, td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid var(--gold-dark);
    }
    
    th {
        background-color: var(--black-dark);
        color: var(--gold-primary);
    }
    
    .badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.9em;
    }
    
    .badge.admin {
        background-color: var(--gold-primary);
        color: var(--black-dark);
    }
    
    .badge.user {
        background-color: var(--black-dark);
        color: var(--gold-primary);
        border: 1px solid var(--gold-primary);
    }
    
    .btn-small {
        padding: 4px 8px;
        font-size: 0.9em;
    }
    
    input[type="checkbox"] {
        margin-right: 8px;
    }
    </style>
</body>
</html>
