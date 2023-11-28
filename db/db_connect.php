<?php

const DB_HOST = 'postgres';
const DB_NAME = 'notification_service';
const DB_USER = 'user';
const DB_PASS = 'pass';
const DB_PORT = '5432';

function db()
{
    try {
        $pdo = new PDO("pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}
