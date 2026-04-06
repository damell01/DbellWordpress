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

// ── Input Validation ──────────────────────────────────────────────────────────
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

// ── Save to Database ──────────────────────────────────────────────────────────
$leadId = null;
try {
    $dbConfig = [];
    $dbConfigFile = __DIR__ . '/WebsiteScan/config/database.php';
    if (file_exists($dbConfigFile)) {
        $dbConfig = require $dbConfigFile;
    }

    if (!empty($dbConfig['database'])) {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $dbConfig['host'] ?? '127.0.0.1',
            $dbConfig['port'] ?? 3306,
            $dbConfig['database']
        );
        $pdo = new PDO($dsn, $dbConfig['username'] ?? 'root', $dbConfig['password'] ?? '', [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        $now = date('Y-m-d H:i:s');

        // Check if lead already exists by email
        $existingLead = null;
        if (!empty($email)) {
            $stmt = $pdo->prepare("SELECT id FROM leads WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $existingLead = $stmt->fetch();
        }

        if ($existingLead) {
            $leadId = (int)$existingLead['id'];
            // Update existing lead
            $pdo->prepare("UPDATE leads SET contact_name = ?, phone = ?, business_name = ?, website_url = ?, service_interest = ?, source_page = ?, notes = ? WHERE id = ?")
                ->execute([$name, $phone ?: null, $businessName ?: null, $website ?: null, $serviceInterest ?: null, $sourcePage, $message, $leadId]);
        } else {
            // Insert new lead
            $stmt = $pdo->prepare("INSERT INTO leads (contact_name, email, phone, business_name, website_url, notes, source, service_interest, source_page, status, follow_up_stage, next_follow_up_at, created_at) VALUES (?, ?, ?, ?, ?, ?, 'contact_form', ?, ?, 'new', 0, DATE_ADD(NOW(), INTERVAL 1 DAY), ?)");
            $stmt->execute([$name, $email, $phone ?: null, $businessName ?: null, $website ?: null, $message, $serviceInterest ?: null, $sourcePage, $now]);
            $leadId = (int)$pdo->lastInsertId();
        }

        // Also save contact request
        $pdo->prepare("INSERT INTO contact_requests (lead_id, name, email, phone, company, message, service_type, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)")
            ->execute([$leadId ?: null, $name, $email, $phone ?: null, $businessName ?: null, $message, $serviceInterest ?: null, $now]);
    }
} catch (\Throwable $e) {
    // DB save failed — log but don't block the form submission
    error_log('DBell Contact Form DB Error: ' . $e->getMessage());
}

// ── Send Admin Notification Email ─────────────────────────────────────────────
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

// ── Send Confirmation Email to Lead ──────────────────────────────────────────
$confirmSubject = "Thanks for reaching out, {$name}! — DBell Creations";
$confirmBody  = "Hi {$name},\n\n";
$confirmBody .= "Thanks for getting in touch with DBell Creations! I received your message and I'll be back with you within 24 hours.\n\n";
$confirmBody .= "Here's a quick recap of what you submitted:\n";
$confirmBody .= "- Service interest: " . ($serviceInterest ?: 'General inquiry') . "\n";
$confirmBody .= "- Your message: \"" . substr($message, 0, 200) . (strlen($message) > 200 ? '...' : '') . "\"\n\n";
$confirmBody .= "While you wait, here are a few things you can explore:\n";
$confirmBody .= "👉 View our pricing: https://www.dbellcreations.com/pricing.html\n";
$confirmBody .= "👉 Run a free website audit: https://www.dbellcreations.com/scan.html\n";
$confirmBody .= "👉 See our portfolio: https://www.dbellcreations.com/project.html\n\n";
$confirmBody .= "Talk soon,\nDBell Creations\n";
$confirmBody .= "📞 251-406-2292\n";
$confirmBody .= "📧 dbellcreations@gmail.com\n";
$confirmBody .= "🌐 https://www.dbellcreations.com\n";

$confirmHeaders  = "From: DBell Creations <{$adminEmail}>\r\n";
$confirmHeaders .= "Reply-To: {$adminEmail}\r\n";
$confirmHeaders .= "X-Mailer: PHP/" . phpversion();

@mail($email, $confirmSubject, $confirmBody, $confirmHeaders);

// ── Success Response ──────────────────────────────────────────────────────────
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
            echo '<p><a href="contact.html">← Go back</a></p>';
        }
    }
    exit;
}
