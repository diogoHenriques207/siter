<?php
session_start();

$pdo = new PDO(
    "mysql:host=localhost;dbname=meusite;charset=utf8mb4",
    "root",
    "",
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$ids = $_SESSION['cart'] ?? [];

if (empty($ids)) {
    die("<div style='font-family:sans-serif;padding:40px;text-align:center;'>🛒 Carrinho vazio</div>");
}

$placeholders = implode(',', array_fill(0, count($ids), '?'));

$stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
$stmt->execute($ids);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = 0;
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title>Checkout</title>

<link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">

<style>
body{
    margin:0;
    font-family: 'Segoe UI', sans-serif;
    background: linear-gradient(135deg,#0f172a,#1e293b);
    color:#fff;
}

.container{
    max-width:900px;
    margin:60px auto;
    padding:20px;
}

h1{
    text-align:center;
    font-size:38px;
    margin-bottom:10px;
}

.sub{
    text-align:center;
    opacity:.7;
    margin-bottom:30px;
}

.card{
    background: rgba(255,255,255,0.05);
    backdrop-filter: blur(10px);
    border:1px solid rgba(255,255,255,0.1);
    border-radius:16px;
    padding:20px;
    box-shadow:0 10px 30px rgba(0,0,0,0.3);
}

.item{
    display:flex;
    justify-content:space-between;
    padding:14px;
    margin:10px 0;
    border-radius:10px;
    transition:0.3s;
    background: rgba(255,255,255,0.03);
}

.item:hover{
    transform:scale(1.02);
    background: rgba(255,255,255,0.08);
}

.price{
    font-weight:bold;
    color:#38bdf8;
}

.total{
    margin-top:20px;
    padding:20px;
    font-size:22px;
    text-align:right;
    border-top:1px solid rgba(255,255,255,0.2);
}

.total span{
    color:#22c55e;
    font-size:28px;
    font-weight:bold;
}

button{
    width:100%;
    margin-top:20px;
    padding:16px;
    font-size:18px;
    border:none;
    border-radius:12px;
    cursor:pointer;
    background: linear-gradient(90deg,#3b82f6,#22c55e);
    color:white;
    font-weight:bold;
    transition:0.3s;
}

button:hover{
    transform:translateY(-2px);
    box-shadow:0 10px 20px rgba(0,0,0,0.3);
}

a{
    display:block;
    text-align:center;
    margin-top:20px;
    color:#93c5fd;
    text-decoration:none;
}
</style>
</head>

<body>

<div class="container" data-aos="fade-up">

<h1>💳 Checkout</h1>
<p class="sub">Confirma a tua compra antes de pagar</p>

<div class="card">

<?php foreach ($products as $p): ?>
    <?php
        $price = $p['price'] ?? 0;
        $total += $price;
    ?>

    <div class="item" data-aos="fade-right">
        <span><?= htmlspecialchars($p['nome'] ?? $p['name']) ?></span>
        <span class="price"><?= number_format($price, 2) ?>€</span>
    </div>

<?php endforeach; ?>

<div class="total">
    Total: <span><?= number_format($total, 2) ?>€</span>
</div>

<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
    <input type="hidden" name="cmd" value="_xclick">
    <input type="hidden" name="business" value="diogohenri1481@gmail.com">
    <input type="hidden" name="amount" value="<?= $total ?>">
    <input type="hidden" name="currency_code" value="EUR">

    <input type="hidden" name="return" value="http://localhost/meusite/sucesso.php">
    <input type="hidden" name="cancel_return" value="http://localhost/meusite/cancelado.php">

    <button type="submit">💳 Pagar com PayPal</button>
</form>

<a href="cart.php">← Voltar ao carrinho</a>

</div>
</div>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
AOS.init({
    duration: 800,
    once: true
});
</script>

</body>
</html>