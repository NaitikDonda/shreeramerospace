<?php

session_cache_limiter('nocache');
header('Expires: ' . gmdate('r', 0));
header('Content-Type: application/json');

// Admin email
$email = 'dondanaitik@gmail.com';

// Database connection
try {
    $dbFile = __DIR__ . '/../database.sqlite';

    if (!$dbFile) {
        throw new Exception('Database file not found');
    }

    $db = new PDO('sqlite:' . $dbFile);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (Exception $e) {
    echo json_encode([
        'response' => 'error',
        'errorMessage' => $e->getMessage()
    ]);
    exit;
}

// Get form data
$name = $_POST['name'] ?? '';
$emailUser = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$company = $_POST['company'] ?? '';
$subject = $_POST['subject'] ?? 'New Contact Form Submission';
$messageText = $_POST['message'] ?? '';
$industry = $_POST['industry'] ?? '';

$uniqueId = 'SR-' . time();

// Save to database
// Save to database
try {

    $stmt = $db->prepare("
        INSERT INTO submissions (
            unique_id,
            name,
            email,
            phone,
            company,
            subject,
            message,
            industry,
            timestamp
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $uniqueId,
        $name,
        $emailUser,
        $phone,
        $company,
        $subject,
        $messageText,
        $industry,
        date('Y-m-d H:i:s')
    ]);

    // Get inserted submission ID
    $submissionId = $db->lastInsertId();
	echo $submissionId;
exit;

    // Handle uploaded files
    if (!empty($_FILES['file']['name'][0])) {

        $uploadDir = __DIR__ . '/uploads/';

        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        foreach ($_FILES['file']['tmp_name'] as $key => $tmpName) {

            $fileName = time() . '_' . basename($_FILES['file']['name'][$key]);

            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($tmpName, $targetPath)) {

                $stmt = $db->prepare("
                    INSERT INTO files (
                        submission_id,
                        file_name,
                        file_path,
                        file_size,
                        file_type
                    )
                    VALUES (?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $submissionId,
                    $_FILES['file']['name'][$key],
                    'php/uploads/' . $fileName,
                    $_FILES['file']['size'][$key],
                    $_FILES['file']['type'][$key]
                ]);
            }
        }
    }

} catch (Exception $e) {
    echo json_encode([
        'response' => 'error',
        'errorMessage' => 'Database save failed: ' . $e->getMessage()
    ]);
    exit;
}

// Email body
$emailMessage = "
<h2>New Contact Form Submission</h2>
<p><strong>Name:</strong> {$name}</p>
<p><strong>Email:</strong> {$emailUser}</p>
<p><strong>Phone:</strong> {$phone}</p>
<p><strong>Company:</strong> {$company}</p>
<p><strong>Subject:</strong> {$subject}</p>
<p><strong>Industry:</strong> {$industry}</p>
<p><strong>Message:</strong><br>{$messageText}</p>
<p><strong>Submitted On:</strong> " . date('Y-m-d H:i:s') . "</p>
";

// Email headers
$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-type:text/html;charset=UTF-8\r\n";
$headers .= "From: dondanaitik@gmail.com\r\n";

if (!empty($emailUser)) {
    $headers .= "Reply-To: {$emailUser}\r\n";
}

// Send email to admin
mail($email, $subject, $emailMessage, $headers);

// Send confirmation email to user
if (!empty($emailUser)) {
    $confirmationSubject = 'Thank You for Your Submission - Shreeram Aerospace & Defence LLP';
    
    $confirmationMessage = "
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
                <p style='color: #333; line-height: 1.6; font-size: 16px;'>Dear {$name},</p>
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
    
    $confirmationHeaders = "MIME-Version: 1.0\r\n";
    $confirmationHeaders .= "Content-type:text/html;charset=UTF-8\r\n";
    $confirmationHeaders .= "From: dondanaitik@gmail.com\r\n";
    
    mail($emailUser, $confirmationSubject, $confirmationMessage, $confirmationHeaders);
}

// Success response
echo json_encode([
    'response' => 'success'
]);


?>

