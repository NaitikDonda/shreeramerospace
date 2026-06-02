<?php
/*
Name: 			Contact Form
Written by: 	Okler Themes - (http://www.okler.net)
Theme Version:	13.0.0
*/

namespace PortoContactForm;

// Increase PHP upload limits for large files (25MB)
ini_set('upload_max_filesize', '25M');
ini_set('post_max_size', '30M');
ini_set('max_execution_time', '600');
ini_set('max_input_time', '600');
ini_set('memory_limit', '256M');

session_cache_limiter('nocache');
header('Expires: ' . gmdate('r', 0));

header('Content-type: application/json');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'php-mailer/src/PHPMailer.php';
require 'php-mailer/src/SMTP.php';
require 'php-mailer/src/Exception.php';

// Step 1 - Enter your email address below.
$email = 'dondanaitik@gmail.com';

// If the e-mail is not working, change the debug option to 2 | $debug = 2;
$debug = 0;

// If contact form don't has the subject input change the value of subject here
$subject = ( isset($_POST['subject']) ) ? $_POST['subject'] : 'SHREERAM CONTACT FORM';

$message = '';

foreach($_POST as $label => $value) {
	$label = ucwords($label);

	// Use the commented code below to change label texts. On this example will change "Email" to "Email Address"

	// if( $label == 'Email' ) {
	// 	$label = 'Email Address';
	// }

	// Checkboxes
	if( is_array($value) ) {
		// Store new value
		$value = implode(', ', $value);
	}

	$message .= $label.": " . nl2br(htmlspecialchars($value, ENT_QUOTES)) . "<br>";
}

// ============================================================================
// STEP 1: Validate Form Data
// ============================================================================
if (empty($_POST['name']) || empty($_POST['email']) || empty($_POST['message'])) {
	$arrResult = array('response'=>'error','errorMessage'=>'Please fill in all required fields (Name, Email, Message).');
	echo json_encode($arrResult);
	exit;
}

// ============================================================================
// STEP 2: Generate Unique ID (Always needed for database)
// ============================================================================
$uniqueId = uniqid();

// ============================================================================
// STEP 3: Validate and Upload File (Optional)
// ============================================================================
$maxFileSize = 50 * 1024 * 1024; // 50MB in bytes
$allowedExtensions = ['pdf', 'dwg', 'dxf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx'];
$uploadedFilePath = null;
$uploadedFileName = null;
$uploadedFileSize = 0;
$uploadedFileType = null;

error_log('=== FILE UPLOAD DEBUG START ===');
error_log('PHP upload_max_filesize: ' . ini_get('upload_max_filesize'));
error_log('PHP post_max_size: ' . ini_get('post_max_size'));
error_log('PHP max_execution_time: ' . ini_get('max_execution_time'));
error_log('PHP memory_limit: ' . ini_get('memory_limit'));
error_log('FILES array: ' . print_r($_FILES, true));

