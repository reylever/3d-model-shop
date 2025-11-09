<?php
require_once 'includes/connect.php';

$sql = 'SELECT id, name, slug, image FROM categories ORDER BY name';
foreach ($pdo->query($sql) as $row) {
    echo $row['id'] . "<br>";
    echo $row['name'] . "<br>";
    echo $row['slug'] . "<br>";
    echo $row['image'] . "<br>";
}
?>  