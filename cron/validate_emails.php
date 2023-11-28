<?php

require_once dirname(__DIR__) . '/db/db_connect.php';
require_once dirname(__DIR__) . '/utils/helpers.php';

/** @var PDO $pdo */
$pdo = db();

$maxChecks = $argv[1] ?? 140000;
$processCount = $argv[2] ?? 20;

run_parallel_processes($processCount, function ($index) use ($maxChecks, $processCount) {
    // New DB connection for this child process
    $pdo = db();

    // Calculate the number of emails this child process should check
    $checksPerProcess = (int) ceil($maxChecks / $processCount);
    $updateValidStmt = $pdo->prepare("UPDATE users SET valid = :isValid WHERE id = :userId");

    // Get records
    $selectStmt = $pdo->prepare("
        SELECT id, email
        FROM users
        WHERE confirmed = TRUE AND valid IS NULL
        AND DATE(validts) > CURRENT_DATE
        LIMIT :limit OFFSET :offset
    ");
    $selectStmt->bindValue(':limit', $checksPerProcess, PDO::PARAM_INT);
    $selectStmt->bindValue(':offset', $index * $checksPerProcess, PDO::PARAM_INT);
    $selectStmt->execute();

    echo "PID: $index LIMIT: $checksPerProcess OFFSET: " . $index * $checksPerProcess . PHP_EOL;

    while ($user = $selectStmt->fetch(PDO::FETCH_ASSOC)) {
        $isValid = check_email($user['email']);
        $updateValidStmt->bindValue(':isValid', $isValid, PDO::PARAM_BOOL);
        $updateValidStmt->bindValue(':userId', $user['id'], PDO::PARAM_INT);
        $updateValidStmt->execute();
        echo "PID: $index Updated " . $user['id'] . PHP_EOL;
    }
});

echo "Email validation check completed." . PHP_EOL;
