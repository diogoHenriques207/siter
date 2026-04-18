<?php
session_start();

try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=meusite;charset=utf8mb4",
        "root",
        "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $stmt = $pdo->query("SELECT * FROM products");
    $servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Erro na base de dados: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title>Serviços</title>

<link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
<link rel="stylesheet" href="style.css">

<style>
<?php include "style.css"; ?>
</style>
</head>

<body>

<header>
    <div class="logo">IT SOLUTIONS</div>
    <nav>
        <a href="index.php">Home</a>
        <a href="servicos.php">Serviços</a>
        <a href="sobre.php">Sobre</a>
        <a href="contactos.php">Contactos</a>
        <a href="cart.php">
        🛒 Carrinho
        <span class="cart-badge">
        <?= count($_SESSION['cart'] ?? []) ?>
        </span>
        </a>
</header>

<section class="hero">
    <h1 data-aos="fade-up">Os Nossos Serviços</h1>
    <p data-aos="fade-up">Soluções profissionais para empresas</p>
</section>

<section class="grid">

<?php foreach($servicos as $s): ?>

    <div class="card" data-aos="zoom-in">

        <h3><?= htmlspecialchars($s['nome'] ?? $s['name']) ?></h3>

        <div class="price">
            <?= number_format($s['price'] ?? 0, 2) ?>€
        </div>

        <a href="cart.php?add=<?= $s['id'] ?>">
            <button type="button">Adicionar ao carrinho</button>
        </a>

        <a href="checkout.php?id=<?= $s['id'] ?>">
            <button type="button">Comprar agora</button>
        </a>

    </div>

<?php endforeach; ?>

</section>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>AOS.init();</script>

</body>
</html>