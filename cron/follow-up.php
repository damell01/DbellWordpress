<?php
/**
 * DBell Creations - Lead Follow-Up Automation
 * Run daily via cron: 0 9 * * * php /path/to/cron/follow-up.php
 */

define('DBELL_ROOT', dirname(__DIR__));
$adminEmail = 'dbellcreations@gmail.com';

// Load DB config
$dbConfig = [];
$dbConfigFile = DBELL_ROOT . '/WebsiteScan/config/database.php';
if (file_exists($dbConfigFile)) {
    $dbConfig = require $dbConfigFile;
}

if (empty($dbConfig['database'])) {
    echo "No database config found. Exiting.\n";
    exit(1);
}

try {
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
} catch (\PDOException $e) {
    echo "DB connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Find leads that need follow-up
$stmt = $pdo->prepare("
    SELECT * FROM leads
    WHERE status IN ('new', 'reviewed', 'contacted')
      AND follow_up_stage < 4
      AND email IS NOT NULL
      AND email != ''
      AND (next_follow_up_at IS NULL OR next_follow_up_at <= NOW())
    ORDER BY created_at ASC
    LIMIT 50
");
$stmt->execute();
$leads = $stmt->fetchAll();

$sent = 0;
$failed = 0;

foreach ($leads as $lead) {
    $stage = (int)($lead['follow_up_stage'] ?? 0);
    $nextStage = $stage + 1;
    $email = $lead['email'];
    $name  = $lead['contact_name'] ?: 'there';
    $firstName = explode(' ', $name)[0];

    // Check for duplicate send
    $dupCheck = $pdo->prepare("SELECT id FROM email_log WHERE lead_id = ? AND email_stage = ? LIMIT 1");
    $dupCheck->execute([$lead['id'], $nextStage]);
    if ($dupCheck->fetch()) {
        // Already sent this stage â€” advance stage silently
        $pdo->prepare("UPDATE leads SET follow_up_stage = ?, last_contacted_at = NOW(), next_follow_up_at = ? WHERE id = ?")
            ->execute([$nextStage, getNextFollowUpDate($nextStage), $lead['id']]);
        continue;
    }

    // Build email content for this stage
    [$subject, $body] = buildFollowUpEmail($nextStage, $firstName, $lead);

    // Send email
    $headers  = "From: DBell Creations <{$adminEmail}>\r\n";
    $headers .= "Reply-To: {$adminEmail}\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    $mailSent = @mail($email, $subject, $body, $headers);
    $status   = $mailSent ? 'sent' : 'failed';

    // Log
    $pdo->prepare("INSERT INTO email_log (lead_id, email_stage, recipient_email, subject, status, sent_at) VALUES (?, ?, ?, ?, ?, NOW())")
        ->execute([$lead['id'], $nextStage, $email, $subject, $status]);

    // Update lead
    $nextFollowUpAt = getNextFollowUpDate($nextStage);
    $pdo->prepare("UPDATE leads SET follow_up_stage = ?, last_contacted_at = NOW(), next_follow_up_at = ?, status = CASE WHEN status = 'new' THEN 'contacted' ELSE status END WHERE id = ?")
        ->execute([$nextStage, $nextFollowUpAt, $lead['id']]);

    if ($mailSent) {
        $sent++;
        echo "Sent stage {$nextStage} to {$email}\n";
    } else {
        $failed++;
        echo "FAILED stage {$nextStage} to {$email}\n";
    }
}

echo "Done. Sent: {$sent}, Failed: {$failed}\n";

function getNextFollowUpDate(int $stage): ?string {
    // Intervals (days) between stages: stage 1â†’2 waits 1 day, 2â†’3 waits 3 days, 3â†’4 waits 5 days
    // Cumulative from first contact: stage 1 = day 0, stage 2 = day 1, stage 3 = day 4, stage 4 = day 9
    $daysMap = [1 => 1, 2 => 3, 3 => 5, 4 => null];
    $days = $daysMap[$stage] ?? null;
    if ($days === null) return null;
    return date('Y-m-d H:i:s', strtotime("+{$days} days"));
}

function buildFollowUpEmail(int $stage, string $firstName, array $lead): array {
    $service = $lead['service_interest'] ?? 'website';
    $businessName = $lead['business_name'] ? " ({$lead['business_name']})" : '';

    switch ($stage) {
        case 1:
            $subject = "Thanks for reaching out, {$firstName}! Here's what's next ðŸ™Œ";
            $body  = "Hey {$firstName},\n\n";
            $body .= "Thanks for reaching out to DBell Creations{$businessName}! I wanted to personally follow up and make sure you got everything you need.\n\n";
            $body .= "We help small businesses like yours with:\n";
            $body .= "âœ… Affordable websites (starting at just \$350)\n";
            $body .= "âœ… Custom software & business automation\n";
            $body .= "âœ… SEO that actually gets you more traffic\n\n";
            $body .= "One quick thing â€” have you run a free website audit yet? It's completely free and will show you exactly what's hurting your site's performance and rankings:\n";
            $body .= "ðŸ‘‰ https://www.dbellcreations.com/WebsiteScan/public/audit\n\n";
            $body .= "I'll be back in touch shortly. In the meantime, feel free to reply to this email with any questions!\n\n";
            $body .= "Talk soon,\nDBell Creations\nðŸ“ž 251-406-2292\nðŸŒ https://www.dbellcreations.com";
            break;

        case 2:
            $subject = "Quick question for you, {$firstName} â€” is your website holding you back?";
            $body  = "Hey {$firstName},\n\n";
            $body .= "I wanted to share something that might be helpful.\n\n";
            $body .= "Most small business websites we audit have at least 3-5 issues that are quietly killing their results â€” things like:\n\n";
            $body .= "âŒ Slow load times (Google penalizes sites that take more than 3 seconds to load)\n";
            $body .= "âŒ No clear call-to-action (visitors don't know what to do next)\n";
            $body .= "âŒ Poor mobile experience (60%+ of traffic is on phones)\n";
            $body .= "âŒ Missing SEO basics (your site isn't being found for the right keywords)\n";
            $body .= "âŒ No lead capture (you're losing potential customers daily)\n\n";
            $body .= "Any of those sound familiar?\n\n";
            $body .= "If so, our free website audit will catch all of these and give you a prioritized action plan:\n";
            $body .= "ðŸ‘‰ Run your free audit: https://www.dbellcreations.com/WebsiteScan/public/audit\n\n";
            $body .= "Just reply to this email if you have questions â€” happy to help!\n\n";
            $body .= "â€” DBell Creations\nðŸ“ž 251-406-2292";
            break;

        case 3:
            $subject = "Still thinking it over? Here's our pricing ðŸ‘‡";
            $body  = "Hey {$firstName},\n\n";
            $body .= "Just wanted to circle back quickly â€” wanted to make sure you saw our website packages.\n\n";
            $body .= "Our most popular options:\n\n";
            $body .= "â­ Starter Website â€” \$350 (SALE)\n";
            $body .= "   Perfect for getting a professional web presence fast.\n\n";
            $body .= "â­ Business Website â€” \$750 (SALE)\n";
            $body .= "   Full site you can manage yourself, with lead forms and SEO built in.\n\n";
            $body .= "â­ Custom Build â€” \$1,000â€“\$1,500+\n";
            $body .= "   For businesses that need advanced features or custom designs.\n\n";
            $body .= "ðŸ‘‰ See all pricing: https://www.dbellcreations.com/pricing.html\n\n";
            $body .= "If you're not sure which option is right for you, just reply here and I'll help you figure it out â€” no pressure, no obligation.\n\n";
            $body .= "â€” DBell Creations\nðŸ“ž 251-406-2292\nðŸŒ https://www.dbellcreations.com";
            break;

        case 4:
            $subject = "Last check-in from DBell Creations ðŸ‘‹";
            $body  = "Hey {$firstName},\n\n";
            $body .= "I know you're busy â€” just wanted to do one final check-in.\n\n";
            $body .= "If you're still looking to improve your online presence â€” whether it's a new website, better SEO, or a custom software solution â€” I'd love to help.\n\n";
            $body .= "Even if the timing isn't right now, here are some resources to save for later:\n";
            $body .= "ðŸ“‹ View our pricing: https://www.dbellcreations.com/pricing.html\n";
            $body .= "ðŸ” Free website audit: https://www.dbellcreations.com/WebsiteScan/public/audit\n";
            $body .= "ðŸ“ž Call us: 251-406-2292\n\n";
            $body .= "No need to reply if now isn't the right time â€” we'll be here when you're ready.\n\n";
            $body .= "Wishing you the best,\nDBell Creations\nðŸŒ https://www.dbellcreations.com";
            break;

        default:
            $subject = "Following up from DBell Creations";
            $body = "Hey {$firstName},\n\nJust following up from DBell Creations. Reply anytime if we can help!\n\nâ€” DBell Creations\nðŸ“ž 251-406-2292";
    }

    return [$subject, $body];
}

