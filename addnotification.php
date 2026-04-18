<?php
function addNotification($pdo, $userId, $message){
    $pdo->prepare("
        INSERT INTO notifications (user_id, message)
        VALUES (?, ?)
    ")->execute([$userId, $message]);
} 
addLog($pdo, $_SESSION['user_id'], "Assumiu projeto ID: $id");
addNotification($pdo, $_SESSION['user_id'], "Assumiste o projeto #$id");
addLog($pdo, $_SESSION['user_id'], "Terminou projeto ID: $id");
addNotification($pdo, $_SESSION['user_id'], "Projeto #$id concluído");
addNotification($pdo, $_SESSION['user_id'], "Novo projeto criado: $title");
?>