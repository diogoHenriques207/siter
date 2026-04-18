<?php include "auth_admin.php"; ?>

<?php
if(isset($_POST['save'])){
    $name = $_POST['name'];
    $desc = $_POST['description'];

    $stmt = $pdo->prepare("INSERT INTO products(name,description) VALUES(?,?)");
    $stmt->execute([$name,$desc]);

    header("Location: admin_products.php");
}
?>

<form method="POST">
    <h1>Criar Serviço</h1>

    <input name="name" placeholder="Nome"><br><br>
    <textarea name="description" placeholder="Descrição"></textarea><br><br>

    <button name="save">Guardar</button>
</form>