<?php
// config.php
// Fuso horário do Maranhão (UTC-3), usado nas datas e horários do sistema
date_default_timezone_set('America/Fortaleza');

// Configuração da conexão com o banco de dados MySQL
// Os valores padrão são os do XAMPP. No Docker, eles vêm das variáveis de ambiente.
$servername = getenv('DB_HOST') ?: "localhost";
$username = getenv('DB_USER') ?: "root";
$password = getenv('DB_PASSWORD') ?: "";
$dbname = getenv('DB_NAME') ?: "ifmabov";
$port = getenv('DB_PORT') ?: 3306;

try {
    // Criar conexão PDO
    $conn = new PDO(
        "mysql:host=$servername;port=$port;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => true
        ]
    );
    
    // Usa o mesmo fuso no MySQL, para NOW() registrar a hora local das leituras
    $conn->exec("SET time_zone = '-03:00'");

    // Testa a conexão
    $conn->query("SELECT 1");
} catch (PDOException $e) {
    // O detalhe vai para o log do servidor; na tela, só uma mensagem genérica
    error_log("Falha na conexão com o banco: " . $e->getMessage());
    die("Não foi possível conectar ao banco de dados. Verifique se o MySQL está rodando e as configurações em includes/config.php.");
}

$BASE_URL = "/projeto_rastreabilidade";
?>
