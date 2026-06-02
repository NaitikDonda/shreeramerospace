<?php
header('Content-type: application/json');

$submissionsFile = __DIR__ . '/../submissions.json';

if (file_exists($submissionsFile)) {
	$submissions = json_decode(file_get_contents($submissionsFile), true);
	if (!$submissions) {
		$submissions = array();
	}
} else {
	$submissions = array();
}

echo json_encode($submissions);
