<?php
// header.php
require_once __DIR__ . '/config.php';

// Página atual, usada para destacar o item ativo do menu
$pagina_atual = basename($_SERVER['PHP_SELF']);
function menu_ativo($paginas) {
    global $pagina_atual;
    return in_array($pagina_atual, (array) $paginas) ? ' active' : '';
}

// Versão dos arquivos estáticos (data de modificação): o navegador baixa
// o CSS/JS novo assim que ele muda, sem precisar limpar o cache
function asset($caminho) {
    global $BASE_URL;
    $arquivo = __DIR__ . '/../' . $caminho;
    $versao = file_exists($arquivo) ? filemtime($arquivo) : 1;
    return $BASE_URL . '/' . $caminho . '?v=' . $versao;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rastreamento Bovino - IFMA</title>
    <link rel="icon" href="<?= $BASE_URL ?>/assets/img/favicon.svg" type="image/svg+xml">
    <!-- Fonte Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Font Awesome (ícones da tela de monitoramento RFID) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <!-- Estilos customizados -->
    <link rel="stylesheet" href="<?= asset('assets/css/styles.css') ?>">

    <style>
      /* Ajuste de cor para a navbar, menos saturada que o primary padrão */
      .navbar-custom {
        background-color: #34699A; /* tom de azul mais suave */
      }
      /* Espaçamento entre os itens de menu */
      .navbar-nav .nav-item {
        margin-right: 1rem;
      }
      .navbar-brand i {
        font-size: 1.5rem;
        margin-right: 0.5rem;
      }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container">
            <!-- Logo mais enxuta, somente ícone + texto pequeno -->
            <a class="navbar-brand fw-bold" href="<?= $BASE_URL ?>/pages/index.php">
                <i class="bi bi-patch-check-fill"></i> IFMA Bov
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Abrir menu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <!-- Dashboard -->
                    <li class="nav-item">
                        <a class="nav-link<?= menu_ativo('index.php') ?>" href="<?= $BASE_URL ?>/pages/index.php">
                            <i class="bi bi-house"></i> Dashboard
                        </a>
                    </li>
                    <!-- Animais (Dropdown) -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle<?= menu_ativo(['cadastro.php', 'informacoes.php', 'atualizar.php']) ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-hdd-stack"></i> Animais
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="<?= $BASE_URL ?>/pages/cadastro.php">
                                    <i class="bi bi-plus-circle"></i> Cadastrar
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?= $BASE_URL ?>/pages/informacoes.php">
                                    <i class="bi bi-info-circle"></i> Informações
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="<?= $BASE_URL ?>/pages/atualizar.php">
                                    <i class="bi bi-pencil-square"></i> Atualizar
                                </a>
                            </li>
                        </ul>
                    </li>
                    <!-- Vacinas -->
                    <li class="nav-item">
                        <a class="nav-link<?= menu_ativo('vacinas.php') ?>" href="<?= $BASE_URL ?>/pages/vacinas.php">
                            <i class="bi bi-eyedropper"></i> Vacinas
                        </a>
                    </li>
                    <!-- Pesagem -->
                    <li class="nav-item">
                        <a class="nav-link<?= menu_ativo('pesagem.php') ?>" href="<?= $BASE_URL ?>/pages/pesagem.php">
                            <i class="bi bi-bar-chart"></i> Pesagem
                        </a>
                    </li>
                    <!-- Monitoramento RFID -->
                    <li class="nav-item">
                        <a class="nav-link<?= menu_ativo('rfid_management.php') ?>" href="<?= $BASE_URL ?>/pages/rfid_management.php">
                            <i class="bi bi-rss"></i> RFID
                        </a>
                    </li>
                    <!-- Sair -->
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $BASE_URL ?>/logout.php" title="Sair do sistema">
                            <i class="bi bi-box-arrow-right"></i> Sair<?= !empty($_SESSION['user_nome']) ? ' (' . htmlspecialchars(strtok($_SESSION['user_nome'], ' ')) . ')' : '' ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <?php if (basename($_SERVER['PHP_SELF']) !== 'index.php'): ?>
    <!-- Botão Voltar: retorna à página anterior ou, se não houver, ao Dashboard -->
    <div class="back-bar">
        <a href="<?= $BASE_URL ?>/pages/index.php" class="btn btn-outline-secondary btn-sm"
           onclick="if (history.length > 1 && document.referrer.indexOf(location.host) !== -1) { history.back(); return false; }">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>
    <?php endif; ?>
