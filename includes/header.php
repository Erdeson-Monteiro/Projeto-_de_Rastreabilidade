<?php
// header.php
include __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rastreamento Bovino - IFMA</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Estilos customizados -->
    <link rel="stylesheet" href="/assets/css/styles.css">

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
            <a class="navbar-brand fw-bold" href="/projeto_rastreabilidade/pages/index.php">
                <i class="bi bi-patch-check-fill"></i> IFMA Bov
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">    
                    <!-- Dashboard -->
                    <li class="nav-item">
                        <a class="nav-link" href="/projeto_rastreabilidade/pages/index.php">
                            <i class="bi bi-house"></i> Dashboard
                        </a>
                    </li>
                    <!-- Animais (Dropdown) -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-hdd-stack"></i> Animais
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="/projeto_rastreabilidade/pages/cadastro.php">
                                    <i class="bi bi-plus-circle"></i> Cadastrar
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="/projeto_rastreabilidade/pages/informacoes.php">
                                    <i class="bi bi-info-circle"></i> Informações
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="/projeto_rastreabilidade/pages/atualizar.php">
                                    <i class="bi bi-pencil-square"></i> Atualizar
                                </a>
                            </li>
                        </ul>
                    </li>
                    <!-- Vacinas -->
                    <li class="nav-item">
                        <a class="nav-link" href="/projeto_rastreabilidade/pages/vacinas.php">
                            <i class="bi bi-eyedropper"></i> Vacinas
                        </a>
                    </li>
                    <!-- Pesagem -->
                    <li class="nav-item">
                        <a class="nav-link" href="/projeto_rastreabilidade/pages/pesagem.php">
                            <i class="bi bi-bar-chart"></i> Pesagem
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
