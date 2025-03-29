<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../src/models/User.php';
require_once __DIR__ . '/../src/models/Tab.php';

$action = $_GET['action'] ?? 'home';

if (!isset($_SESSION['user_id']) && $action != 'login' && $action != 'register') {
    header('Location: index.php?action=login');
    exit;
}

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($_POST['action'] ?? '') {
        case 'add_tab':
            $tab = new Tab();
            
            // Get the content based on input type
            $content = '';
            if ($_POST['input_type'] === 'file' && isset($_FILES['tab_file'])) {
                if ($_FILES['tab_file']['error'] === UPLOAD_ERR_OK) {
                    $content = file_get_contents($_FILES['tab_file']['tmp_name']);
                    
                    // Process content exactly like import_tabs.php
                    if (!mb_check_encoding($content, 'UTF-8')) {
                        $content = mb_convert_encoding($content, 'UTF-8', 'Windows-1252');
                    }
                    $content = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $content);
                    
                    // If it's an HTML file, extract just the body content
                    if (preg_match('/<body[^>]*>(.*?)<\/body>/si', $content, $matches)) {
                        $content = $matches[1];
                    }
                } else {
                    header('Location: /index.php?action=add_tab&error=File upload failed');
                    exit;
                }
            } else {
                $content = $_POST['content'] ?? '';
            }
            
            if (empty($content)) {
                header('Location: /index.php?action=add_tab&error=No content provided');
                exit;
            }

            $categoryId = $_POST['category_id'] ?? 1;
            if (empty($categoryId)) {
                header('Location: /index.php?action=add_tab&error=Category is required');
                exit;
            }

            // Extract info from filename
            $title = '';
            $artist = '';
            $keySignature = '';
            
            $filename = basename($_FILES['tab_file']['name'], '.htm');
            $filename = basename($filename, '.html');

            // Check for artist - title format
            if (strpos($filename, ' - ') !== false) {
                list($artist, $title) = explode(' - ', $filename, 2);
            } else {
                $title = $filename;
            }

            // Check for key at the end
            if (preg_match('/\s+([A-G][#b]?m?)$/', $title, $matches)) {
                $keySignature = $matches[1];
                $title = preg_replace('/\s+[A-G][#b]?m?$/', '', $title);
            }

            // Clean up strings
            $title = trim(str_replace('_', ' ', $title));
            $artist = trim(str_replace('_', ' ', $artist));

            if (!$tab->addTab($title, $artist, $content, $categoryId, $keySignature)) {
                header('Location: /index.php?action=add_tab&error=Failed to add tab');
                exit;
            }

            header('Location: /index.php?action=tabs&message=Tab added successfully');
            exit;
            break;

        case 'schedule_tab':
            $tab = new Tab();
            $order = isset($_POST['display_order']) && $_POST['display_order'] !== '' 
                  ? intval($_POST['display_order']) 
                  : null;
            $tab->scheduleTab(
                $_POST['tab_id'],
                $_POST['schedule_date'],
                $order
            );
            header('Location: /index.php?action=schedule&date=' . $_POST['schedule_date'] . '&message=Tab scheduled successfully');
            exit;
            break;
    }
}

// Handle view routing
switch ($action) {
    case 'login':
        include __DIR__ . '/../src/views/login.php';
        break;
    
    case 'register':
        include __DIR__ . '/../src/views/register.php';
        break;
    
    case 'tabs':
        include __DIR__ . '/../src/views/tabs.php';
        break;
    
    case 'add_tab':
        if (!($_SESSION['is_admin'] ?? false)) {
            header('Location: /index.php?action=tabs');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input_type = $_POST['input_type'] ?? '';
            $category_id = $_POST['category_id'] ?? 1;  
            $error = '';
            $tab = new Tab();

            if (empty($category_id)) {
                $error = 'Category is required';
            } else {
                if ($input_type === 'file') {
                    if (!isset($_FILES['tab_file']) || $_FILES['tab_file']['error'] !== UPLOAD_ERR_OK) {
                        $error = 'File upload failed';
                    } else {
                        $file = $_FILES['tab_file'];
                        $filename = basename($file['name'], '.htm');
                        $filename = basename($filename, '.html');

                        // Read and process file content
                        $content = file_get_contents($file['tmp_name']);
                        error_log("UPLOAD: Content length before: " . strlen($content));
                        error_log("UPLOAD: First 100 bytes before: " . bin2hex(substr($content, 0, 100)));
                        
                        if (!$content) {
                            $error = 'Could not read file';
                            break;
                        }

                        // Process content exactly like import_tabs.php
                        if (!mb_check_encoding($content, 'UTF-8')) {
                            $content = mb_convert_encoding($content, 'UTF-8', 'Windows-1252');
                            error_log("UPLOAD: Converted to UTF-8");
                        }
                        
                        // Clean up any potential UTF-8 issues
                        $content = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $content);
                        
                        error_log("UPLOAD: Content length after: " . strlen($content));
                        error_log("UPLOAD: First 100 bytes after: " . bin2hex(substr($content, 0, 100)));
                        
                        // Parse filename to get title and key (same as import_tabs.php)
                        if (preg_match('/^(.+?)(?:\s*[-_]\s*([A-G][#b]?m?))?$/', $filename, $matches)) {
                            $title = str_replace('_', ' ', $matches[1]);
                            // Use form key_signature if provided, otherwise use key from filename
                            $keySignature = !empty($_POST['key_signature']) ? $_POST['key_signature'] : (isset($matches[2]) ? $matches[2] : '');
                        } else {
                            $error = 'Invalid filename format. Expected: "title.htm" or "title - Am.htm"';
                            break;
                        }

                        // Add the tab to the database with the raw HTML content
                        if (!$tab->addTab($title, '', $content, $category_id, $keySignature)) {
                            $error = 'Failed to add tab';
                        }
                    }
                } else if ($input_type === 'manual') {
                    $title = trim($_POST['title'] ?? '');
                    $artist = trim($_POST['artist'] ?? '');
                    $content = trim($_POST['content'] ?? '');
                    $keySignature = trim($_POST['key'] ?? '');

                    if (empty($title) || empty($content)) {
                        $error = 'Title and content are required';
                    } else {
                        if (!$tab->addTab($title, $artist, $content, $category_id, $keySignature)) {
                            $error = 'Failed to add tab';
                        }
                    }
                } else {
                    $error = 'Invalid input type';
                }
            }

            if ($error) {
                header('Location: /index.php?action=add_tab&error=' . urlencode($error));
            } else {
                header('Location: /index.php?action=tabs');
            }
            exit;
        }

        require_once __DIR__ . '/../src/views/add_tab.php';
        break;
    
    case 'schedule':
        include __DIR__ . '/../src/views/schedule.php';
        break;
    
    case 'admin':
        if (!($_SESSION['is_admin'] ?? false)) {
            header('Location: /index.php');
            exit;
        }
        include __DIR__ . '/../src/views/admin.php';
        break;
    
    case 'song_only':
        include __DIR__ . '/../src/views/song_only.php';
        break;
    
    default:
        include __DIR__ . '/../src/views/home.php';
        break;
}
