<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title>Pagamento cancelado</title>

<style>
body{
    margin:0;
    font-family: 'Segoe UI', sans-serif;
    background: linear-gradient(135deg,#0f172a,#1e293b);
    color:#fff;
    display:flex;
    align-items:center;
    justify-content:center;
    height:100vh;
}

.card{
    background: rgba(255,255,255,0.06);
    border:1px solid rgba(255,255,255,0.1);
    padding:40px;
    border-radius:18px;
    text-align:center;
    max-width:420px;
    box-shadow:0 20px 50px rgba(0,0,0,0.4);
    backdrop-filter: blur(10px);
    animation: fadeIn 0.6s ease;
}

h1{
    font-size:28px;
    margin-bottom:10px;
}

p{
    opacity:0.8;
    margin-bottom:25px;
    font-size:15px;
}

.icon{
    font-size:50px;
    margin-bottom:10px;
}

a{
    display:inline-block;
    padding:12px 18px;
    border-radius:10px;
    text-decoration:none;
    font-weight:bold;
    transition:0.3s;
    margin:5px;
}

.back{
    background:#3b82f6;
    color:white;
}

.back:hover{
    background:#2563eb;
    transform:translateY(-2px);
}

.shop{
    background:rgba(255,255,255,0.08);
    color:white;
    border:1px solid rgba(255,255,255,0.2);
}

.shop:hover{
    background:rgba(255,255,255,0.15);
    transform:translateY(-2px);
}

@keyframes fadeIn{
    from{opacity:0; transform:translateY(10px);}
    to{opacity:1; transform:translateY(0);}
}
</style>
</head>

<body>

<div class="card">
    <div class="icon">❌</div>
    <h1>Pagamento cancelado</h1>
    <p>Não te preocupes — nenhum valor foi cobrado.<br>Podes voltar ao carrinho e tentar novamente.</p>

    <a class="back" href="cart.php">🛒 Voltar ao carrinho</a>
    <a class="shop" href="servicos.php">🛍 Continuar a comprar</a>
</div>

</body>
</html>