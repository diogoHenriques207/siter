<?php
session_start();

$pdo = new PDO("mysql:host=localhost;dbname=meusite;charset=utf8mb4","root","");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$error = "";
$success = "";

if(isset($_POST['register'])){

    $email = trim($_POST['email']);
    $pass = $_POST['password'];
    $confirm = $_POST['confirm'];

    // 🔐 validações base
    if(strlen($pass) < 6){
        $error = "Password demasiado fraca (min 6 caracteres)";
    }
    elseif($pass !== $confirm){
        $error = "As passwords não coincidem";
    }
    else {

        // email existe?
        $check = $pdo->prepare("SELECT id FROM users WHERE email=?");
        $check->execute([$email]);

        if($check->fetch()){
            $error = "Este email já está registado";
        } else {

            $hash = password_hash($pass, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                INSERT INTO users(email,password,role,status)
                VALUES(?,?,'user','active')
            ");

            $stmt->execute([$email,$hash]);

            header("Location: login.php?registered=1");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title>Registo</title>

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
    width:400px;
    padding:40px;
    background:rgba(75, 29, 29, 0.06);
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
    margin-bottom:10px;
}

input{
    width:100%;
    padding:12px;
    margin:8px 0;
    border:none;
    border-radius:10px;
    background:rgba(255,255,255,0.08);
    color:white;
    outline:none;
}

/* PASSWORD STRENGTH BAR */
.bar{
    height:5px;
    background:#222;
    border-radius:10px;
    overflow:hidden;
    margin-bottom:10px;
}

.bar div{
    height:100%;
    width:0%;
    background:red;
    transition:.3s;
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

.error{
    background:#ff4d4d;
    padding:10px;
    border-radius:10px;
    margin-bottom:10px;
}

a{
    color:#00d4ff;
    text-decoration:none;
}
</style>
</head>

<body>

<div class="box">
    <h2>Criar Conta 🚀</h2>
    <p>Regista-te na plataforma</p>

    <?php if($error): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">

        <input type="email" name="email" placeholder="Email" required>

        <input type="password" id="pass" name="password" placeholder="Password" required>

        <div class="bar"><div id="strength"></div></div>

        <input type="password" name="confirm" placeholder="Confirmar password" required>

        <button name="register">Criar conta</button>
    </form>

    <p style="margin-top:15px;">
        Já tens conta? <a href="login.php">Login</a>
    </p>
</div>

<script>
const pass = document.getElementById("pass");
const bar = document.getElementById("strength");

pass.addEventListener("input", function(){

    let val = pass.value;
    let strength = 0;

    if(val.length > 5) strength += 25;
    if(val.match(/[A-Z]/)) strength += 25;
    if(val.match(/[0-9]/)) strength += 25;
    if(val.match(/[^a-zA-Z0-9]/)) strength += 25;

    bar.style.width = strength + "%";

    if(strength < 50){
        bar.style.background = "red";
    } else if(strength < 75){
        bar.style.background = "orange";
    } else {
        bar.style.background = "lime";
    }

});
</script>

</body>
</html>