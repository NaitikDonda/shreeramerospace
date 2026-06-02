<?php
header('Content-type: application/json');

$id = $_POST['id'] ?? '';

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

// Find and remove the submission
$found = false;
foreach ($submissions as $key => $sub) {
	if ($sub['id'] === $id) {
		unset($submissions[$key]);
		$found = true;
		break;
	}
}

if ($found) {
	// Re-index array
	$submissions = array_values($submissions);
	file_put_contents($submissionsFile, json_encode($submissions, JSON_PRETTY_PRINT));
	echo json_encode(array('success' => true, 'message' => 'Submission deleted'));
} else {
	echo json_encode(array('success' => false, 'message' => 'Submission not found'));
}
