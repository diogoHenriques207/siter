<?php
session_start();
require_once __DIR__ . "/includes/config.php";

requireLogin();

$worker_id = $_SESSION['user']['id'];
$project_id = $_POST['project_id'];

/* garantir que ainda está livre */
$stmt = $pdo->prepare("
    SELECT * FROM projects
    WHERE id = ? AND status = 'pendente' AND worker_id IS NULL
");
$stmt->execute([$project_id]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$project){
    die("Projeto já foi assumido.");
}

/* assumir projeto */
$pdo->prepare("
    UPDATE projects
    SET status = 'em processo',
        worker_id = ?
    WHERE id = ?
")->execute([$worker_id, $project_id]);

/* log */
$pdo->prepare("
    INSERT INTO logs (user_id, action, created_at)
    VALUES (?,?,NOW())
")->execute([
    $worker_id,
    "Assumiu projeto #$project_id"
]);

header("Location: dashboardtrabalhador.php");
exit;