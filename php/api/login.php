<?php
session_start();
header('Content-Type: application/json');

try {
$dbFile = __DIR__ . '/../../database.sqlite';

    if (!$dbFile) {
        throw new Exception('Database file not found');
    }

    $db = new PDO('sqlite:' . $dbFile);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Username and password required'
        ]);
        exit;
    }

    $stmt = $db->prepare('SELECT * FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];

        echo json_encode([
            'success' => true,
            'message' => 'Login successful'
        ]);

    } else {

        echo json_encode([
            'success' => false,
            'message' => 'Invalid username or password'
        ]);
    }

} catch (Exception $e) {

    echo json_encode([
        'success' => false,
        'message' => 'Login error: ' . $e->getMessage()
    ]);
}
?>