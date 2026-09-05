<?php

function getDbConnection()
{
    $host = '127.0.0.1';
    $port = 3307;
    $username = 'root';
    $password = '';
    $database = 'job_portal';

    $connection = new mysqli(
        $host,
        $username,
        $password,
        $database,
        $port
    );

    if ($connection->connect_error) {
        die('Database connection failed: ' . $connection->connect_error);
    }

    $connection->set_charset('utf8mb4');

    return $connection;
}