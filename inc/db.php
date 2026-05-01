<?php
$conn = new mysqli("db", "root", "root", "autorent");

if ($conn->connect_error) {
    die("DB error");
}
?>
