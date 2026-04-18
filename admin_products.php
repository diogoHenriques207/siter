<?php include "auth_admin.php"; ?>

<?php
$products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
<title>Serviços Admin</title>

<style>
body{
    font-family:Arial;
    background:#0b1220;
    color:white;
    padding:40px;
}

table{
    width:100%;
    border-collapse:collapse;
}

td,th{
    padding:10px;
    border-bottom:1px solid rgba(255,255,255,0.1);
}

a{
    color:#00d4ff;
}
.btn{
    padding:6px 10px;
    background:#00d4ff;
    color:#000;
    border-radius:6px;
    text-decoration:none;
}
.delete{
    background:red;
    color:white;
}
</style>
</head>

<body>

<h1>Serviços</h1>

<a class="btn" href="admin_product_create.php">+ Criar Serviço</a>

<table>
<tr>
    <th>ID</th>
    <th>Nome</th>
    <th>Ações</th>
</tr>

<?php foreach($products as $p): ?>
<tr>
    <td><?= $p['id'] ?></td>
    <td><?= $p['name'] ?></td>
    <td>
        <a class="btn" href="admin_product_edit.php?id=<?= $p['id'] ?>">Editar</a>
        <a class="btn delete" href="admin_product_delete.php?id=<?= $p['id'] ?>">Apagar</a>
    </td>
</tr>
<?php endforeach; ?>

</table>

</body>
</html>