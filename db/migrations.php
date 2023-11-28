<?php

require_once 'db_connect.php';

/** @var PDO $pdo */
$pdo = db();

$pdo->exec("DROP TABLE IF EXISTS email_log, users CASCADE;");

// User related
$pdo->exec("
    CREATE TABLE users (
        id SERIAL PRIMARY KEY,
        username VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        validts TIMESTAMP WITHOUT TIME ZONE DEFAULT NULL,
        confirmed BOOLEAN NOT NULL DEFAULT false,
        valid BOOLEAN DEFAULT NULL
    )
");
// Index for users who have a subscription, confirmed email, and an email pending validation.
$pdo->exec("CREATE INDEX idx_confirmed_unvalidated_validts ON users((validts::DATE)) WHERE confirmed = TRUE AND valid IS NULL;");
// Index for users to find whose subscription is ending soon and have valid emails.
$pdo->exec("CREATE INDEX idx_validts_valid ON users((validts::DATE)) WHERE valid = TRUE;");


// Email related
$pdo->exec("CREATE TABLE email_log (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    sent_date TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
)");
// Unique per user per day
$pdo->exec("CREATE UNIQUE INDEX uniq_user_sent_date ON email_log(user_id, (sent_date::DATE));");

echo "Database migration completed successfully." . PHP_EOL;
