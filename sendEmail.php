<?php

// Replace this with your own email address
$siteOwnersEmail = 'dbellcreations@gmail.com';

function clientIpAddress() {
    $keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
    foreach ($keys as $key) {
        if (!empty($_SERVER[$key])) {
            $value = trim(explode(',', $_SERVER[$key])[0]);
            if (filter_var($value, FILTER_VALIDATE_IP)) {
                return $value;
            }
        }
    }
    return '0.0.0.0';
}

function rateLimitFilePath($ip) {
    $safeIp = preg_replace('/[^0-9a-fA-F:\.]/', '_', $ip);
    return rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'dbell_contact_' . md5($safeIp) . '.txt';
}

function isRateLimited($ip, $windowSeconds) {
    $file = rateLimitFilePath($ip);
    if (!file_exists($file)) {
        return false;
    }
    $last = (int) @file_get_contents($file);
    return ($last > 0 && (time() - $last) < $windowSeconds);
}

function updateRateLimit($ip) {
    $file = rateLimitFilePath($ip);
    @file_put_contents($file, (string) time(), LOCK_EX);
}

function safeRedirectPath($path) {
    if (!$path) {
        return null;
    }

    $path = trim($path);

    // Block full URLs and protocol-relative redirects.
    if (preg_match('/^https?:\/\//i', $path) || strpos($path, '//') === 0) {
        return null;
    }

    // Keep redirects local to HTML pages in this site.
    if (!preg_match('/^[a-zA-Z0-9_\-\.\/\?=&%]+$/', $path)) {
        return null;
    }

    return $path;
}

function appendQueryValue($path, $key, $value) {
    if (!$path) {
        return null;
    }

    $separator = (strpos($path, '?') !== false) ? '&' : '?';
    return $path . $separator . rawurlencode($key) . '=' . rawurlencode($value);
}


if($_POST) {

    $error = array();

    $name = trim(stripslashes($_POST['contactName']));
    $email = trim(stripslashes($_POST['contactEmail']));
    $subject = trim(stripslashes($_POST['contactSubject']));
    $contact_message = trim(stripslashes($_POST['contactMessage']));
    $honeypot = trim($_POST['websiteUrl'] ?? '');
    $formStartedAt = (int) ($_POST['formStartedAt'] ?? 0);
    $redirectSuccess = safeRedirectPath($_POST['redirectSuccess'] ?? null);
    $redirectError = safeRedirectPath($_POST['redirectError'] ?? null);
    $ip = clientIpAddress();

    // Honeypot should stay blank for real users.
    if ($honeypot !== '') {
        $error['spam'] = "Spam detected.";
    }

    // Humans typically take at least 3 seconds to submit.
    if ($formStartedAt <= 0 || ((int) floor(microtime(true) * 1000) - $formStartedAt) < 3000) {
        $error['timing'] = "Submission too fast.";
    }

    // Basic per-IP rate limit: 1 submission every 60 seconds.
    if (isRateLimited($ip, 60)) {
        $error['rate'] = "Please wait a minute before submitting again.";
    }

    // Check Name
    if (strlen($name) < 2) {
        $error['name'] = "Please enter your name.";
    }
    // Check Email
    if (!preg_match('/^[a-z0-9&\'\.\-_\+]+@[a-z0-9\-]+\.([a-z0-9\-]+\.)*+[a-z]{2}/is', $email)) {
        $error['email'] = "Please enter a valid email address.";
    }
    // Check Message
    if (strlen($contact_message) < 15) {
        $error['message'] = "Please enter your message. It should have at least 15 characters.";
    }
    // Subject
    if ($subject == '') { $subject = "Contact Form Submission"; }


    // Set Message
    $message .= "Email from: " . $name . "<br />";
    $message .= "Email address: " . $email . "<br />";
    $message .= "Message: <br />";
    $message .= $contact_message;
    $message .= "<br /> ----- <br /> This email was sent from your site's contact form. <br />";

    // Set From: header
    $from =  $name . " <" . $email . ">";

    // Email Headers
    $headers = "From: " . $from . "\r\n";
    $headers .= "Reply-To: ". $email . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";


    if (empty($error)) {

        ini_set("sendmail_from", $siteOwnersEmail); // for windows server
        $mail = mail($siteOwnersEmail, $subject, $message, $headers);

        if ($mail) {
            updateRateLimit($ip);
            if ($redirectSuccess) {
                header("Location: " . appendQueryValue($redirectSuccess, 'mail', 'sent'));
                exit;
            }
            echo "OK";
        }
        else {
            if ($redirectError) {
                header("Location: " . appendQueryValue($redirectError, 'mail', 'failed'));
                exit;
            }
            echo "Something went wrong. Please try again.";
        }
        
    } # end if - no validation error

    else {

        $response = (isset($error['name'])) ? $error['name'] . "<br /> \n" : null;
        $response .= (isset($error['email'])) ? $error['email'] . "<br /> \n" : null;
        $response .= (isset($error['message'])) ? $error['message'] . "<br />" : null;
        $response .= (isset($error['rate'])) ? $error['rate'] . "<br />" : null;

        if ($redirectError) {
            header("Location: " . appendQueryValue($redirectError, 'mail', 'invalid'));
            exit;
        }
        
        echo $response;

    } # end if - there was a validation error

}

?>