<?php
// Forçar retorno como JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');

// Função para registrar logs
function logMessage($message) {
    $logFile = __DIR__ . '/esp32_log.txt';
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
    logMessage("Iniciando API ESP32");
    
    // Verifica se o arquivo de configuração existe
    $config_file = __DIR__ . '/../../includes/config.php';
    if (!file_exists($config_file)) {
        throw new Exception("Arquivo de configuração não encontrado: $config_file");
    }
    logMessage("Arquivo de configuração encontrado: $config_file");

    // Inclui o arquivo de configuração
    require_once $config_file;
    logMessage("Arquivo de configuração carregado");

    // Obtém o método da requisição
    $method = $_SERVER['REQUEST_METHOD'];
    logMessage("Método da requisição: $method");

    // Obtém os dados da requisição
    $input = file_get_contents('php://input');
    $postData = $_POST;
    $getData = $_GET;
    
    logMessage("Dados POST: " . print_r($postData, true));
    logMessage("Dados GET: " . print_r($getData, true));
    logMessage("Input bruto: " . $input);

    // Tenta obter o tag_id de diferentes fontes
    $tag_id = null;
    
    // 1. Tenta obter do POST (form-urlencoded)
    if (isset($postData['tag_id']) && !empty($postData['tag_id'])) {
        $tag_id = $postData['tag_id'];
        logMessage("Tag ID obtido do POST (form-urlencoded): $tag_id");
    }
    // 2. Tenta obter do JSON
    elseif (!empty($input)) {
        $jsonData = json_decode($input, true);
        if (json_last_error() === JSON_ERROR_NONE && isset($jsonData['tag_id'])) {
            $tag_id = $jsonData['tag_id'];
            logMessage("Tag ID obtido do JSON: $tag_id");
        } else {
            logMessage("Erro ao decodificar JSON: " . json_last_error_msg());
        }
    }
    // 3. Tenta obter do GET
    elseif (isset($getData['tag_id']) && !empty($getData['tag_id'])) {
        $tag_id = $getData['tag_id'];
        logMessage("Tag ID obtido do GET: $tag_id");
    }
    // 4. Tenta obter do input bruto (caso seja enviado diretamente)
    elseif (!empty($input) && strpos($input, '=') === false) {
        $tag_id = trim($input);
        logMessage("Tag ID obtido do input bruto: $tag_id");
    }

    // Se não encontrou o tag_id em nenhuma fonte
    if (empty($tag_id)) {
        logMessage("Nenhum tag_id encontrado na requisição");
        echo json_encode([
            'success' => true,
            'message' => 'Aguardando leitura...',
            'tag_id' => null,
            'animal' => null,
            'historico' => []
        ]);
        exit;
    }

    // Conecta ao banco de dados
    try {
        $conn = new PDO("mysql:host=$servername;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        logMessage("Conexão com banco de dados estabelecida");
    } catch (PDOException $e) {
        logMessage("Erro na conexão com banco de dados: " . $e->getMessage());
        throw new Exception("Erro ao conectar com o banco de dados: " . $e->getMessage());
    }

    // Busca informações do animal
    try {
        $stmt = $conn->prepare("
            SELECT a.*, h.data_leitura 
            FROM animais a 
            LEFT JOIN historico_leitura h ON a.tag_id = h.tag_id 
            WHERE a.tag_id = :tag_id 
            ORDER BY h.data_leitura DESC 
            LIMIT 1
        ");
        $stmt->execute(['tag_id' => $tag_id]);
        $animal = $stmt->fetch(PDO::FETCH_ASSOC);
        logMessage("Animal buscado: " . ($animal ? "encontrado" : "não encontrado"));
    } catch (PDOException $e) {
        logMessage("Erro ao buscar animal: " . $e->getMessage());
        throw new Exception("Erro ao buscar informações do animal: " . $e->getMessage());
    }

    // Busca histórico de leituras
    try {
        $stmt = $conn->prepare("
            SELECT h.*, a.identificador 
            FROM historico_leitura h
            LEFT JOIN animais a ON h.tag_id = a.tag_id
            WHERE h.tag_id = :tag_id 
            ORDER BY h.data_leitura DESC 
            LIMIT 10
        ");
        $stmt->execute(['tag_id' => $tag_id]);
        $historico = $stmt->fetchAll(PDO::FETCH_ASSOC);
        logMessage("Histórico de leituras obtido: " . count($historico) . " registros");
    } catch (PDOException $e) {
        logMessage("Erro ao buscar histórico: " . $e->getMessage());
        throw new Exception("Erro ao buscar histórico de leituras: " . $e->getMessage());
    }

    // Registra a nova leitura
    try {
        $stmt = $conn->prepare("
            INSERT INTO historico_leitura (tag_id, data_leitura) 
            VALUES (:tag_id, NOW())
        ");
        $stmt->execute(['tag_id' => $tag_id]);
        logMessage("Nova leitura registrada com sucesso");
    } catch (PDOException $e) {
        logMessage("Erro ao registrar leitura: " . $e->getMessage());
        throw new Exception("Erro ao registrar nova leitura: " . $e->getMessage());
    }

    // Prepara a resposta
    $response = [
        'success' => true,
        'message' => 'Leitura registrada com sucesso',
        'tag_id' => $tag_id,
        'animal' => $animal ? [
            'identificador' => $animal['identificador'],
            'raca' => $animal['raca'],
            'peso' => floatval($animal['peso'])
        ] : null,
        'historico' => array_map(function($item) {
            return [
                'data_leitura' => $item['data_leitura'],
                'tag_id' => $item['tag_id'],
                'identificador' => $item['identificador']
            ];
        }, $historico)
    ];

    logMessage("Resposta enviada: " . json_encode($response));
    echo json_encode($response);

} catch (Exception $e) {
    logMessage("Erro: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao processar a requisição: ' . $e->getMessage()
    ]);
} 