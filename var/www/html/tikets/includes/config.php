<?php
$pdo = new PDO("pgsql:host=localhost;dbname=tiket_db", "tiket_user", "2");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>