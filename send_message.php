<?php
session_start();
require_once "includes/config.php";

$user_id = $_SESSION['user']['id'];
$project_id = $_POST['project_id'];
$message = $_POST['message'];

$pdo->prepare("
    INSERT INTO messages (project_id, sender_id, message)
    VALUES (?,?,?)
")->execute([$project_id, $user_id, $message]);

header("Location: dashboard.php");
exit;