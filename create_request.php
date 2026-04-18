<?php
session_start();
$pdo = new PDO("mysql:host=localhost;dbname=meusite;charset=utf8mb4","root","");

$user_id = $_SESSION['user_id'] ?? null;

$order_id = $_POST['order_id'];
$name = $_POST['name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$description = $_POST['description'];

/* guardar pedido */
$pdo->prepare("
    INSERT INTO project_requests (user_id, order_id, name, email, phone, description)
    VALUES (?,?,?,?,?,?)
")->execute([$user_id, $order_id, $name, $email, $phone, $description]);

/* criar projeto automático */
$projectDesc =
"Pedido do cliente:\n\n".
"Nome: $name\nEmail: $email\nTelefone: $phone\n\nDescrição:\n$description";

$pdo->prepare("
    INSERT INTO projects (title, description, status, tracking_status, current_stage, user_id, order_id, priority, phone, address, created_at)
    VALUES (?,?,?,?,?,?,?,?,?,?,NOW())
")->execute([
    "Projeto Cliente #".$order_id,
    $projectDesc,
    "pendente",
    "encomendado",
    1,
    $user_id,
    $order_id,
    "normal",
    $phone,
    null
]);

/* LOG */
$pdo->prepare("
    INSERT INTO logs (user_id, action, created_at)
    VALUES (?,?,NOW())
")->execute([
    $user_id,
    "Cliente enviou briefing do pedido #$order_id"
]);

/* NOTIFICA ADMIN */
$pdo->prepare("
    INSERT INTO notifications (user_id, message, seen, created_at)
    VALUES (NULL,?,0,NOW())
")->execute([
    "📩 Novo pedido detalhado recebido (#$order_id)"
]);

echo "Pedido enviado com sucesso!";
?>
<style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;700&display=swap');
        
        body {
            margin: 0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #070a12;
            font-family: 'Inter', sans-serif;
            color: white;
            overflow: hidden;
        }

        /* Fundo Animado */
        .bg-glow {
            position: absolute;
            width: 300px;
            height: 300px;
            background: #00e5ff;
            filter: blur(150px);
            opacity: 0.2;
            animation: move 10s infinite alternate;
            z-index: -1;
        }

        @keyframes move {
            from { transform: translate(-50%, -50%); }
            to { transform: translate(50%, 50%); }
        }

        /* Cartão Glassmorphism */
        .success-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 40px 60px;
            border-radius: 24px;
            text-align: center;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
            transform: translateY(30px);
            opacity: 0;
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes slideUp {
            to { transform: translateY(0); opacity: 1; }
        }

        .icon-box {
            width: 80px;
            height: 80px;
            background: rgba(0, 229, 255, 0.1);
            border: 2px solid #00e5ff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 0 20px rgba(0, 229, 255, 0.4);
        }

        .icon-box svg {
            width: 40px;
            height: 40px;
            stroke: #00e5ff;
            stroke-width: 3;
            fill: none;
            stroke-dasharray: 100;
            stroke-dashoffset: 100;
            animation: draw 0.8s 0.3s forwards;
        }

        @keyframes draw {
            to { stroke-dashoffset: 0; }
        }

        h1 {
            font-size: 24px;
            margin: 0;
            letter-spacing: 1px;
            text-shadow: 0 0 10px rgba(0, 229, 255, 0.5);
        }

        p {
            color: #94a3b8;
            margin: 10px 0 30px;
            font-size: 14px;
        }

        .btn-back {
            text-decoration: none;
            color: #000;
            background: #00e5ff;
            padding: 12px 30px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 13px;
            text-transform: uppercase;
            transition: 0.3s;
            display: inline-block;
            box-shadow: 0 0 15px rgba(0, 229, 255, 0.3);
        }

        .btn-back:hover {
            transform: scale(1.05);
            box-shadow: 0 0 25px rgba(0, 229, 255, 0.6);
        }
    </style>
</head>
<body>
    <div class="bg-glow"></div>
    
    <div class="success-card">
        <div class="icon-box">
            <svg viewBox="0 0 24 24">
                <path d="M20 6L9 17L4 12" />
            </svg>
        </div>
        <h1>Pedido Transmitido</h1>
        <p>O briefing do projeto #<?= htmlspecialchars($order_id) ?> foi processado.</p>
        
        <a href="dashboard.php" class="btn-back">Ir para Projetos</a>
    </div>

    <script>
        // Redirecionamento automático após 5 segundos (opcional)
        // setTimeout(() => { window.location.href = 'dashboard.php'; }, 5000);
    </script>
</body>
</html>