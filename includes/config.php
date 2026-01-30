<?php
// config.php
// Configuração da conexão com o banco de dados MySQL
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ifmabov";
$port = 3306;

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
    
    // Testa a conexão
    $conn->query("SELECT 1");
} catch (PDOException $e) {
    die("Falha na conexão: " . $e->getMessage());
}

$BASE_URL = "/projeto_rastreabilidade";
?>
