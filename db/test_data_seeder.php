<?php

require_once 'db_connect.php';

/** @var PDO $pdo */
$pdo = db();

$defaultUserCount = 100000;
$defaultUnconfirmedPercentage = 85; // 85% of users do not confirm their email
$defaultActiveSubscriptionPercentage = 20; // 20% of users have active subscriptions

// Retrieve parameters
$userCount = $argc > 1 ? (int)$argv[1] : $defaultUserCount;
$unconfirmedPercentage = $argc > 2 ? (int)$argv[2] : $defaultUnconfirmedPercentage;
$activeSubscriptionPercentage = $argc > 3 ? (int)$argv[3] : $defaultActiveSubscriptionPercentage;

$batchSize = 5000;
$insertQueryBase = "
    INSERT INTO users (username, email, validts, confirmed, valid) VALUES 
";

$values = [];

for ($i = 1; $i <= $userCount; $i++) {
    $isConfirmed = (rand(1, 100) > $unconfirmedPercentage);
    $hasActiveSubscription = (rand(1, 100) <= $activeSubscriptionPercentage);
    $validts = $hasActiveSubscription ? "to_timestamp(" . (time() + rand(1, 30) * 24 * 60 * 60) . ")" : "NULL";
    $valid = "NULL";

    $values[] = sprintf(
        "(%s, %s, %s, %s, %s)",
        $pdo->quote("user$i"),
        $pdo->quote("user$i@example.com"),
        $validts,
        $pdo->quote($isConfirmed ? 'true' : 'false'),
        $valid
    );

    if (count($values) === $batchSize || $i === $userCount) {
        $insertQuery = $insertQueryBase . implode(', ', $values);
        $pdo->exec($insertQuery);
        $values = [];
    }
}

echo "Database seeding completed: $userCount users inserted." . PHP_EOL;
