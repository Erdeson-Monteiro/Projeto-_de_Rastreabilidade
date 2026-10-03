<?php
session_start();
require_once __DIR__ . '/includes/config.php';

// Se já estiver logado, redireciona para o dashboard
if (!empty($_SESSION['logged_in'])) {
    header("Location: pages/index.php");
    exit();
}

$erro = '';
$email = '';

// Processa o formulário de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['password'] ?? '';

    // Busca o usuário pelo e-mail e confere a senha (guardada com password_hash)
    $stmt = $conn->prepare("SELECT id, nome, senha FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($senha, $usuario['senha'])) {
        session_regenerate_id(true);
        $_SESSION['logged_in'] = true;
        $_SESSION['user_id'] = $usuario['id'];
        $_SESSION['user_nome'] = $usuario['nome'];
        header("Location: pages/index.php");
        exit();
    }

    $erro = 'E-mail ou senha incorretos.';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Rastreamento Bovino</title>
    <link rel="icon" href="assets/img/favicon.svg" type="image/svg+xml">
    <!-- Fonte Poppins -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Poppins', sans-serif;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 30px 24px;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header i {
            font-size: 3rem;
            color: #34699A;
            margin-bottom: 10px;
        }
        .login-header h2 {
            font-weight: 600;
        }
        .btn-primary {
            background-color: #34699A;
            border-color: #34699A;
            border-radius: 50px;
            padding: 10px 20px;
        }
        .btn-primary:hover,
        .btn-primary:focus {
            background-color: #285071;
            border-color: #285071;
        }
        .demo-info {
            font-size: 0.85rem;
        }
        @media (max-width: 576px) {
            .login-container {
                margin: 40px auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-header">
                <i class="bi bi-patch-check-fill"></i>
                <h2>Rastreamento Bovino</h2>
                <p class="text-muted mb-0">IFMA - Sistema de Rastreabilidade</p>
            </div>

            <?php if ($erro): ?>
                <div class="alert alert-danger text-center py-2"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="email" class="form-control" id="email" name="email"
                           value="<?= htmlspecialchars($email) ?>" autocomplete="username" required autofocus>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Senha</label>
                    <input type="password" class="form-control" id="password" name="password"
                           autocomplete="current-password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-box-arrow-in-right"></i> Entrar
                </button>
            </form>

            <p class="demo-info text-muted text-center mt-4 mb-0">
                Acesso de demonstração: <strong>usuario@exemplo.com</strong> / <strong>senha123</strong>
            </p>
        </div>
    </div>
</body>
</html>
