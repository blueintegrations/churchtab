<?php
require_once __DIR__ . '/Database.php';

class Tab {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function clearAllTabs() {
        // First clear the song_schedule table that references tabs
        $query1 = "DELETE FROM song_schedule";
        $this->db->query($query1);
        
        // Then clear the tabs table
        $query2 = "DELETE FROM tabs";
        return $this->db->query($query2);
    }

    public function getAllTabs() {
        $query = "SELECT t.*, c.name as category_name 
                 FROM tabs t 
                 LEFT JOIN categories c ON t.category_id = c.id 
                 ORDER BY t.title ASC";
        return $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function search($searchTerm) {
        $query = "SELECT t.*, c.name as category_name 
                 FROM tabs t 
                 LEFT JOIN categories c ON t.category_id = c.id 
                 WHERE t.title LIKE :search 
                 OR t.artist LIKE :search 
                 ORDER BY t.title ASC";
        $params = [':search' => "%$searchTerm%"];
        return $this->db->query($query, $params)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCategories() {
        $query = "SELECT * FROM categories ORDER BY name ASC";
        return $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addTab($title, $artist, $content, $categoryId, $keySignature = '', $leader = '', $author = '') {
        // Debug the content
        error_log("Content before insert (first 100 bytes): " . bin2hex(substr($content, 0, 100)));
        
        $query = "INSERT INTO tabs (title, artist, content, category_id, created_by, key_signature, leader, author) 
                 VALUES (:title, :artist, :content, :category_id, :created_by, :key_signature, :leader, :author)";
        $params = [
            ':title' => $title,
            ':artist' => $artist,
            ':content' => $content,
            ':category_id' => $categoryId,
            ':created_by' => $_SESSION['user_id'] ?? null,
            ':key_signature' => $keySignature,
            ':leader' => $leader,
            ':author' => $author
        ];
        return $this->db->query($query, $params);
    }

    public function getScheduledTabs($date) {
        $query = "SELECT t.*, c.name as category_name, s.display_order, s.youtube_link 
                 FROM tabs t 
                 JOIN song_schedule s ON t.id = s.tab_id 
                 LEFT JOIN categories c ON t.category_id = c.id 
                 WHERE s.scheduled_date = :date 
                 ORDER BY s.display_order ASC";
        $params = [':date' => $date];
        return $this->db->query($query, $params)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function scheduleTab($tabId, $date, $order = null, $youtubeLink = null) {
        if ($order === null) {
            $query = "SELECT COALESCE(MAX(display_order), 0) + 1 as next_order 
                     FROM song_schedule 
                     WHERE scheduled_date = :date";
            $params = [':date' => $date];
            $result = $this->db->query($query, $params)->fetch(PDO::FETCH_ASSOC);
            $order = $result['next_order'];
        }

        $query = "INSERT INTO song_schedule (tab_id, scheduled_date, display_order, youtube_link) 
                 VALUES (:tab_id, :date, :order, :youtube_link)";
        $params = [
            ':tab_id' => $tabId,
            ':date' => $date,
            ':order' => $order,
            ':youtube_link' => $youtubeLink
        ];
        return $this->db->query($query, $params);
    }

    public function getTab($id) {
        $query = "SELECT t.*, c.name as category_name 
                 FROM tabs t 
                 LEFT JOIN categories c ON t.category_id = c.id 
                 WHERE t.id = :id";
        $params = [':id' => $id];
        return $this->db->query($query, $params)->fetch(PDO::FETCH_ASSOC);
    }

    public function removeFromSchedule($tabId, $date) {
        $query = "DELETE FROM song_schedule 
                 WHERE tab_id = :tab_id 
                 AND scheduled_date = :date";
        $params = [
            ':tab_id' => $tabId,
            ':date' => $date
        ];
        return $this->db->query($query, $params);
    }

    public function updateYoutubeLink($tabId, $date, $youtubeLink) {
        $query = "UPDATE song_schedule 
                 SET youtube_link = :youtube_link 
                 WHERE tab_id = :tab_id 
                 AND scheduled_date = :date";
        $params = [
            ':tab_id' => $tabId,
            ':date' => $date,
            ':youtube_link' => $youtubeLink
        ];
        return $this->db->query($query, $params);
    }

    public function getYoutubeLink($tabId, $date) {
        $query = "SELECT youtube_link 
                 FROM song_schedule 
                 WHERE tab_id = :tab_id 
                 AND scheduled_date = :date";
        $params = [
            ':tab_id' => $tabId,
            ':date' => $date
        ];
        $result = $this->db->query($query, $params)->fetch(PDO::FETCH_ASSOC);
        return $result['youtube_link'] ?? null;
    }
}
