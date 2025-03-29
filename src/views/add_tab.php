<?php
require_once __DIR__ . '/../models/Tab.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /index.php?action=login');
    exit;
}

$tab = new Tab();
$categories = $tab->getCategories();

// Helper function to safely display potentially null values
function safeHtml($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>ChurchTab - Add New Tab</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="container">
        <nav class="nav">
            <ul class="nav-list">
                <li class="nav-item"><a href="/index.php">Home</a></li>
                <li class="nav-item"><a href="/index.php?action=tabs">Tabs</a></li>
                <li class="nav-item"><a href="/index.php?action=add_tab">Add Tab</a></li>
                <li class="nav-item"><a href="/index.php?action=schedule">Schedule</a></li>
                <?php if ($_SESSION['is_admin'] ?? false): ?>
                    <li class="nav-item"><a href="/index.php?action=admin">Admin</a></li>
                <?php endif; ?>
            </ul>
        </nav>

        <h1>Add New Tab</h1>

        <?php if (isset($_GET['error'])): ?>
            <div class="error-message">
                <?php echo safeHtml($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Add New Tab Form -->
        <div class="add-tab-section">
            <div class="input-type-selector">
                <button class="tab-button active" data-form="file-upload">Upload File</button>
                <button class="tab-button" data-form="manual-entry">Manual Entry</button>
            </div>

            <!-- File Upload Form -->
            <form method="post" action="/index.php" enctype="multipart/form-data" id="file-upload-form" class="tab-form active">
                <input type="hidden" name="action" value="add_tab">
                <input type="hidden" name="input_type" value="file">
                
                <div class="form-group">
                    <label>Upload HTML File:</label>
                    <div class="file-input-wrapper">
                        <input type="file" name="tab_file" accept=".html,.htm" class="file-input" required>
                        <p class="help-text">The title, artist, and key will be extracted from the filename (e.g., "Song Title - Am.htm" or "Artist - Song_Title.htm")</p>
                    </div>
                </div>

                <div class="form-group">
                    <label>Category:</label>
                    <select name="category_id" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo safeHtml($category['id']); ?>">
                                <?php echo safeHtml($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Key Signature:</label>
                    <input type="text" name="key_signature" placeholder="e.g. Am, D, F#m" maxlength="10">
                    <p class="help-text">Optional. If specified, this will override any key found in the filename.</p>
                </div>

                <div class="form-group">
                    <label>Leader:</label>
                    <input type="text" name="leader" placeholder="e.g. John, Sarah" maxlength="255">
                    <p class="help-text">Optional. The person who typically leads this song.</p>
                </div>

                <div class="form-group">
                    <label>Author:</label>
                    <input type="text" name="author" placeholder="e.g. Chris Tomlin, Matt Redman" maxlength="255">
                    <p class="help-text">Optional. The person or group who wrote/composed the song.</p>
                </div>

                <div class="extracted-info" style="display: none;">
                    <h3>Extracted Information</h3>
                    <p>Title: <span id="extracted-title"></span></p>
                    <p>Artist: <span id="extracted-artist"></span></p>
                    <p>Key: <span id="extracted-key"></span></p>
                </div>

                <button type="submit" class="submit-button">Upload Tab</button>
            </form>

            <!-- Manual Entry Form -->
            <form method="post" action="/index.php" id="manual-entry-form" class="tab-form">
                <input type="hidden" name="action" value="add_tab">
                <input type="hidden" name="input_type" value="manual">
                
                <div class="form-group">
                    <label>Title:</label>
                    <input type="text" name="title" required>
                </div>

                <div class="form-group">
                    <label>Artist:</label>
                    <input type="text" name="artist" required>
                </div>

                <div class="form-group">
                    <label>Key Signature:</label>
                    <input type="text" name="key_signature" placeholder="e.g. Am, D, F#m" maxlength="10">
                    <p class="help-text">Optional. The key signature of the song.</p>
                </div>

                <div class="form-group">
                    <label>Leader:</label>
                    <input type="text" name="leader" placeholder="e.g. John, Sarah" maxlength="255">
                    <p class="help-text">Optional. The person who typically leads this song.</p>
                </div>

                <div class="form-group">
                    <label>Author:</label>
                    <input type="text" name="author" placeholder="e.g. Chris Tomlin, Matt Redman" maxlength="255">
                    <p class="help-text">Optional. The person or group who wrote/composed the song.</p>
                </div>

                <div class="form-group">
                    <label>Content:</label>
                    <textarea name="content" rows="20" required></textarea>
                </div>

                <div class="form-group">
                    <label>Category:</label>
                    <select name="category_id" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo safeHtml($category['id']); ?>">
                                <?php echo safeHtml($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="submit-button">Add Tab</button>
            </form>
        </div>
    </div>

    <style>
    .error-message {
        background-color: #ffebee;
        color: #c62828;
        padding: 10px;
        margin-bottom: 20px;
        border: 1px solid #ef9a9a;
        border-radius: 4px;
        text-align: center;
    }

    .add-tab-section {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
        background-color: var(--black-light);
        border: 1px solid var(--gold-dark);
        border-radius: 8px;
    }

    .input-type-selector {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        border-bottom: 1px solid var(--gold-dark);
        padding-bottom: 10px;
    }

    .tab-button {
        background-color: var(--black-dark);
        color: var(--text-light);
        border: 1px solid var(--gold-dark);
        padding: 10px 20px;
        cursor: pointer;
        border-radius: 4px;
        transition: all 0.3s ease;
    }

    .tab-button.active {
        background-color: var(--gold-primary);
        color: var(--black-dark);
    }

    .tab-form {
        display: none;
    }

    .tab-form.active {
        display: block;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        color: var(--gold-primary);
    }

    .help-text {
        font-size: 0.9em;
        color: var(--text-light);
        margin-top: 5px;
        opacity: 0.8;
    }

    .form-group input[type="text"],
    .form-group select {
        width: 100%;
        padding: 10px;
        border: 1px solid var(--gold-dark);
        background-color: var(--black-dark);
        color: var(--text-light);
        border-radius: 4px;
    }

    .file-input-wrapper {
        margin-top: 10px;
    }

    .file-input {
        display: block;
        color: var(--text-light);
        margin-bottom: 10px;
    }

    .tab-content {
        width: 100%;
        min-height: 300px;
        padding: 10px;
        border: 1px solid var(--gold-dark);
        background-color: var(--black-dark);
        color: var(--text-light);
        font-family: monospace;
        white-space: pre;
        margin-top: 10px;
    }

    .extracted-info {
        margin: 20px 0;
        padding: 15px;
        background-color: var(--black-dark);
        border: 1px solid var(--gold-dark);
        border-radius: 4px;
    }

    .extracted-info h3 {
        color: var(--gold-primary);
        margin-top: 0;
    }

    .submit-button {
        background-color: var(--gold-primary);
        color: var(--black-dark);
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-weight: bold;
        transition: background-color 0.3s ease;
    }

    .submit-button:hover {
        background-color: var(--gold-light);
    }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabForms = document.querySelectorAll('.tab-form');
        const fileInput = document.querySelector('input[type="file"]');
        const extractedInfo = document.querySelector('.extracted-info');
        const extractedTitle = document.getElementById('extracted-title');
        const extractedArtist = document.getElementById('extracted-artist');
        const extractedKey = document.getElementById('extracted-key');

        // Tab switching
        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabForms.forEach(form => form.classList.remove('active'));
                
                button.classList.add('active');
                const formId = button.getAttribute('data-form');
                document.getElementById(formId + '-form').classList.add('active');
            });
        });

        // File name parsing
        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                // Remove extension
                let filename = file.name.replace(/\.[^/.]+$/, "");
                
                // Try to extract information
                let title = '', artist = '', key = '';
                
                // Check for artist - title format
                if (filename.includes(' - ')) {
                    [artist, title] = filename.split(' - ');
                } else {
                    title = filename;
                }
                
                // Check for key at the end
                const keyMatch = title.match(/\s+([A-G][#b]?m?)$/);
                if (keyMatch) {
                    key = keyMatch[1];
                    title = title.replace(/\s+[A-G][#b]?m?$/, '');
                }
                
                // Clean up the title
                title = title.replace(/_/g, ' ').trim();
                artist = artist.replace(/_/g, ' ').trim();
                
                // Update the display
                extractedTitle.textContent = title;
                extractedArtist.textContent = artist || 'Not found';
                extractedKey.textContent = key || 'Not found';
                extractedInfo.style.display = 'block';
            } else {
                extractedInfo.style.display = 'none';
            }
        });
    });
    </script>
</body>
</html>
