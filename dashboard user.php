<?php
require_once __DIR__ . "/includes/config.php";
require_once __DIR__ . "/includes/auth.php";

requireLogin();

// Ajustado para bater com a lógica do seu admin: $_SESSION['user_id']
$userId = $_SESSION['user_id'] ?? 0;

/* =====================
   ORDERS DO USER
===================== */
$stmtOrders = $pdo->prepare("
    SELECT * FROM orders
    WHERE user_id = ?
    ORDER BY created_at DESC
");
$stmtOrders->execute([$userId]);
$orders = $stmtOrders->fetchAll(PDO::FETCH_ASSOC);

/* =====================
   MÉTRICAS
===================== */
$stmtTotal = $pdo->prepare("
    SELECT SUM(total) FROM orders WHERE user_id = ?
");
$stmtTotal->execute([$userId]);
$totalSpent = $stmtTotal->fetchColumn() ?? 0;

$totalOrders = count($orders);
$avgOrder = $totalOrders > 0 ? $totalSpent / $totalOrders : 0;

$stmtLast = $pdo->prepare("
    SELECT * FROM orders
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 1
");
$stmtLast->execute([$userId]);
$lastOrder = $stmtLast->fetch(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>My Dashboard</title>
    <style>
        :root{
            --bg:#0b0f19;
            --card:#111827;
            --text:#fff;
            --muted:#94a3b8;
            --border:#1f2937;
            --accent:#00d4ff;
        }
        body{
            margin:0;
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
        }
        .header { padding: 20px; display: flex; align-items: center; justify-content: space-between; }
        .cards{
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
            gap:15px;
            padding:0 20px 20px 20px;
        }
        .card{
            background:var(--card);
            padding:20px;
            border-radius:14px;
            border:1px solid var(--border);
        }
        .card h3 { margin: 0; font-size: 14px; color: var(--muted); text-transform: uppercase; }
        .card h2 { margin: 10px 0 0 0; color: var(--accent); }
        
        table{ width:100%; border-collapse:collapse; margin-top: 10px; }
        th,td{ padding:12px; border-bottom:1px solid var(--border); text-align:left; }
        th { color: var(--muted); font-size: 13px; }
        
        .back-btn{
            color: var(--accent);
            text-decoration: none;
            font-weight: bold;
            border: 1px solid var(--accent);
            padding: 8px 15px;
            border-radius: 8px;
            transition: 0.3s;
        }
        .back-btn:hover{ background: var(--accent); color: #000; }
        .status { padding: 4px 8px; border-radius: 6px; font-size: 12px; background: var(--border); }
    </style>
</head>
<body>

<div class="header">
    <h1>👤 My Dashboard</h1>
    <a href="index.php" class="back-btn">← Voltar à Loja</a>
</div>

<div class="cards">
    <div class="card">
        <h3>Total gasto</h3>
        <h2><?= number_format($totalSpent, 2, ',', '.') ?> €</h2>
    </div>
    <div class="card">
        <h3>Total compras</h3>
        <h2><?= $totalOrders ?></h2>
    </div>
    <div class="card">
        <h3>Média por compra</h3>
        <h2><?= number_format($avgOrder, 2, ',', '.') ?> €</h2>
    </div>
    <div class="card">
        <h3>Última compra</h3>
        <h2><?= $lastOrder ? number_format($lastOrder['total'], 2, ',', '.') . ' €' : '---' ?></h2>
    </div>
</div>

<div style="padding: 20px;">
    <div class="card">
        <h2 style="margin-top:0">📦 Minhas compras</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Data</th>
                </tr>
            </thead>
            <tbody>
                <?php if($totalOrders > 0): ?>
                    <?php foreach($orders as $o): ?>
                    <tr>
                        <td>#<?= $o['id'] ?></td>
                        <td><b><?= number_format($o['total'], 2, ',', '.') ?> €</b></td>
                        <td><span class="status"><?= ucfirst($o['status']) ?></span></td>
                        <td><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align:center; padding: 40px; color: var(--muted);">Ainda não realizou nenhuma compra.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>