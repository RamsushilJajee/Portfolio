<?php
require_once __DIR__ . '/queue_email.php';

if (isset($_POST['submit'])) {

    // REPLACE THIS 2 LINES AS YOU DESIRE
    $email_to = "ramsushil.jajee@gmail.com";

    function problem($error)
    {
        echo "Oh looks like there is some problem with your form data: <br><br>";
        echo $error . "<br><br>";
        echo "Please fix those to proceed.<br><br>";
        die();
    }

    // validation expected data exists
    if (
        !isset($_POST['name']) ||
        !isset($_POST['email']) ||
        !isset($_POST['message']) ||
        !isset($_POST['subject'])
    ) {
        problem('Oh looks like there is some problem with your form data.');
    }

    $name = $_POST['name']; // required
    $email = $_POST['email']; // required
    $email_subject = $_POST['subject'];  // required
    $message = $_POST['message']; // required

    $error_message = "";
    $email_exp = '/^[A-Za-z0-9._%-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/';

    if (!preg_match($email_exp, $email)) {
        $error_message .= 'Email address does not seem valid.<br>';
    }

    $string_exp = "/^[A-Za-z .'-]+$/";

    if (!preg_match($string_exp, $name)) {
        $error_message .= 'Name does not seem valid.<br>';
    }

    if (strlen($message) < 2) {
        $error_message .= 'Message should not be less than 2 characters<br>';
    }

    if (strlen($error_message) > 0) {
        problem($error_message);
    }

    $email_message = "Form details following:\n\n";

    function clean_string($string)
    {
        $bad = array("content-type", "bcc:", "to:", "cc:", "href");
        return str_replace($bad, "", $string);
    }

    $email_message .= "Name: " . clean_string($name) . "\n";
    $email_message .= "Email: " . clean_string($email) . "\n";
    $email_message .= "Message: " . clean_string($message) . "\n";

    // create email headers
    $headers = 'From: ' . $email . "\r\n" .
        'Reply-To: ' . $email . "\r\n" .
        'X-Mailer: PHP/' . phpversion();
    $result = queue_email($email_to, $email_subject, $email_message, $headers);

    //error checking
    if($result) {
        $success = "The message was sent successfully!";
    } else {
        $failed = "Error: Message was not sent, Try again Later";
    }
}
?>