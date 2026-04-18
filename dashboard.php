<?php
require_once __DIR__ . "/includes/config.php";
require_once __DIR__ . "/includes/auth.php";

// A função addLog deve estar disponível antes de ser chamada
if (!function_exists('addLog')) {
    function addLog($pdo, $userId, $action){
        $pdo->prepare("
            INSERT INTO logs (user_id, action, created_at)
            VALUES (?, ?, NOW())
        ")->execute([$userId, $action]);
    }
}

requireLogin();
requireRole('admin');

$currentUserId = $_SESSION['user_id'] ?? 0;

/* =========================
   AÇÕES DE PROJETOS (GET)
========================= */

// Ação: Assumir Projeto
if(isset($_GET['assume_project'])){
    $proj_id = intval($_GET['assume_project']);
    $stmtAssume = $pdo->prepare("
        UPDATE projects 
        SET status = 'em_processo', assigned_to = ? 
        WHERE id = ? AND status = 'pendente'
    ");
    if($stmtAssume->execute([$currentUserId, $proj_id])){
        addLog($pdo, $currentUserId, "Admin assumiu o projeto ID: $proj_id");
        header("Location: dashboard.php#projects");
        exit;
    }
}

// Ação: Terminar Projeto
if(isset($_GET['finish_project'])){
    $proj_id = intval($_GET['finish_project']);
    $stmtFinish = $pdo->prepare("
        UPDATE projects 
        SET status = 'terminado' 
        WHERE id = ? AND assigned_to = ? AND status = 'em_processo'
    ");
    if($stmtFinish->execute([$proj_id, $currentUserId])){
        addLog($pdo, $currentUserId, "Admin concluiu o projeto ID: $proj_id");
        header("Location: dashboard.php#projects");
        exit;
    }
}

/* =========================
   POST ACTIONS
========================= */

// Criar Produto
if(isset($_POST['create_product'])){
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);

    if($name !== '' && $price !== false){
        $pdo->prepare("INSERT INTO products (name, description, price) VALUES (?, ?, ?)")
            ->execute([$name, $description, $price]);
        addLog($pdo, $currentUserId, "Criou o produto: $name");
    }
}

// Criar Projeto (Unificado)
if(isset($_POST['create_project'])){
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $assigned_to = (!empty($_POST['assigned_to'])) ? intval($_POST['assigned_to']) : null;

    if($title !== ''){
        $stmt = $pdo->prepare("
            INSERT INTO projects (title, description, status, assigned_to)
            VALUES (?, ?, 'pendente', ?)
        ");
        $stmt->execute([$title, $description, $assigned_to]);
        addLog($pdo, $currentUserId, "Criou o projeto: $title");
        header("Location: dashboard.php#projects");
        exit;
    }
}

// Update User Role
if(isset($_POST['update_user'])){
    $id = intval($_POST['id']);
    $role = $_POST['role'];
    $allowedRoles = ['admin','user','worker'];
    if(in_array($role, $allowedRoles)){
        $pdo->prepare("UPDATE users SET role=? WHERE id=?")->execute([$role, $id]);
        addLog($pdo, $currentUserId, "Alterou cargo do utilizador ID $id para $role");
    }
}

// Reset Pass
if(isset($_POST['resetpass']) && !empty($_POST['password'])){
    $id = intval($_POST['id']);
    $newPass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([$newPass, $id]);
    addLog($pdo, $currentUserId, "Reset de password para utilizador ID $id");
}

/* =========================
   DATA FETCHING
========================= */
$products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

$usersCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$productsCount = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$latestUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE DATE(created_at)=CURDATE()")->fetchColumn() ?? 0;
$totalRevenue = $pdo->query("SELECT SUM(total) FROM orders")->fetchColumn() ?? 0;

$usersPerDay = $pdo->query("SELECT DATE(created_at) as day, COUNT(*) as total FROM users GROUP BY DATE(created_at) ORDER BY day ASC")->fetchAll(PDO::FETCH_ASSOC);
$ordersPerDay = $pdo->query("SELECT DATE(created_at) as day, SUM(total) as revenue FROM orders GROUP BY DATE(created_at) ORDER BY day ASC")->fetchAll(PDO::FETCH_ASSOC);

$pendentes = $pdo->query("SELECT * FROM projects WHERE status='pendente' ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$em_processo = $pdo->query("SELECT p.*, u.email FROM projects p LEFT JOIN users u ON p.assigned_to = u.id WHERE p.status='em_processo' ORDER BY p.id DESC")->fetchAll(PDO::FETCH_ASSOC);
$terminados = $pdo->query("SELECT p.*, u.email FROM projects p LEFT JOIN users u ON p.assigned_to = u.id WHERE p.status='terminado' ORDER BY p.id DESC")->fetchAll(PDO::FETCH_ASSOC);

$total_pendentes = count($pendentes);
$total_em_processo = count($em_processo);
$total_terminados = count($terminados);
$total_projects = $total_pendentes + $total_em_processo + $total_terminados;
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap');
        :root{ --bg:#070a12; --panel:rgba(255,255,255,0.04); --stroke:rgba(255,255,255,0.08); --neon:#00e5ff; --shadow:0 10px 40px rgba(0,0,0,0.6); }
        body{ margin:0; font-family:Inter, sans-serif; background: var(--bg); color:#fff; }
        .sidebar{ width:260px; height:100vh; position:fixed; top:0; left:0; padding:20px; background:rgba(15, 23, 42, 0.55); backdrop-filter: blur(18px); border-right:1px solid var(--stroke); }
        .sidebar h2{ color:var(--neon); text-shadow:0 0 10px var(--neon); }
        .tab-btn{ width:100%; padding:12px; margin:8px 0; background:var(--panel); border:1px solid var(--stroke); color:#fff; border-radius:12px; cursor:pointer; }
        .tab-btn.active{ border-color:var(--neon); background:rgba(0,229,255,0.1); }
        .main{ margin-left:260px; padding:40px; }
        .card{ background:var(--panel); border:1px solid var(--stroke); border-radius:16px; padding:20px; margin-bottom:20px; }
        input, select{ width:100%; padding:10px; margin:6px 0; border-radius:10px; border:1px solid var(--stroke); background:rgba(0,0,0,0.3); color:#fff; }
        button, .btn{ padding:10px 14px; border:none; border-radius:10px; background:var(--neon); color:#000; font-weight:700; cursor:pointer; text-decoration:none; display:inline-block; }
        table{ width:100%; border-collapse:collapse; }
        th,td{ padding:12px; border-bottom:1px solid rgba(255,255,255,0.06); text-align:left; }
        .tab{ display:none; }
        .tab.active{ display:block; }
        hr { border: 0; border-top: 1px solid var(--stroke); margin: 15px 0; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Admin Panel</h2>
    <button class="tab-btn" onclick="window.location.href='index.php'">🏠 Voltar</button>
    <button class="tab-btn active" onclick="openTab('products', this)">📦 Produtos</button>
    <button class="tab-btn" onclick="openTab('users', this)">👥 Utilizadores</button>
    <button class="tab-btn" onclick="openTab('charts', this)">📊 Gráficos</button>
    <button class="tab-btn" onclick="openTab('projects', this)">📂 Projetos</button>
</div>

<div class="main">
    <div id="products" class="tab active">
        <div class="card">
            <h2>Criar Produto</h2>
            <form method="POST">
                <input name="name" placeholder="Nome" required>
                <input name="description" placeholder="Descrição">
                <input name="price" placeholder="Preço" required>
                <button name="create_product">Criar</button>
            </form>
        </div>
        <div class="card">
            <h2>Produtos Recentes</h2>
            <?php foreach($products as $p): ?>
                <p><b><?= htmlspecialchars($p['name']) ?></b> - <?= number_format($p['price'],2) ?>€</p>
                <hr>
            <?php endforeach; ?>
        </div>
    </div>

    <div id="users" class="tab">
        <div class="card">
            <h2>Gestão de Utilizadores</h2>
            <table>
                <tr><th>Email</th><th>Role</th><th>Ações</th></tr>
                <?php foreach($users as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td>
                        <form method="POST" style="display:flex; gap:5px;">
                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                            <select name="role">
                                <option value="user" <?= $u['role']=='user'?'selected':'' ?>>User</option>
                                <option value="worker" <?= $u['role']=='worker'?'selected':'' ?>>Worker</option>
                                <option value="admin" <?= $u['role']=='admin'?'selected':'' ?>>Admin</option>
                            </select>
                            <button name="update_user">OK</button>
                        </form>
                    </td>
                    <td>
                        <form method="POST" style="display:flex; gap:5px;">
                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                            <input type="password" name="password" placeholder="Nova pass">
                            <button name="resetpass">Reset</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>

    <div id="charts" class="tab">
        <div class="card">
            <h2>Estatísticas</h2>
            <div style="height:300px;"><canvas id="chart"></canvas></div>
        </div>
    </div>

    <div id="projects" class="tab">
    <div class="card">
        <h2>Criar Projeto</h2>
        <form method="POST">
            <input name="title" placeholder="Título do projeto" required>
            <input name="description" placeholder="Descrição">
            <select name="assigned_to">
                <option value="">Sem atribuição</option>
                <?php foreach($users as $u): ?>
                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['email']) ?> (<?= $u['role'] ?>)</option>
                <?php endforeach; ?>
            </select>
            <button name="create_project">Criar projeto</button>
        </form>
    </div>

    <div class="card">
        <h2>Projetos Overview</h2>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:10px;">
            <div class="card" style="margin:0"><h3>Total</h3><p style="font-size:26px;color:#00d4ff"><?= $total_projects ?></p></div>
            <div class="card" style="margin:0"><h3>Pendentes</h3><p style="font-size:26px;color:#f59e0b"><?= $total_pendentes ?></p></div>
            <div class="card" style="margin:0"><h3>Em Processo</h3><p style="font-size:26px;color:#3b82f6"><?= $total_em_processo ?></p></div>
            <div class="card" style="margin:0"><h3>Terminados</h3><p style="font-size:26px;color:#22c55e"><?= $total_terminados ?></p></div>
        </div>
    </div>

    <div class="card">
        <h3>🟡 Disponíveis para Assumir</h3>
        <table>
            <?php foreach($pendentes as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['title']) ?></td>
                <td style="text-align:right">
                    <a href="?assume_project=<?= $p['id'] ?>" class="btn" style="font-size:11px">Assumir</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>

        <hr>

        <h3>🔵 Em Processo</h3>
        <table>
            <?php foreach($em_processo as $p): ?>
            <tr>
                <td>
                    <?= htmlspecialchars($p['title']) ?> 
                    <br><small style="opacity:0.6">Assinado a: <?= htmlspecialchars($p['email'] ?? 'N/A') ?></small>
                </td>
                <td style="text-align:right">
                    <?php if($p['assigned_to'] == $currentUserId): ?>
                        <a href="?finish_project=<?= $p['id'] ?>" onclick="return confirm('Fechar projeto?')" class="btn" style="background:#22c55e; font-size:11px">Concluir</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>

        <hr>

        <h3>🟢 Terminados</h3>
        <table>
            <?php foreach($terminados as $p): ?>
            <tr>
                <td>• <?= htmlspecialchars($p['title']) ?> <small>(Por: <?= htmlspecialchars($p['email'] ?? 'N/A') ?>)</small></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div class="card">
        <h2>📜 Logs de Atividade</h2>
        <div style="max-height: 400px; overflow-y: auto;">
            <?php
            $logs = $pdo->query("
                SELECT l.*, u.email 
                FROM logs l 
                LEFT JOIN users u ON l.user_id = u.id 
                ORDER BY l.id DESC 
                LIMIT 30
            ")->fetchAll(PDO::FETCH_ASSOC);

            if(empty($logs)): echo "<p style='opacity:0.5'>Nenhuma atividade registada.</p>"; endif;

            foreach($logs as $log): ?>
                <p style="font-size:13px; border-bottom:1px solid var(--stroke); padding: 8px 0; margin:0;">
                    <span style="color:var(--neon)"><?= date('d/m H:i', strtotime($log['created_at'])) ?></span> | 
                    <b style="color: #fff;"><?= htmlspecialchars($log['email'] ?? 'Sistema') ?></b>: 
                    <span style="color: #94a3b8;"><?= htmlspecialchars($log['action']) ?></span>
                </p>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
function openTab(tab, el){
    document.querySelectorAll('.tab').forEach(t=>t.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
    document.getElementById(tab).classList.add('active');
    el.classList.add('active');
}

const labels = <?= json_encode(array_column($usersPerDay,'day')) ?>;
new Chart(document.getElementById('chart'),{
    type:'line',
    data:{
        labels:labels,
        datasets:[
            {label:'Utilizadores', data:<?= json_encode(array_column($usersPerDay,'total')) ?>, borderColor:'#00d4ff', tension:0.3},
            {label:'Revenue (€)', data:<?= json_encode(array_column($ordersPerDay,'revenue')) ?>, borderColor:'#22c55e', tension:0.3}
        ]
    },
    options: { responsive: true, maintainAspectRatio: false }
});
</script>
</body>
</html>