// Check for attachment field (contact page)
if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE) {
	$file = $_FILES['attachment'];
	
	error_log('Attachment detected: ' . $file['name']);
	error_log('Upload error code: ' . $file['error']);
	
	// Check for upload errors
	if ($file['error'] !== UPLOAD_ERR_OK) {
		$errorMessages = [
			UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive in php.ini',
			UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive specified in HTML form',
			UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
			UPLOAD_ERR_NO_FILE => 'No file was uploaded',
			UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
			UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
			UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
		];
		
		$errorMessage = isset($errorMessages[$file['error']]) 
			? $errorMessages[$file['error']] 
			: 'Unknown upload error (code: ' . $file['error'] . ')';
		
		error_log('Upload error: ' . $errorMessage);
		$arrResult = array('response'=>'error','errorMessage'=>'File upload failed: ' . $errorMessage);
		echo json_encode($arrResult);
		exit;
	}
	
	// Validate file size
	if ($file['size'] > $maxFileSize) {
		$fileSizeMB = round($file['size'] / (1024 * 1024), 2);
		$maxSizeMB = round($maxFileSize / (1024 * 1024), 2);
		error_log('File size too large: ' . $fileSizeMB . 'MB (max: ' . $maxSizeMB . 'MB)');
		$arrResult = array('response'=>'error','errorMessage'=>'File size (' . $fileSizeMB . 'MB) exceeds maximum allowed size (' . $maxSizeMB . 'MB).');
		echo json_encode($arrResult);
		exit;
	}
	
	// Validate file extension
	$fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
	if (!in_array($fileExtension, $allowedExtensions)) {
		error_log('Invalid file extension: ' . $fileExtension);
		$arrResult = array('response'=>'error','errorMessage'=>'Invalid file type. Allowed types: ' . implode(', ', $allowedExtensions));
		echo json_encode($arrResult);
		exit;
	}
	
	// Create uploads directory if it doesn't exist
	$uploadDir = __DIR__ . '/../uploads/';
	if (!is_dir($uploadDir)) {
		if (!mkdir($uploadDir, 0755, true)) {
			error_log('Failed to create uploads directory: ' . $uploadDir);
			$arrResult = array('response'=>'error','errorMessage'=>'Failed to create uploads directory. Please check folder permissions.');
			echo json_encode($arrResult);
			exit;
		}
		error_log('Created uploads directory: ' . $uploadDir);
	}
	
	// Check if directory is writable
	if (!is_writable($uploadDir)) {
		error_log('Uploads directory not writable: ' . $uploadDir);
		$arrResult = array('response'=>'error','errorMessage'=>'Uploads directory is not writable. Please check folder permissions.');
		echo json_encode($arrResult);
		exit;
	}
	
	// Generate unique filename
	$fileUniqueId = uniqid();
	$uploadedFileName = $fileUniqueId . '_' . $file['name'];
	$uploadedFilePath = $uploadDir . $uploadedFileName;
	
	error_log('Attempting to move file from: ' . $file['tmp_name'] . ' to: ' . $uploadedFilePath);
	
	// Move uploaded file
	if (!move_uploaded_file($file['tmp_name'], $uploadedFilePath)) {
		error_log('Failed to move uploaded file');
		$arrResult = array('response'=>'error','errorMessage'=>'Failed to save uploaded file. Please try again.');
		echo json_encode($arrResult);
		exit;
	}
	
	// Verify file was actually moved
	if (!file_exists($uploadedFilePath)) {
		error_log('File does not exist after move: ' . $uploadedFilePath);
		$arrResult = array('response'=>'error','errorMessage'=>'File was not saved correctly. Please try again.');
		echo json_encode($arrResult);
		exit;
	}
	
	$uploadedFileSize = $file['size'];
	$uploadedFileType = $file['type'];
	
	error_log('File uploaded successfully: ' . $uploadedFileName);
	error_log('File path: ' . $uploadedFilePath);
	error_log('File size: ' . $uploadedFileSize . ' bytes');
	error_log('=== FILE UPLOAD DEBUG END ===');
}

