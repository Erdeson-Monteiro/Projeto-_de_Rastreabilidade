<?php
// Forçar retorno como JSON
header('Content-Type: application/json');

// Função para log
function logDebug($message) {
    $logFile = __DIR__ . '/test_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message\n";
    
    // Verifica se o diretório existe
    if (!is_dir(__DIR__)) {
        mkdir(__DIR__, 0777, true);
    }
    
    // Tenta escrever no arquivo
    if (file_put_contents($logFile, $logMessage, FILE_APPEND) === false) {
        // Se não conseguir escrever, tenta criar o arquivo
        file_put_contents($logFile, $logMessage);
    }
}

try {
    // Log inicial
    logDebug("Iniciando teste de conexão");

    // Verifica se o arquivo de configuração existe
    $config_file = __DIR__ . '/../../includes/config.php';
    if (!file_exists($config_file)) {
        throw new Exception("Arquivo de configuração não encontrado: $config_file");
    }
    logDebug("Arquivo de configuração encontrado: $config_file");

    // Inclui o arquivo de configuração
    require_once $config_file;
    logDebug("Arquivo de configuração carregado");

    // Verifica se a conexão com o banco foi estabelecida
    if (!isset($conn) || !($conn instanceof PDO)) {
        throw new Exception('Conexão com o banco de dados não estabelecida');
    }
    logDebug("Conexão com banco de dados verificada");

    // Testa a conexão
    try {
        $conn->query("SELECT 1");
        logDebug("Conexão com banco de dados testada com sucesso");
    } catch (PDOException $e) {
        logDebug("ERRO na conexão com banco de dados: " . $e->getMessage());
        throw new Exception("Erro ao conectar com o banco de dados: " . $e->getMessage());
    }

    // Testa a leitura de dados
    try {
        $stmt = $conn->query("SELECT * FROM animais LIMIT 1");
        $animal = $stmt->fetch(PDO::FETCH_ASSOC);
        logDebug("Dados lidos com sucesso: " . json_encode($animal));
    } catch (PDOException $e) {
        logDebug("ERRO ao ler dados: " . $e->getMessage());
        throw new Exception("Erro ao ler dados: " . $e->getMessage());
    }

    // Retorna sucesso
    echo json_encode([
        'success' => true,
        'message' => 'Conexão com banco de dados estabelecida com sucesso',
        'data' => $animal
    ]);

} catch (Exception $e) {
    // Log do erro
    logDebug("ERRO: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_details' => [
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}
?> 