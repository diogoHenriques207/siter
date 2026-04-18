<?php
session_start();

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin();
requireRole('worker');

/* =========================
   SESSÃO SEGURA
========================= */
// Usamos o $user_id para todas as operações
$user_id = $_SESSION['user_id'] ?? ($_SESSION['user']['id'] ?? null);

if(!$user_id){
    header("Location: index.php");
    exit;
}

// Função de Log (caso não esteja no auth.php)
if (!function_exists('addLog')) {
    function addLog($pdo, $userId, $action){
        $pdo->prepare("INSERT INTO logs (user_id, action, created_at) VALUES (?, ?, NOW())")->execute([$userId, $action]);
    }
}


/* =========================
   LÓGICA: ASSUMIR / TERMINAR PROJETO
========================= */

// Ação: Assumir Projeto
if(isset($_GET['assume_project'])){
    $proj_id = intval($_GET['assume_project']);
    $stmtAssume = $pdo->prepare("
        UPDATE projects 
        SET status = 'em_processo', assigned_to = ? 
        WHERE id = ? AND status = 'pendente'
    ");
    if($stmtAssume->execute([$user_id, $proj_id])){
        addLog($pdo, $user_id, "Trabalhador assumiu o projeto ID: $proj_id");
        header("Location: dashboardtrabalhador.php");
        exit;
    }
}

// Ação: Terminar Projeto (NOVA)
if(isset($_GET['finish_project'])){
    $proj_id = intval($_GET['finish_project']);
    $stmtFinish = $pdo->prepare("
        UPDATE projects 
        SET status = 'terminado' 
        WHERE id = ? AND assigned_to = ? AND status = 'em_processo'
    ");
    if($stmtFinish->execute([$proj_id, $user_id])){
        addLog($pdo, $user_id, "Trabalhador concluiu o projeto ID: $proj_id");
        header("Location: dashboardtrabalhador.php");
        exit;
    }
}


/* =========================
   DATA FETCHING
========================= */

// Projetos que ninguém assumiu ainda
$pendentes = $pdo->query("
    SELECT * FROM projects 
    WHERE status='pendente'
    ORDER BY id DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Projetos assumidos por ESTE trabalhador
$stmt = $pdo->prepare("
    SELECT * FROM projects 
    WHERE status='em_processo' 
    AND assigned_to=?
    ORDER BY id DESC
");
$stmt->execute([$user_id]);
$em_processo = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Projetos terminados por ESTE trabalhador
$stmt = $pdo->prepare("
    SELECT * FROM projects 
    WHERE status='terminado' 
    AND assigned_to=?
    ORDER BY id DESC
");
$stmt->execute([$user_id]);
$terminados = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_pendentes = count($pendentes);
$total_em_processo = count($em_processo);
$total_terminados = count($terminados);
$total_projects = $total_pendentes + $total_em_processo + $total_terminados;
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Worker Dashboard</title>
    <style>
        body{ font-family: Arial; background:#0b0f19; color:white; padding:20px; }
        .topbar{ display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
        .btn{ background:#00d4ff; color:#000; padding:10px 15px; border-radius:10px; text-decoration:none; font-weight:bold; transition:0.2s; border:none; cursor:pointer; }
        .btn:hover{ background:#00b8db; transform:scale(1.05); }
        h2{ margin-top:30px; border-left:4px solid #00d4ff; padding-left:10px; }
        .card{ background:#111827; padding:15px; margin:10px 0; border-radius:12px; border:1px solid #1f2937; }
        table{ width: 100%; border-collapse: collapse; }
        hr { border: 0; border-top: 1px solid #1f2937; margin: 15px 0; }
    </style>
</head>
<body>

<div class="topbar">
    <h1>⚡ Worker Dashboard</h1>
    <a class="btn" href="index.php">⬅ Voltar ao início</a>
</div>

<div id="projects">
    <div class="card">
        <h2>Projetos Overview</h2>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px;">
            <div class="card"><h3>Total</h3><p style="font-size:26px;color:#00d4ff"><?= $total_projects ?></p></div>
            <div class="card"><h3>Pendentes</h3><p style="font-size:26px;color:#f59e0b"><?= $total_pendentes ?></p></div>
            <div class="card"><h3>Em Processo</h3><p style="font-size:26px;color:#3b82f6"><?= $total_em_processo ?></p></div>
            <div class="card"><h3>Terminados</h3><p style="font-size:26px;color:#22c55e"><?= $total_terminados ?></p></div>
        </div>
    </div>

    <div class="card">
        <h3>🟡 Disponíveis para Assumir</h3>
        <table>
            <?php foreach($pendentes as $p): ?>
            <tr>
                <td style="padding: 10px 0;">• <?= htmlspecialchars($p['title']) ?></td>
                <td style="text-align: right;">
                    <a href="?assume_project=<?= $p['id'] ?>" class="btn" style="font-size: 12px; padding: 5px 10px;">Assumir</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($pendentes)) echo "<p>Nenhum projeto disponível no momento.</p>"; ?>
        </table>

        <hr>

        <h3>🔵 Meus Projetos Ativos</h3>
<table>
    <?php foreach($em_processo as $p): ?>
    <tr>
        <td style="padding: 10px 0;">
            • <?= htmlspecialchars($p['title']) ?> 
            <br><small style="color: #94a3b8;"><?= htmlspecialchars($p['description']) ?></small>
        </td>
        <td style="text-align: right;">
            <a href="?finish_project=<?= $p['id'] ?>" 
               onclick="return confirm('Confirmar conclusão do projeto?')" 
               class="btn" 
               style="background: #22c55e; font-size: 12px; padding: 5px 10px;">
                Concluir
            </a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php if(empty($em_processo)) echo "<p>Não tens projetos em mãos.</p>"; ?>

        <h3>🟢 Meus Projetos Terminados</h3>
        <?php foreach($terminados as $p): ?>
            <p>• <?= htmlspecialchars($p['title']) ?></p>
        <?php endforeach; ?>
    </div>
</div>

</body>
</html>