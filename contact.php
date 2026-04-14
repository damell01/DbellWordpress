<?php
/**
 * DBell Creations - Contact Form Handler
 * Saves leads to DB and sends email notifications
 */

// CORS + JSON header for AJAX
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
}

$adminEmail = 'dbellcreations@gmail.com';
$errors = [];
$responseArray = [];

// â”€â”€ Input Validation â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$name           = trim(strip_tags($_POST['name'] ?? ''));
$email          = trim(strip_tags($_POST['email'] ?? ''));
$phone          = trim(strip_tags($_POST['phone'] ?? ''));
$businessName   = trim(strip_tags($_POST['business_name'] ?? ''));
$website        = trim(strip_tags($_POST['website'] ?? ''));
$message        = trim(strip_tags($_POST['message'] ?? ''));
$serviceInterest= trim(strip_tags($_POST['service_interest'] ?? ''));
$sourcePage     = trim(strip_tags($_POST['source_page'] ?? 'contact'));

if (empty($name))  $errors[] = 'Name is required.';
if (empty($email)) $errors[] = 'Email is required.';
if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
}
if (empty($message)) $errors[] = 'Message is required.';

if (!empty($errors)) {
    $responseArray = ['type' => 'danger', 'message' => implode(' ', $errors)];
    respond($responseArray);
    exit;
}

$leadId = null;

// â”€â”€ Send Admin Notification Email â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$adminSubject = "New Lead: {$name}" . ($businessName ? " ({$businessName})" : '');
$adminBody  = "You have a new contact form submission!\n";
$adminBody .= "=====================================\n";
$adminBody .= "Name:             {$name}\n";
$adminBody .= "Email:            {$email}\n";
$adminBody .= "Phone:            " . ($phone ?: 'Not provided') . "\n";
$adminBody .= "Business Name:    " . ($businessName ?: 'Not provided') . "\n";
$adminBody .= "Website:          " . ($website ?: 'Not provided') . "\n";
$adminBody .= "Service Interest: " . ($serviceInterest ?: 'Not specified') . "\n";
$adminBody .= "Source Page:      {$sourcePage}\n";
$adminBody .= "Message:\n{$message}\n";
$adminBody .= "=====================================\n";
$adminBody .= "Lead ID: " . ($leadId ?: 'Not saved to DB') . "\n";
$adminBody .= "Time: " . date('Y-m-d H:i:s') . "\n";

$adminHeaders  = "From: DBell Creations <{$adminEmail}>\r\n";
$adminHeaders .= "Reply-To: {$name} <{$email}>\r\n";
$adminHeaders .= "X-Mailer: PHP/" . phpversion();

@mail($adminEmail, $adminSubject, $adminBody, $adminHeaders);

// â”€â”€ Send Confirmation Email to Lead â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$confirmSubject = "Thanks for reaching out, {$name}! - DBell Creations";
$confirmBody  = "Hi {$name},\n\n";
$confirmBody .= "Thanks for getting in touch with DBell Creations! I received your message and I'll be back with you within 24 hours.\n\n";
$confirmBody .= "Here's a quick recap of what you submitted:\n";
$confirmBody .= "- Service interest: " . ($serviceInterest ?: 'General inquiry') . "\n";
$confirmBody .= "- Your message: \"" . substr($message, 0, 200) . (strlen($message) > 200 ? '...' : '') . "\"\n\n";
$confirmBody .= "While you wait, here are a few things you can explore:\n";
$confirmBody .= "ðŸ‘‰ View our pricing: https://www.dbellcreations.com/pricing.html\n";
$confirmBody .= "ðŸ‘‰ Run a free consultation: https://www.dbellcreations.com/contact.html\n";
$confirmBody .= "ðŸ‘‰ See our portfolio: https://www.dbellcreations.com/project.html\n\n";
$confirmBody .= "Talk soon,\nDBell Creations\n";
$confirmBody .= "ðŸ“ž 251-406-2292\n";
$confirmBody .= "ðŸ“§ dbellcreations@gmail.com\n";
$confirmBody .= "ðŸŒ https://www.dbellcreations.com\n";

$confirmHeaders  = "From: DBell Creations <{$adminEmail}>\r\n";
$confirmHeaders .= "Reply-To: {$adminEmail}\r\n";
$confirmHeaders .= "X-Mailer: PHP/" . phpversion();

@mail($email, $confirmSubject, $confirmBody, $confirmHeaders);

// â”€â”€ Success Response â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$responseArray = [
    'type'    => 'success',
    'message' => "Thank you, {$name}! Your message has been received. We'll be in touch within 24 hours.",
];
respond($responseArray);

function respond(array $data): void {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        echo json_encode($data);
    } else {
        // Non-AJAX fallback
        if ($data['type'] === 'success') {
            header('Location: contact.html?success=1');
        } else {
            echo '<p style="color:red;">' . htmlspecialchars($data['message']) . '</p>';
            echo '<p><a href="contact.html">â† Go back</a></p>';
        }
    }
    exit;
}



