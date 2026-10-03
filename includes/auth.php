<?php
// auth.php
// Protege as páginas internas: sem login, o usuário volta para a tela de login.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['logged_in'])) {
    header("Location: ../login.php");
    exit();
}
