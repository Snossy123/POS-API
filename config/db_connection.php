<?php
function getDbConnection() {
    $host = 'localhost';
    $dbname = 'your_database';
    $username = 'your_username';
    $password = 'your_password';
    return new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
}
