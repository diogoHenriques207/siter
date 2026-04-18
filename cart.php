<?php
session_start();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (isset($_GET['add'])) {
    $_SESSION['cart'][] = $_GET['add'];
    header("Location: cart.php");
    exit;
}

if (isset($_GET['clear'])) {
    $_SESSION['cart'] = [];
    header("Location: cart.php");
    exit;
}

$cartCount = count($_SESSION['cart']);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title>Carrinho</title>

<link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">

<style>
body{
    margin:0;
    font-family:Segoe UI, sans-serif;
    background:#0f172a;
    color:white;
}

header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:20px 40px;
    background:#111827;
    position:sticky;
    top:0;
}

nav a{
    color:white;
    margin-left:20px;
    text-decoration:none;
    position:relative;
}

.cart-badge{
    background:red;
    border-radius:50%;
    padding:2px 7px;
    font-size:12px;
    position:absolute;
    top:-10px;
    right:-15px;
}

.container{
    max-width:900px;
    margin:60px auto;
    padding:20px;
}

h1{
    font-size:34px;
}

.item{
    background:rgba(255,255,255,0.05);
    padding:15px;
    margin:10px 0;
    border-radius:10px;
    border:1px solid rgba(255,255,255,0.1);
}

button{
    padding:12px 16px;
    border:none;
    border-radius:10px;
    cursor:pointer;
    margin-right:10px;
    margin-top:10px;
    font-weight:bold;
}

button:hover{
    transform:scale(1.05);
}

.danger{
    background:#ef4444;
    color:white;
}

.primary{
    background:#3b82f6;
    color:white;
}

a{
    text-decoration:none;
}

.empty{
    opacity:0.7;
    font-size:18px;
}
</style>

</head>

<body>

<header>
    <div><b>IT SOLUTIONS</b></div>

    <nav>
        <a href="service.php">Serviços</a>

        <a href="cart.php">
            🛒 Carrinho
            <span class="cart-badge"><?= $cartCount ?></span>
        </a>
    </nav>
</header>

<div class="container" data-aos="fade-up">

<h1>🛒 O teu carrinho</h1>

<a href="service.php" style="color:#93c5fd;">← Continuar a comprar</a>

<br><br>

<?php if (empty($_SESSION['cart'])): ?>

    <p class="empty">O carrinho está vazio 😢</p>

<?php else: ?>

    <?php foreach ($_SESSION['cart'] as $item): ?>
        <div class="item" data-aos="fade-right">
            Produto ID: <b><?= htmlspecialchars($item) ?></b>
        </div>
    <?php endforeach; ?>

    <a href="checkout.php">
        <button class="primary">💳 Pagar agora</button>
    </a>

    <a href="cart.php?clear=1">
        <button class="danger">🗑️ Limpar carrinho</button>
    </a>

<?php endif; ?>

</div>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
AOS.init();
</script>

</body>
</html>