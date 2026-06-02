<?php
session_start();
header('Content-Type: application/json');

try {

    $dbFile = __DIR__ . '/../../database.sqlite';

    $db = new PDO('sqlite:' . $dbFile);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $db->query("
        SELECT s.*, 
               f.id as file_id,
               f.file_name,
               f.file_path,
               f.file_size,
               f.file_type
        FROM submissions s
        LEFT JOIN files f ON s.id = f.submission_id
        ORDER BY s.id DESC, f.id ASC
    ");

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Group files by submission
    $submissions = [];
    foreach ($rows as $row) {
        $submissionId = $row['id'];
        
        if (!isset($submissions[$submissionId])) {
            $submissions[$submissionId] = [
                'id' => $row['id'],
                'unique_id' => $row['unique_id'],
                'name' => $row['name'],
                'email' => $row['email'],
                'phone' => $row['phone'],
                'company' => $row['company'],
                'subject' => $row['subject'],
                'message' => $row['message'],
                'industry' => $row['industry'],
                'timestamp' => $row['timestamp'],
                'files' => []
            ];
        }
        
        // Add file if exists
        if ($row['file_id']) {
            $submissions[$submissionId]['files'][] = [
                'file_id' => $row['file_id'],
                'file_name' => $row['file_name'],
                'file_path' => $row['file_path'],
                'file_size' => $row['file_size'],
                'file_type' => $row['file_type']
            ];
        }
    }

    // Re-index array
    $submissions = array_values($submissions);

    echo json_encode($submissions);

} catch (Exception $e) {

    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
?>