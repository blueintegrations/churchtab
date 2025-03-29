<?php
require_once __DIR__ . '/../models/Tab.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /index.php?action=login');
    exit;
}

$tab = new Tab();
$tabId = $_GET['id'] ?? null;

if (!$tabId) {
    echo "No song specified";
    exit;
}

$result = $tab->getTab($tabId);

if (!$result) {
    echo "Song not found";
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($result['title']); ?></title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #fff;
        }
        .song-header {
            margin-bottom: 20px;
        }
        .song-title {
            font-size: 1.5em;
            margin-bottom: 10px;
        }
        .song-meta {
            font-size: 0.9em;
            color: #666;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        .meta-item {
            display: flex;
            gap: 5px;
        }
        .meta-label {
            font-weight: 500;
        }
        .content-frame {
            width: 100%;
            height: 600px;
            border: none;
            background: #fff;
            font-family: 'Courier New', monospace;
        }
    </style>
    <script>
        function resizeIframe(obj) {
            obj.style.height = obj.contentWindow.document.documentElement.scrollHeight + "px";
        }
    </script>
</head>
<body>
    <div class="song-header">
        <div class="song-title"><?php echo htmlspecialchars($result['title']); ?></div>
        <div class="song-meta">
            <?php if (!empty($result['author'])): ?>
                <div class="meta-item">
                    <span class="meta-label">Author:</span>
                    <span><?php echo htmlspecialchars($result['author']); ?></span>
                </div>
            <?php endif; ?>
            <?php if (!empty($result['key_signature'])): ?>
                <div class="meta-item">
                    <span class="meta-label">Key:</span>
                    <span><?php echo htmlspecialchars($result['key_signature']); ?></span>
                </div>
            <?php endif; ?>
            <?php if (!empty($result['leader'])): ?>
                <div class="meta-item">
                    <span class="meta-label">Leader:</span>
                    <span><?php echo htmlspecialchars($result['leader']); ?></span>
                </div>
            <?php endif; ?>
            <div class="meta-item">
                <span class="meta-label">Category:</span>
                <span><?php echo htmlspecialchars($result['category_name'] ?? 'Uncategorized'); ?></span>
            </div>
        </div>
    </div>
    <iframe class="content-frame" 
            srcdoc="<?php echo htmlspecialchars($result['content']); ?>" 
            onload="resizeIframe(this)"
            title="Song Content">
    </iframe>
</body>
</html>
