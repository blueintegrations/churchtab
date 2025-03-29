<?php
require_once __DIR__ . '/../models/Tab.php';

class TabImporter {
    private $tabModel;
    private $tabsDirectory;

    public function __construct($tabsDirectory) {
        $this->tabModel = new Tab();
        $this->tabsDirectory = $tabsDirectory;
    }

    private function processHtmlContent($content) {
        error_log("IMPORT: Content length before: " . strlen($content));
        error_log("IMPORT: First 100 bytes before: " . bin2hex(substr($content, 0, 100)));
        
        // Convert to UTF-8 if needed
        if (!mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'Windows-1252');
            error_log("IMPORT: Converted to UTF-8");
        }
        
        // Replace "By:" with "Author:"
        $content = preg_replace('/<p[^>]*>By:\s*([^<]+)<\/p>/i', '<p>Author: $1</p>', $content);
        
        // Clean up any potential UTF-8 issues
        $content = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $content);
        
        error_log("IMPORT: Content length after: " . strlen($content));
        error_log("IMPORT: First 100 bytes after: " . bin2hex(substr($content, 0, 100)));
        
        return $content;
    }

    private function cleanKeySignature($key) {
        // Remove any HTML tags
        $key = strip_tags($key);
        
        // Remove any non-alphanumeric characters except for # and b
        $key = preg_replace('/[^A-Za-z0-9#b]/', '', $key);
        
        // Limit to 10 characters
        return substr($key, 0, 10);
    }

    private function parseIndexFile($filePath) {
        $content = file_get_contents($filePath);
        $songs = [];
        $totalLinks = 0;
        $foundFiles = 0;
        $missingFiles = [];
        $duplicateFiles = [];
        
        // Convert content to UTF-8 if needed
        if (!mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'Windows-1252');
        }
        
        // Find all table rows that contain song links
        if (preg_match_all('/<tr[^>]*>(.*?)<\/tr>/si', $content, $rows)) {
            foreach ($rows[1] as $row) {
                // Extract all cells from the row
                if (preg_match_all('/<td[^>]*>(.*?)<\/td>/si', $row, $cells)) {
                    // Look for a link in the first cell
                    if (preg_match('/<a\s+href=(["\'])(.*?)\1[^>]*>(.*?)<\/a>/si', $cells[1][0], $link)) {
                        $filename = trim($link[2]);
                        $title = trim(strip_tags($link[3]));
                        
                        // Skip if this is a section header (e.g., #A, #B, etc.)
                        if (preg_match('/^#[A-Z]$/', $filename)) {
                            continue;
                        }
                        
                        // Skip if filename doesn't end in .htm
                        if (!preg_match('/\.htm$/i', $filename)) {
                            continue;
                        }
                        
                        $totalLinks++;
                        
                        // Initialize song data
                        $songData = [
                            'file' => $filename,
                            'title' => $title,
                            'key' => '',
                            'leader' => '',
                            'author' => ''
                        ];
                        
                        // Extract key from second cell if it exists
                        if (isset($cells[1][1])) {
                            $key = trim(strip_tags($cells[1][1]));
                            if (!empty($key) && strtoupper($key) !== 'KEY') {
                                $songData['key'] = $this->cleanKeySignature($key);
                            }
                        }
                        
                        // Extract leader from third cell if it exists
                        if (isset($cells[1][2])) {
                            $leader = trim(strip_tags($cells[1][2]));
                            if (!empty($leader) && strtoupper($leader) !== 'LEADER') {
                                $songData['leader'] = $leader;
                            }
                        }
                        
                        // Extract author from fourth cell if it exists
                        if (isset($cells[1][3])) {
                            $author = trim(strip_tags($cells[1][3]));
                            if (!empty($author) && strtoupper($author) !== 'AUTHOR') {
                                $songData['author'] = $author;
                            }
                        }
                        
                        // Check if file exists in any form
                        $actualFile = $this->findMatchingFile($songData['file']);
                        if ($actualFile) {
                            $foundFiles++;
                            $songData['file'] = $actualFile;
                            
                            // Check for duplicates
                            $isDuplicate = false;
                            foreach ($songs as $existing) {
                                if ($existing['file'] === $actualFile) {
                                    $duplicateFiles[] = $actualFile;
                                    $isDuplicate = true;
                                    break;
                                }
                            }
                            
                            if (!$isDuplicate) {
                                $songs[] = $songData;
                            }
                        } else {
                            $missingFiles[] = $songData['file'];
                        }
                    }
                }
            }
        }
        
        echo "\nIndex Analysis:\n";
        echo "Total links found: $totalLinks\n";
        echo "Files found: $foundFiles\n";
        echo "Files missing: " . count($missingFiles) . "\n";
        echo "Duplicate files: " . count($duplicateFiles) . "\n\n";
        
        if (!empty($duplicateFiles)) {
            echo "Duplicate files:\n";
            foreach ($duplicateFiles as $file) {
                echo "- $file\n";
            }
            echo "\n";
        }
        
        if (!empty($missingFiles)) {
            echo "Missing files:\n";
            foreach ($missingFiles as $file) {
                echo "- $file\n";
            }
            echo "\n";
        }
        
        return $songs;
    }

    private function findMatchingFile($filename) {
        // Try exact match first
        if (file_exists($this->tabsDirectory . '/' . $filename)) {
            return $filename;
        }
        
        // Try with underscores instead of spaces
        $underscore = str_replace(' ', '_', $filename);
        if (file_exists($this->tabsDirectory . '/' . $underscore)) {
            return $underscore;
        }
        
        // Try with spaces instead of underscores
        $spaces = str_replace('_', ' ', $filename);
        if (file_exists($this->tabsDirectory . '/' . $spaces)) {
            return $spaces;
        }
        
        // Try with URL decoded name
        $decoded = urldecode($filename);
        if (file_exists($this->tabsDirectory . '/' . $decoded)) {
            return $decoded;
        }
        
        // Try with URL decoded name and underscores
        $decodedUnderscore = str_replace(' ', '_', $decoded);
        if (file_exists($this->tabsDirectory . '/' . $decodedUnderscore)) {
            return $decodedUnderscore;
        }
        
        // Try with URL decoded name and spaces
        $decodedSpaces = str_replace('_', ' ', $decoded);
        if (file_exists($this->tabsDirectory . '/' . $decodedSpaces)) {
            return $decodedSpaces;
        }
        
        return null;
    }

    public function import() {
        // First parse the index file to get song metadata
        $songs = $this->parseIndexFile($this->tabsDirectory . '/index-susie.htm');
        
        // Clear existing tabs
        $this->tabModel->clearAllTabs();
        echo "Cleared existing tabs from database.\n\n";

        $imported = 0;
        $errors = [];

        foreach ($songs as $song) {
            try {
                if (empty($song['file'])) {
                    $errors[] = "- Empty filename in song data";
                    continue;
                }

                // Find the actual file that matches
                $actualFile = $this->findMatchingFile($song['file']);
                if (!$actualFile) {
                    $errors[] = "- Error processing {$song['file']}: File not found";
                    continue;
                }

                $content = file_get_contents($this->tabsDirectory . '/' . $actualFile);
                if ($content === false) {
                    $errors[] = "- Error reading {$actualFile}: Could not read file contents";
                    continue;
                }

                $content = $this->processHtmlContent($content);
                
                error_log("Content before insert (first 100 bytes): " . bin2hex(substr($content, 0, 100)));

                if ($this->tabModel->addTab(
                    $song['title'],
                    '', // artist
                    $content,
                    1, // category_id
                    $song['key'] ?? '',
                    $song['leader'] ?? '',
                    $song['author'] ?? ''
                )) {
                    $imported++;
                    echo "Imported: {$song['title']}" . 
                         (isset($song['key']) ? " ({$song['key']})" : '') .
                         (isset($song['leader']) ? " - Leader: {$song['leader']}" : '') .
                         (isset($song['author']) ? " - Author: {$song['author']}" : '') . "\n";
                } else {
                    $errors[] = "- Error importing {$actualFile}: Database insert failed";
                }
            } catch (Exception $e) {
                $errors[] = "- Error processing {$song['file']}: " . $e->getMessage();
            }
        }

        if (!empty($errors)) {
            echo "\nErrors:\n" . implode("\n", $errors);
        }

        echo "\nImported $imported songs successfully.";
    }
}

// Run the importer
$importer = new TabImporter('/var/www/html/churchtab/tabs');
$importer->import();
