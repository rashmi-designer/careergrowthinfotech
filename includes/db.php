<?php

function getDbConnection()
{
    $host = 'localhost';
    $username = 'root';
    $password = 'Ankit@123';
    $database = 'job_portal';

    $connection = new mysqli($host, $username, $password, $database);

    if ($connection->connect_error) {
        die('Database connection failed: ' . $connection->connect_error);
    }

    $connection->set_charset('utf8mb4');

    return $connection;
}