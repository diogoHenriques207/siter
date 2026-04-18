<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title>Sobre Nós</title>
<?php session_start();
?>
<link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">

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
    </nav>
</header>

<section class="hero">
    <h1 data-aos="fade-up">Sobre Nós</h1>
    <p data-aos="fade-up">Inovação e tecnologia</p>
</section>

<section class="grid">
    <div class="card" data-aos="fade-right">
        <h3>Missão</h3>
        <p>Transformar empresas com tecnologia moderna</p>
    </div>

    <div class="card" data-aos="fade-up">
        <h3>Visão</h3>
        <p>Ser referência em soluções digitais</p>
    </div>

    <div class="card" data-aos="fade-left">
        <h3>Valores</h3>
        <p>Inovação, confiança e qualidade</p>
    </div>
</section>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>AOS.init();</script>

</body>
</html>