// Check for drawing field (homepage)
elseif (isset($_FILES['drawing']) && $_FILES['drawing']['error'] !== UPLOAD_ERR_NO_FILE) {
	$file = $_FILES['drawing'];
	
	error_log('Drawing detected: ' . $file['name']);
	error_log('Upload error code: ' . $file['error']);
	
	// Check for upload errors
	if ($file['error'] !== UPLOAD_ERR_OK) {
		$errorMessages = [
			UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive in php.ini',
			UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive specified in HTML form',
			UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
			UPLOAD_ERR_NO_FILE => 'No file was uploaded',
			UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
			UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
			UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
		];
		
		$errorMessage = isset($errorMessages[$file['error']]) 
			? $errorMessages[$file['error']] 
			: 'Unknown upload error (code: ' . $file['error'] . ')';
		
		error_log('Upload error: ' . $errorMessage);
		$arrResult = array('response'=>'error','errorMessage'=>'File upload failed: ' . $errorMessage);
		echo json_encode($arrResult);
		exit;
	}
	
	// Validate file size
	if ($file['size'] > $maxFileSize) {
		$fileSizeMB = round($file['size'] / (1024 * 1024), 2);
		$maxSizeMB = round($maxFileSize / (1024 * 1024), 2);
		error_log('File size too large: ' . $fileSizeMB . 'MB (max: ' . $maxSizeMB . 'MB)');
		$arrResult = array('response'=>'error','errorMessage'=>'File size (' . $fileSizeMB . 'MB) exceeds maximum allowed size (' . $maxSizeMB . 'MB).');
		echo json_encode($arrResult);
		exit;
	}
	
	// Validate file extension
	$fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
	if (!in_array($fileExtension, $allowedExtensions)) {
		error_log('Invalid file extension: ' . $fileExtension);
		$arrResult = array('response'=>'error','errorMessage'=>'Invalid file type. Allowed types: ' . implode(', ', $allowedExtensions));
		echo json_encode($arrResult);
		exit;
	}
	
	// Create uploads directory if it doesn't exist
	$uploadDir = __DIR__ . '/../uploads/';
	if (!is_dir($uploadDir)) {
		if (!mkdir($uploadDir, 0755, true)) {
			error_log('Failed to create uploads directory: ' . $uploadDir);
			$arrResult = array('response'=>'error','errorMessage'=>'Failed to create uploads directory. Please check folder permissions.');
			echo json_encode($arrResult);
			exit;
		}
		error_log('Created uploads directory: ' . $uploadDir);
	}
	
	// Check if directory is writable
	if (!is_writable($uploadDir)) {
		error_log('Uploads directory not writable: ' . $uploadDir);
		$arrResult = array('response'=>'error','errorMessage'=>'Uploads directory is not writable. Please check folder permissions.');
		echo json_encode($arrResult);
		exit;
	}
	
	// Generate unique filename
	$fileUniqueId = uniqid();
	$uploadedFileName = $fileUniqueId . '_' . $file['name'];
	$uploadedFilePath = $uploadDir . $uploadedFileName;
	
	error_log('Attempting to move file from: ' . $file['tmp_name'] . ' to: ' . $uploadedFilePath);
	
	// Move uploaded file
	if (!move_uploaded_file($file['tmp_name'], $uploadedFilePath)) {
		error_log('Failed to move uploaded file');
		$arrResult = array('response'=>'error','errorMessage'=>'Failed to save uploaded file. Please try again.');
		echo json_encode($arrResult);
		exit;
	}
	
	// Verify file was actually moved
	if (!file_exists($uploadedFilePath)) {
		error_log('File does not exist after move: ' . $uploadedFilePath);
		$arrResult = array('response'=>'error','errorMessage'=>'File was not saved correctly. Please try again.');
		echo json_encode($arrResult);
		exit;
	}
	
	$uploadedFileSize = $file['size'];
	$uploadedFileType = $file['type'];
	
	error_log('File uploaded successfully: ' . $uploadedFileName);
	error_log('File path: ' . $uploadedFilePath);
	error_log('File size: ' . $uploadedFileSize . ' bytes');
	error_log('=== FILE UPLOAD DEBUG END ===');
}

