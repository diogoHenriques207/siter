<?php include "auth_admin.php"; ?>

<?php
$id = $_GET['id'];

$stmt = $pdo->prepare("DELETE FROM products WHERE id=?");
$stmt->execute([$id]);

header("Location: admin_products.php");