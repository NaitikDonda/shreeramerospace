<?php
session_start();
header('Content-type: application/json');

$dbFile = __DIR__ . '/../../database.sqlite';
$db = new \PDO('sqlite:' . $dbFile);
$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

try {
    $uniqueId = uniqid();
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $company = $_POST['company'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';
    $industry = $_POST['industry'] ?? '';
    
    $stmt = $db->prepare('
        INSERT INTO submissions (unique_id, name, email, phone, company, subject, message, industry)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $stmt->execute([$uniqueId, $name, $email, $phone, $company, $subject, $message, $industry]);
    
    $submissionId = $db->lastInsertId();
    
    // Handle file uploads
    $uploadDir = __DIR__ . '/../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    foreach ($_FILES as $fieldName => $file) {
        if ($file['error'] === UPLOAD_ERR_OK) {
            $fileName = $uniqueId . '_' . $file['name'];
            $filePath = $uploadDir . $fileName;
            
            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                $stmt = $db->prepare('
                    INSERT INTO files (submission_id, file_name, file_path, file_size, file_type)
                    VALUES (?, ?, ?, ?, ?)
                ');
                $stmt->execute([
                    $submissionId,
                    $file['name'],
                    'uploads/' . $fileName,
                    $file['size'],
                    $file['type']
                ]);
            }
        }
    }
    
    echo json_encode(['success' => true, 'message' => 'Submission created successfully']);
} catch (\PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
