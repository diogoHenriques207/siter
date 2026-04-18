<?php include "auth_admin.php"; ?>

<?php
$users = $pdo->query("SELECT * FROM users")->fetchAll(PDO::FETCH_ASSOC);
?>

<h1>Utilizadores</h1>

<table>
<?php foreach($users as $u): ?>
<tr>
    <td><?= $u['email'] ?></td>
    <td><?= $u['role'] ?></td>
</tr>
<?php endforeach; ?>
</table>