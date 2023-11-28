<?php

require_once dirname(__DIR__) . '/db/db_connect.php';
require_once dirname(__DIR__) . '/utils/helpers.php';

const FROM_EMAIL = 'something@example.com';

/** @var PDO $pdo */
$pdo = db();

$maxChecks = $argv[1] ?? 10000;
$processCount = $argv[2] ?? 20;

run_parallel_processes($processCount, function ($index) use ($maxChecks, $processCount) {
    // New DB connection for this child process
    $pdo = db();

    // Calculate the number of emails this child process should send
    $sendsPerProcess = (int) ceil($maxChecks / $processCount);
    $insertLogStmt = $pdo->prepare("INSERT INTO email_log (user_id) VALUES (?)");

    // Get records
    $selectStmt = $pdo->prepare("
        SELECT id, email, username
        FROM users
        WHERE valid = TRUE
          AND (DATE(validts) = CURRENT_DATE + INTERVAL '1 day' OR
               DATE(validts) = CURRENT_DATE + INTERVAL '3 days')
          AND NOT EXISTS (
            SELECT 1 FROM email_log
            WHERE users.id = email_log.user_id AND DATE(sent_date) = CURRENT_DATE
          )
        LIMIT :limit OFFSET :offset
    ");
    $selectStmt->bindValue(':limit', $sendsPerProcess, PDO::PARAM_INT);
    $selectStmt->bindValue(':offset', $index * $sendsPerProcess, PDO::PARAM_INT);
    $selectStmt->execute();

    echo "PID: $index LIMIT: $sendsPerProcess OFFSET: " . $index * $sendsPerProcess . PHP_EOL;

    while ($user = $selectStmt->fetch(PDO::FETCH_ASSOC)) {
        send_email(FROM_EMAIL, $user['email'], "Your subscription is expiring soon");
        $insertLogStmt->execute([$user['id']]);
        echo "PID: $index Sent email to " . $user['id'] . PHP_EOL;
    }
});

echo "Email sending job completed." . PHP_EOL;
