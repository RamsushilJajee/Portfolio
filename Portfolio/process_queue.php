<?php

$queueDir = __DIR__ . '/queue';
$files = glob($queueDir . '/*.json');

if ($files === false) {
    echo "Error reading queue directory.\n";
    exit(1);
}

if (empty($files)) {
    echo "Queue is empty.\n";
    exit(0);
}

foreach ($files as $file) {
    // Attempt to rename the file to lock it for processing
    $processingFile = $file . '.processing';
    if (!rename($file, $processingFile)) {
        // Could not rename, likely another worker picked it up
        continue;
    }

    echo "Processing $file...\n";

    $content = file_get_contents($processingFile);
    $data = json_decode($content, true);

    if (!$data) {
        echo "Error decoding JSON in $file. Deleting corrupted file.\n";
        unlink($processingFile);
        continue;
    }

    $to = $data['to'];
    $subject = $data['subject'];
    $message = $data['message'];
    $headers = $data['headers'];
    $retryCount = isset($data['retry_count']) ? (int)$data['retry_count'] : 0;

    $result = mail($to, $subject, $message, $headers);

    if ($result) {
        echo "Email sent successfully. Deleting $file.\n";
        unlink($processingFile);
    } else {
        echo "Failed to send email for $file.\n";

        $retryCount++;
        if ($retryCount >= 3) {
            echo "Max retries reached. Deleting $file.\n";
            unlink($processingFile);
        } else {
            echo "Retrying later (Attempt $retryCount/3).\n";
            $data['retry_count'] = $retryCount;
            // Write back to the original filename to release lock and update retry count
            file_put_contents($file, json_encode($data), LOCK_EX);
            unlink($processingFile);
        }
    }
}

echo "Queue processing complete.\n";
?>
