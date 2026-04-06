<?php
/**
 * DBell Creations - Contact Form Handler (Legacy endpoint)
 * Redirects to/uses contact.php logic
 */

$adminEmail = 'dbellcreations@gmail.com';

$errors = '';
if (empty($_POST['name']) || empty($_POST['email']) || empty($_POST['message'])) {
    $errors .= 'Error: name, email, and message are required.';
}

$name    = trim(strip_tags($_POST['name'] ?? ''));
$email   = trim(strip_tags($_POST['email'] ?? ''));
$message = trim(strip_tags($_POST['message'] ?? ''));
$phone   = trim(strip_tags($_POST['phone'] ?? ''));
$businessName = trim(strip_tags($_POST['business_name'] ?? $_POST['surname'] ?? ''));

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors .= ' Error: Invalid email address.';
}

if (empty($errors)) {
    $subject = "New Contact Form Submission: {$name}";
    $body    = "New message from dbellcreations.com contact form\n";
    $body   .= "=================================================\n";
    $body   .= "Name:          {$name}\n";
    $body   .= "Email:         {$email}\n";
    $body   .= "Phone:         " . ($phone ?: 'Not provided') . "\n";
    $body   .= "Business:      " . ($businessName ?: 'Not provided') . "\n";
    $body   .= "Message:\n{$message}\n";

    $headers  = "From: {$adminEmail}\r\n";
    $headers .= "Reply-To: {$email}\r\n";

    mail($adminEmail, $subject, $body, $headers);
    header('Location: contact.html?success=1');
    exit;
}
?>
<!DOCTYPE HTML>
<html>
<head><title>Contact Form Error</title></head>
<body>
<p><?php echo nl2br(htmlspecialchars($errors)); ?></p>
<p><a href="contact.html">← Go back</a></p>
</body>
</html>
