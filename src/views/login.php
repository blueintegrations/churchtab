<?php
if (isset($_SESSION['user_id'])) {
    header('Location: /index.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>ChurchTab - Login</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Login</h1>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($_SESSION['error']); ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($_SESSION['success']); ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        <form method="post" action="/auth.php">
            <input type="hidden" name="action" value="login">
            <div class="form-group">
                <label>Username:</label>
                <input type="text" name="username" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
        
    </div>
    <script>
    // Debug info
    console.log('Login form loaded');
    document.querySelector('form').addEventListener('submit', function(e) {
        console.log('Form submitted');
        console.log('Action:', this.action);
        console.log('Method:', this.method);
        console.log('Username:', this.username.value);
    });
    </script>
</body>
</html>
