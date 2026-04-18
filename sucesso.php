<?php
session_start();
$pdo = new PDO("mysql:host=localhost;dbname=meusite;charset=utf8mb4","root","");

$cart = $_SESSION['cart'] ?? [];
$user_id = $_SESSION['user_id'] ?? null;

if (!$cart) {
    exit("Carrinho vazio");
}

/* =========================
   GET PRODUCTS
========================= */
$ids = array_keys($cart);
$placeholders = str_repeat('?,', count($ids)-1) . '?';

$stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
$stmt->execute($ids);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   CREATE ORDER
========================= */
$pdo->prepare("INSERT INTO orders(user_id,total,status) VALUES(?,?,?)")
    ->execute([$user_id, 0, 'paid']);

$orderId = $pdo->lastInsertId();

$total = 0;
$productLines = [];

foreach ($products as $p) {

    $qty = $cart[$p['id']];
    $subtotal = $p['price'] * $qty;

    $total += $subtotal;

    $productLines[] = "{$p['name']} x{$qty}";

    $pdo->prepare("
        INSERT INTO order_items(order_id, product_id, price, quantity)
        VALUES (?,?,?,?)
    ")->execute([$orderId, $p['id'], $p['price'], $qty]);
}

/* update order total */
$pdo->prepare("UPDATE orders SET total=? WHERE id=?")
    ->execute([$total, $orderId]);

/* =========================
   PRIORITY ENGINE
========================= */
$priority =
    $total < 50 ? 'low' :
    ($total < 150 ? 'normal' :
    ($total < 400 ? 'high' : 'urgent'));

/* =========================
   TRACKING ENGINE
========================= */
$stage = 1;

/* =========================
   AUTO DESCRIPTION AI STYLE
========================= */
$description =
"Projeto automático gerado pela encomenda.\n\n".
"Produtos:\n- ".implode("\n- ", $productLines)."\n\n".
"Total: {$total}€\n".
"Prioridade: {$priority}\n".
"Estado inicial: Encomendado";

/* =========================
   CREATE PROJECT (CORE)
========================= */
$pdo->prepare("
    INSERT INTO projects
    (title, description, status, tracking_status, current_stage, user_id, order_id, priority, phone, address, created_at)
    VALUES (?,?,?,?,?,?,?,?,?,?,NOW())
")->execute([
    "Encomenda #".$orderId,
    $description,
    "pendente",
    "encomendado",
    $stage,
    $user_id,
    $orderId,
    $priority,
    $_POST['phone'] ?? null,
    $_POST['address'] ?? null
]);

/* =========================
   LOG SYSTEM
========================= */
$pdo->prepare("
    INSERT INTO logs (user_id, action, created_at)
    VALUES (?,?,NOW())
")->execute([
    $user_id,
    "Encomenda #$orderId → projeto criado (priority:$priority)"
]);

/* =========================
   NOTIFICATION SYSTEM (base)
========================= */
$pdo->prepare("
    INSERT INTO notifications (user_id, message, seen, created_at)
    VALUES (?,?,0,NOW())
")->execute([
    $user_id,
    "A tua encomenda #$orderId foi criada com sucesso!"
]);

$_SESSION['cart'] = [];
$_SESSION['pending_order'] = $orderId;

?>

<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title>Projeto Criado</title>

<style>
body{
    margin:0;
    font-family: 'Inter', sans-serif;
    background: radial-gradient(circle at top, #0f172a, #020617);
    color:#fff;
    display:flex;
    justify-content:center;
    align-items:center;
    min-height:100vh;
}

/* container principal */
.container{
    width:90%;
    max-width:600px;
    background: rgba(17,24,39,0.7);
    backdrop-filter: blur(15px);
    border:1px solid rgba(255,255,255,0.08);
    border-radius:20px;
    padding:30px;
    box-shadow: 0 0 40px rgba(0,212,255,0.15);
    animation: fadeIn 0.6s ease;
}

/* animação entrada */
@keyframes fadeIn{
    from{opacity:0; transform: translateY(20px);}
    to{opacity:1; transform: translateY(0);}
}

h1{
    color:#00d4ff;
    font-size:28px;
    margin-bottom:10px;
}

h2{
    font-size:16px;
    color:#cbd5e1;
    font-weight:400;
    margin-bottom:20px;
}

form{
    display:flex;
    flex-direction:column;
    gap:12px;
}

input, textarea{
    padding:12px;
    border-radius:12px;
    border:1px solid rgba(255,255,255,0.1);
    background: rgba(255,255,255,0.05);
    color:white;
    outline:none;
    transition:0.3s;
}

input:focus, textarea:focus{
    border-color:#00d4ff;
    box-shadow:0 0 10px rgba(0,212,255,0.3);
}

textarea{
    min-height:100px;
    resize:none;
}

button{
    padding:12px;
    border:none;
    border-radius:12px;
    background: linear-gradient(90deg,#00d4ff,#007cf0);
    color:white;
    font-weight:bold;
    cursor:pointer;
    transition:0.3s;
}

button:hover{
    transform:scale(1.03);
    box-shadow:0 0 20px rgba(0,212,255,0.4);
}

/* badge sucesso */
.success{
    display:flex;
    align-items:center;
    gap:10px;
    background: rgba(34,197,94,0.1);
    border:1px solid rgba(34,197,94,0.3);
    padding:12px;
    border-radius:12px;
    margin-bottom:20px;
    color:#22c55e;
}

/* link */
a{
    display:inline-block;
    margin-top:15px;
    color:#00d4ff;
    text-decoration:none;
}

a:hover{
    text-decoration:underline;
}
</style>

</head>

<body>

<div class="container">

    <div class="success">
        ✅ Compra concluída com sucesso
    </div>

    <h1>🚀 Projeto criado</h1>
    <h2>Agora preenche os teus dados para ativar o teu pedido</h2>

    <form method="POST" action="create_request.php">

        <input type="hidden" name="order_id" value="<?= $orderId ?>">

        <input type="text" name="name" placeholder="Nome completo" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="phone" placeholder="Telefone" required>

        <textarea name="description" placeholder="Descreve o teu pedido em detalhe" required></textarea>

        <button type="submit">Enviar pedido 🚀</button>

    </form>

    <a href="service.php">← Voltar ao serviço</a>

</div>

</body>
</html>