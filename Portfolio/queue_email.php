<?php

function queue_email($to, $subject, $message, $headers)
{
    $data = [
        'to' => $to,
        'subject' => $subject,
        'message' => $message,
        'headers' => $headers,
        'timestamp' => time()
    ];

    $filename = __DIR__ . '/queue/' . uniqid('email_', true) . '.json';

    // Ensure the queue directory exists
    if (!is_dir(__DIR__ . '/queue')) {
        mkdir(__DIR__ . '/queue', 0755, true);
    }

    $result = file_put_contents($filename, json_encode($data), LOCK_EX);

    return $result !== false;
}
?>
