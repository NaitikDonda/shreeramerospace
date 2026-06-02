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
        DELETE FROM submissions
        WHERE id = ?
    ");

    $stmt->execute([$id]);

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