<?php

// Replace this with your own email address
$siteOwnersEmail = 'dbellcreations@gmail.com';

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


if($_POST) {

    $name = trim(stripslashes($_POST['contactName']));
    $email = trim(stripslashes($_POST['contactEmail']));
    $subject = trim(stripslashes($_POST['contactSubject']));
    $contact_message = trim(stripslashes($_POST['contactMessage']));
    $redirectSuccess = safeRedirectPath($_POST['redirectSuccess'] ?? null);
    $redirectError = safeRedirectPath($_POST['redirectError'] ?? null);

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


    if (!$error) {

        ini_set("sendmail_from", $siteOwnersEmail); // for windows server
        $mail = mail($siteOwnersEmail, $subject, $message, $headers);

        if ($mail) {
            if ($redirectSuccess) {
                header("Location: " . $redirectSuccess);
                exit;
            }
            echo "OK";
        }
        else {
            if ($redirectError) {
                header("Location: " . $redirectError);
                exit;
            }
            echo "Something went wrong. Please try again.";
        }
        
    } # end if - no validation error

    else {

        $response = (isset($error['name'])) ? $error['name'] . "<br /> \n" : null;
        $response .= (isset($error['email'])) ? $error['email'] . "<br /> \n" : null;
        $response .= (isset($error['message'])) ? $error['message'] . "<br />" : null;

        if ($redirectError) {
            header("Location: " . $redirectError);
            exit;
        }
        
        echo $response;

    } # end if - there was a validation error

}

?>