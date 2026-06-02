<?php
session_start();
header('Content-type: application/json');

$dbFile = realpath(__DIR__ . '/../database.sqlite');
$db = new \PDO('sqlite:' . $dbFile);
$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

try {
    $sessionId = session_id();
    $stmt = $db->prepare('DELETE FROM sessions WHERE id = ?');
    $stmt->execute([$sessionId]);
    
    session_destroy();
    echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
} catch (\PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
