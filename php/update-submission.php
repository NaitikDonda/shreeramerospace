<?php
header('Content-type: application/json');

$id = $_POST['id'] ?? '';
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$subject = $_POST['subject'] ?? '';
$message = $_POST['message'] ?? '';

if (empty($id)) {
	echo json_encode(array('success' => false, 'message' => 'Invalid ID'));
	exit;
}

$submissionsFile = __DIR__ . '/../submissions.json';

if (file_exists($submissionsFile)) {
	$submissions = json_decode(file_get_contents($submissionsFile), true);
	if (!$submissions) {
		$submissions = array();
	}
} else {
	$submissions = array();
}

// Find and update the submission
$found = false;
foreach ($submissions as $key => $sub) {
	if ($sub['id'] === $id) {
		$submissions[$key]['data']['name'] = $name;
		$submissions[$key]['data']['email'] = $email;
		$submissions[$key]['data']['phone'] = $phone;
		$submissions[$key]['data']['subject'] = $subject;
		$submissions[$key]['data']['message'] = $message;
		$found = true;
		break;
	}
}

if ($found) {
	file_put_contents($submissionsFile, json_encode($submissions, JSON_PRETTY_PRINT));
	echo json_encode(array('success' => true, 'message' => 'Submission updated'));
} else {
	echo json_encode(array('success' => false, 'message' => 'Submission not found'));
}
