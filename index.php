<?php
session_start();
$pdo = new PDO("mysql:host=localhost;dbname=meusite;charset=utf8mb4","root","");
$servicos = $pdo->query("SELECT * FROM products LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
?>
<?php if(isset($_GET['logout'])): ?>
<div class="toast show">Logout feito com sucesso 👋</div>
<?php endif; ?>
<?php if(isset($_SESSION['user'])): ?>
<?php else: ?>
<?php endif; ?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title>IT Solutions</title>
<link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
<style>
<?php include "style.css"; ?>
</style>
</head>
<body>
    <style>/* Container do Card */
.card-container {
    padding: 40px 20px;
    display: flex;
    justify-content: center;
}

/* Base do Card Glassmorphism */
.glass-card {
    background: rgba(255, 255, 255, 0.03);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 24px;
    padding: 35px;
    max-width: 400px;
    position: relative;
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
}

.glass-card:hover {
    transform: translateY(-10px);
    border-color: #00d4ff;
    box-shadow: 0 20px 40px rgba(0, 212, 255, 0.15);
}

/* Badge Superior */
.card-badge {
    background: rgba(0, 212, 255, 0.1);
    color: #00d4ff;
    padding: 5px 15px;
    border-radius: 50px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    display: inline-block;
    margin-bottom: 15px;
    border: 1px solid rgba(0, 212, 255, 0.3);
}

.glass-card h3 {
    margin: 0 0 15px 0;
    font-size: 22px;
    color: #fff;
    font-weight: 600;
}

.glass-card p {
    color: #94a3b8;
    line-height: 1.6;
    font-size: 14px;
    margin-bottom: 25px;
}

/* Botão Estilo Neon */
.btn-neon {
    text-decoration: none;
    display: inline-block;
    padding: 12px 25px;
    color: #00d4ff;
    border: 1px solid #00d4ff;
    border-radius: 12px;
    font-weight: 600;
    font-size: 13px;
    transition: 0.3s;
    background: transparent;
    position: relative;
    overflow: hidden;
}

.btn-neon:hover {
    background: #00d4ff;
    color: #000;
    box-shadow: 0 0 20px rgba(0, 212, 255, 0.6);
}

/* Efeito de brilho sutil no fundo do card */
.glass-card::before {
    content: "";
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(0, 212, 255, 0.05) 0%, transparent 70%);
    pointer-events: none;
}</style>
<header>
    <div class="logo">IT SOLUTIONS</div>
    <nav class="menu">
        <a href="index.php">Home</a>
        <a href="service.php">Serviços</a>
        <a href="sobre.php">Sobre</a>
        <a href="contactos.php">Contactos</a>
        <?php if(isset($_SESSION['user'])): ?>
            <?php if($_SESSION['user']['role'] === 'admin'): ?>
                <a href="dashboard.php" class="admin">Dashboard</a>
            <?php endif; ?>
            <?php if($_SESSION['user']['role'] === 'user'): ?>
                <a href="dashboard user.php" class="user">Dashboard</a>
                 <a href="cart.php">
            🛒 Carrinho
            <span class="cart-badge">
                <?= count($_SESSION['cart'] ?? []) ?>
            </span>
        </a>
            <?php endif; ?>
            <a href="logout.php" class="logout">Logout</a>
        <?php else: ?>
            <a href="login.php" class="login">Login</a>
            <a href="register.php" class="register">Registo</a>
        <?php endif; ?>
    </nav>
</header>
<section class="hero">
    <h1 data-aos="fade-up">Transformamos Tecnologia</h1>
    <p data-aos="fade-up">Soluções modernas para empresas</p>
    <a class="btn" href="service.php">Explorar Serviços</a>
    <section class="card-container">
    <div class="glass-card" data-aos="zoom-in">
        <div class="card-badge">Beta Test</div>
        <h3>Ambiente de Testes</h3>
        <p>Estamos a otimizar a sua experiência. Caso encontre alguma anomalia, a nossa equipa técnica está à distância de um clique.</p>
        <div class="card-footer">
            <a href="contactos.php" class="btn-neon">
                <span>Contactar Suporte</span>
            </a>
        </div>
    </div>
</section>

<section class="grid">
</section>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>AOS.init();
    setTimeout(() => {
    const toast = document.querySelector('.toast');
    if(toast){
        toast.classList.remove('show');
    }
}, 3000);
</script>

</body>
</html>