// ============================================================================
// STEP 3: Save to Database (Only after successful file upload)
// ============================================================================
try {
	$dbFile = __DIR__ . '/../database.sqlite';
	$db = new \PDO('sqlite:' . $dbFile);
	$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
	
	$name = $_POST['name'] ?? '';
	$emailUser = $_POST['email'] ?? '';
	$phone = $_POST['phone'] ?? '';
	$company = $_POST['company'] ?? '';
	$subjectPost = $_POST['subject'] ?? '';
	$messagePost = $_POST['message'] ?? '';
	$industry = $_POST['industry'] ?? '';
	
	$stmt = $db->prepare('
		INSERT INTO submissions (unique_id, name, email, phone, company, subject, message, industry)
		VALUES (?, ?, ?, ?, ?, ?, ?, ?)
	');
	$stmt->execute([$uniqueId, $name, $emailUser, $phone, $company, $subjectPost, $messagePost, $industry]);
	
	$submissionId = $db->lastInsertId();
	error_log('Submission saved to database with ID: ' . $submissionId);
	
	// Save file information to database if file was uploaded
	if ($uploadedFilePath && $uploadedFileName) {
		$stmt = $db->prepare('
			INSERT INTO files (submission_id, file_name, file_path, file_size, file_type)
			VALUES (?, ?, ?, ?, ?)
		');
		$stmt->execute([
			$submissionId,
			$file['name'],
			'uploads/' . $uploadedFileName,
			$uploadedFileSize,
			$uploadedFileType
		]);
		error_log('File information saved to database');
	}
	
} catch (\PDOException $e) {
	error_log('Database error: ' . $e->getMessage());
	// Clean up uploaded file if database save fails
	if ($uploadedFilePath && file_exists($uploadedFilePath)) {
		unlink($uploadedFilePath);
		error_log('Cleaned up uploaded file due to database error');
	}
	$arrResult = array('response'=>'error','errorMessage'=>'Database error: ' . $e->getMessage());
	echo json_encode($arrResult);
	exit;
}

// ============================================================================
// STEP 4: Send Email (Only after successful database save)
// ============================================================================
$mail = new PHPMailer(true);

try {

	$mail->SMTPDebug = $debug;                                 // Debug Mode

	// Step 2 (Optional) - If you don't receive the email, try to configure the parameters below:

	$mail->IsSMTP();                                         // Set mailer to use SMTP
	$mail->Host = 'smtp.gmail.com';				       // Specify main and backup server
	$mail->SMTPAuth = true;                                  // Enable SMTP authentication
	$mail->Username = 'dondanaitik@gmail.com';                    // SMTP username
	$mail->Password = 'iiwa ulge ncot dmna';                              // SMTP password (use App Password)
	$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;        // Enable SSL encryption
	$mail->Port = 465;   								       // TCP port to connect to (SSL)

	$mail->AddAddress($email);	 						       // Add another recipient

	//$mail->AddAddress('person2@domain.com', 'Person 2');     // Add a secondary recipient
	//$mail->AddCC('person3@domain.com', 'Person 3');          // Add a "Cc" address. 
	//$mail->AddBCC('person4@domain.com', 'Person 4');         // Add a "Bcc" address. 

	// From - Name
	$fromName = ( isset($_POST['name']) ) ? $_POST['name'] : 'Website User';
	$mail->SetFrom('dondanaitik@gmail.com', $fromName);

	// Reply To
	if( isset($_POST['email']) && !empty($_POST['email']) ) {
		$mail->AddReplyTo($_POST['email'], $fromName);
	}

	$mail->IsHTML(true);                                       // Set email format to HTML

	$mail->CharSet = 'UTF-8';

	$mail->Subject = $subject;
	$mail->Body    = $message;

	// Attach uploaded file to email
	if ($uploadedFilePath && file_exists($uploadedFilePath)) {
		$mail->AddAttachment($uploadedFilePath, $file['name']);
		error_log('File attached to email: ' . $uploadedFilePath);
	}

	$mail->Send();
	error_log('Email sent successfully');
	
	// Send confirmation email to user
	if (!empty($_POST['email'])) {
		$userEmail = $_POST['email'];
		$userName = $_POST['name'] ?? 'User';
		
		try {
			$userMail = new PHPMailer(true);
			$userMail->SMTPDebug = $debug;
			$userMail->IsSMTP();
			$userMail->Host = 'smtp.gmail.com';
			$userMail->SMTPAuth = true;
			$userMail->Username = 'dondanaitik@gmail.com';
			$userMail->Password = 'iiwa ulge ncot dmna';
			$userMail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
			$userMail->Port = 465;
			
			$userMail->AddAddress($userEmail);
			$userMail->SetFrom('dondanaitik@gmail.com', 'Shreeram Aerospace & Defence LLP');
			$userMail->AddReplyTo('dondanaitik@gmail.com', 'Shreeram Aerospace & Defence LLP');
			
			$userMail->IsHTML(true);
			$userMail->CharSet = 'UTF-8';
			$userMail->Subject = 'Thank You for Your Submission - Shreeram Aerospace & Defence LLP';
			
			$confirmationBody = "
			<!DOCTYPE html>
			<html>
			<head>
				<meta charset='UTF-8'>
				<meta name='viewport' content='width=device-width, initial-scale=1.0'>
				<title>Thank You - Shreeram Aerospace</title>
			</head>
			<body style='margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;'>
				<div style='max-width: 600px; margin: 0 auto; background-color: #ffffff;'>
					<!-- Header -->
					<div style='background: linear-gradient(135deg, #002248 0%, #003366 100%); padding: 30px; text-align: center;'>
						<h1 style='color: #ffffff; margin: 0; font-size: 28px;'>Shreeram Aerospace & Defence LLP</h1>
						<p style='color: #ffffff; margin: 10px 0 0 0; font-size: 16px; opacity: 0.9;'>Innovating Aerospace Solutions</p>
					</div>
					
					<!-- Content -->
					<div style='padding: 40px 30px;'>
						<h2 style='color: #002248; margin-top: 0;'>Thank You for Your Submission</h2>
						<p style='color: #333; line-height: 1.6; font-size: 16px;'>Dear {$userName},</p>
						<p style='color: #333; line-height: 1.6; font-size: 16px;'>We have successfully received your submission. Thank you for reaching out to Shreeram Aerospace & Defence LLP.</p>
						
						<div style='background-color: #f8f9fa; border-left: 4px solid #002248; padding: 20px; margin: 25px 0; border-radius: 4px;'>
							<h3 style='color: #002248; margin-top: 0; font-size: 18px;'>Submission Details</h3>
							<p style='margin: 8px 0; color: #555;'><strong>Reference ID:</strong> {$uniqueId}</p>
							<p style='margin: 8px 0; color: #555;'><strong>Subject:</strong> {$subject}</p>
							<p style='margin: 8px 0; color: #555;'><strong>Submitted On:</strong> " . date('Y-m-d H:i:s') . "</p>
						</div>
						
						<p style='color: #333; line-height: 1.6; font-size: 16px;'>Our team will review your submission and get back to you within 24-48 business hours. If you have any urgent queries, please feel free to contact us directly.</p>
						
						<p style='color: #333; line-height: 1.6; font-size: 16px;'>Best regards,</p>
						<p style='color: #333; line-height: 1.6; font-size: 16px;'><strong>Shreeram Aerospace & Defence LLP Team</strong></p>
					</div>
					
					<!-- Footer -->
					<div style='background-color: #002248; padding: 30px; text-align: center;'>
						<p style='color: #ffffff; margin: 0; font-size: 14px;'>&copy; " . date('Y') . " Shreeram Aerospace & Defence LLP. All rights reserved.</p>
						<p style='color: #ffffff; margin: 10px 0 0 0; font-size: 12px; opacity: 0.8;'>This is an automated email. Please do not reply.</p>
					</div>
				</div>
			</body>
			</html>
			";
			
			$userMail->Body = $confirmationBody;
			$userMail->Send();
			error_log('Confirmation email sent to user: ' . $userEmail);
		} catch (Exception $e) {
			error_log('Confirmation email failed: ' . $e->getMessage());
			// Don't fail the entire submission if confirmation email fails
		}
	}
	
	$arrResult = array ('response'=>'success');

} catch (Exception $e) {
	error_log('Email failed: ' . $e->getMessage());
	// Don't fail the entire submission if email fails - data is already saved
	$arrResult = array ('response'=>'success','warning'=>'Email could not be sent but your submission was saved.');
}

if ($debug == 0) {
	echo json_encode($arrResult);
}
?>