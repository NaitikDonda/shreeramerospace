<?php
header('Content-Type: application/json');

try {

    $dbFile = __DIR__ . '/../../database.sqlite';

    $db = new PDO('sqlite:' . $dbFile);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $id = $_POST['id'] ?? '';

    if (!$id) {
        throw new Exception('Submission ID missing');
    }

    $stmt = $db->prepare("
        UPDATE submissions
        SET
            name = ?,
            email = ?,
            phone = ?,
            subject = ?,
            message = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $_POST['name'] ?? '',
        $_POST['email'] ?? '',
        $_POST['phone'] ?? '',
        $_POST['subject'] ?? '',
        $_POST['message'] ?? '',
        $id
    ]);

    echo json_encode([
        'success' => true
    ]);

} catch (Exception $e) {

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>