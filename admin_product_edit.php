<?php include "auth_admin.php"; ?>

<?php
$id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM products WHERE id=?");
$stmt->execute([$id]);
$p = $stmt->fetch();

if(isset($_POST['update'])){
    $name = $_POST['name'];
    $desc = $_POST['description'];

    $stmt = $pdo->prepare("UPDATE products SET name=?, description=? WHERE id=?");
    $stmt->execute([$name,$desc,$id]);

    header("Location: admin_products.php");
}
?>

<form method="POST">
    <h1>Editar Serviço</h1>

    <input name="name" value="<?= $p['name'] ?>"><br><br>
    <textarea name="description"><?= $p['description'] ?></textarea><br><br>

    <button name="update">Atualizar</button>
</form>