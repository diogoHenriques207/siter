<?php
session_start();

$pdo = new PDO("mysql:host=localhost;dbname=meusite;charset=utf8mb4","root","");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$error = "";

if(isset($_POST['login'])){
    $email = $_POST['email'];
    $pass = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email=?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if($user && password_verify($pass, $user['password'])){

        // 🚨 conta bloqueada
        if($user['status'] === 'banned'){
            $error = "🚫 Conta bloqueada";
        } else {
            $_SESSION['user'] = $user;

            // 🎯 REDIRECT INTELIGENTE
            if($user['role'] === 'admin'){
                header("Location: dashboard.php");
            }elseif ($user['role'] === 'worker'){
                header("Location: dashboardtrabalhador.php");
            } else {
                header("Location: dashboard user.php");
            }
            exit;
        }
    } else {
        $error = "Email ou password inválidos";
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title>Login</title>

<style>
body{
    margin:0;
    font-family:Arial;
    background: radial-gradient(circle at top, #0f172a, #020617);
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    color:white;
}

/* CARD */
.box{
    width:380px;
    padding:40px;
    background:rgba(255,255,255,0.06);
    border:1px solid rgba(255,255,255,0.1);
    border-radius:20px;
    backdrop-filter:blur(15px);
    box-shadow:0 20px 60px rgba(0,0,0,0.5);
    animation:fade .5s ease;
}

@keyframes fade{
    from{transform:translateY(20px);opacity:0}
    to{transform:translateY(0);opacity:1}
}

h2{
    margin:0 0 10px;
}

input{
    width:100%;
    padding:12px;
    margin:10px 0;
    border:none;
    border-radius:10px;
    background:rgba(255,255,255,0.08);
    color:white;
    outline:none;
}

button{
    width:100%;
    padding:12px;
    border:none;
    border-radius:10px;
    background:#00d4ff;
    font-weight:bold;
    cursor:pointer;
    transition:.3s;
}

button:hover{
    transform:scale(1.03);
    box-shadow:0 0 20px #00d4ff;
}

/* ERROR */
.error{
    background:#ff4d4d;
    padding:10px;
    border-radius:10px;
    margin-bottom:10px;
    font-size:14px;
}

/* LINK */
a{
    color:#00d4ff;
    text-decoration:none;
}

/* TOAST */
.toast{
    position:fixed;
    top:20px;
    right:20px;
    background:#00d4ff;
    color:#000;
    padding:12px 18px;
    border-radius:10px;
    font-weight:bold;
    animation:slide 3s forwards;
}

@keyframes slide{
    0%{transform:translateX(200px);opacity:0}
    10%{transform:translateX(0);opacity:1}
    90%{opacity:1}
    100%{opacity:0;transform:translateX(200px)}
}
</style>
</head>

<body>

<div class="box">
    <h2>Bem-vindo 👋</h2>
    <p>Faz login na tua conta</p>

    <?php if($error): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>

        <button name="login">Entrar</button>
    </form>

    <p style="margin-top:15px;">
        Não tens conta? <a href="register.php">Criar conta</a>
    </p>
</div>

</body>
</html>