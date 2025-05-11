<?php
$host = "localhost";
$db = "tiket_db";
$user = "tiket_user";
$password = "2";

$conn = pg_connect("host=$host dbname=$db user=$user password=$password");

if (!$conn) {
    die("Error al conectar con la base de datos.");
}
?>
