<?php
// Força o tipo de retorno para JSON
header('Content-Type: application/json');

// Configurações de CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Função para log
function logDebug($message) {
    $logFile = __DIR__ . '/read_log.txt';
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
    logDebug("Iniciando API de leitura");
    logDebug("Método da requisição: " . $_SERVER['REQUEST_METHOD']);
    logDebug("Dados POST: " . json_encode($_POST));
    logDebug("Dados GET: " . json_encode($_GET));
    logDebug("Input: " . file_get_contents('php://input'));

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

    // Pega o tag_id de todas as fontes possíveis
    $tag_id = '';
    $input = file_get_contents('php://input');
    
    // Tenta pegar do POST primeiro
    if (isset($_POST['tag_id'])) {
        $tag_id = trim($_POST['tag_id']);
        logDebug("Tag ID obtido do POST: " . $tag_id);
    }
    // Se não tiver no POST, tenta do corpo da requisição
    else if (!empty($input)) {
        // Tenta decodificar como JSON
        $data = json_decode($input, true);
        if (json_last_error() === JSON_ERROR_NONE && isset($data['tag_id'])) {
            $tag_id = trim($data['tag_id']);
            logDebug("Tag ID obtido do JSON: " . $tag_id);
        } else {
            // Se não for JSON, tenta como string simples
            parse_str($input, $params);
            if (isset($params['tag_id'])) {
                $tag_id = trim($params['tag_id']);
                logDebug("Tag ID obtido do parse_str: " . $tag_id);
            } else {
                // Se ainda não tiver tag_id, tenta pegar diretamente do input
                $tag_id = trim($input);
                logDebug("Tag ID obtido do input direto: " . $tag_id);
            }
        }
    }
    // Tenta do GET como último recurso
    else if (isset($_GET['tag_id'])) {
        $tag_id = trim($_GET['tag_id']);
        logDebug("Tag ID obtido do GET: " . $tag_id);
    }

    logDebug("Tag ID final: " . ($tag_id ? $tag_id : 'null'));

    // Se não tem tag_id, busca a última leitura registrada
    if (empty($tag_id)) {
        try {
            // Busca a última leitura registrada
            $stmt = $conn->prepare("
                SELECT h.*, a.* 
                FROM historico_leitura h
                LEFT JOIN animais a ON h.tag_id = a.tag_id
                ORDER BY h.data_leitura DESC
                LIMIT 1
            ");
            $stmt->execute();
            $ultimaLeitura = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($ultimaLeitura) {
                $tag_id = $ultimaLeitura['tag_id'];
                logDebug("Última leitura encontrada: " . $tag_id);
                
                // Busca o histórico completo para este tag_id
                $stmt = $conn->prepare("
                    SELECT h.*, a.identificador 
                    FROM historico_leitura h
                    LEFT JOIN animais a ON h.tag_id = a.tag_id
                    WHERE h.tag_id = ?
                    ORDER BY h.data_leitura DESC
                    LIMIT 10
                ");
                $stmt->execute([$tag_id]);
                $historico = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $response = [
                    'success' => true,
                    'message' => 'Última leitura encontrada',
                    'tag_id' => $tag_id,
                    'animal' => $ultimaLeitura,
                    'historico' => $historico
                ];
            } else {
                $response = [
                    'success' => true,
                    'message' => 'Aguardando leitura...',
                    'tag_id' => null,
                    'animal' => null,
                    'historico' => []
                ];
            }
        } catch (Exception $e) {
            logDebug("Erro ao buscar última leitura: " . $e->getMessage());
            $response = [
                'success' => true,
                'message' => 'Aguardando leitura...',
                'tag_id' => null,
                'animal' => null,
                'historico' => []
            ];
        }
        
        logDebug("Retornando resposta: " . json_encode($response));
        echo json_encode($response);
        exit;
    }

    // Inicia uma transação
    $conn->beginTransaction();
    logDebug("Transação iniciada");

    try {
        // Busca o animal
        $stmt = $conn->prepare("SELECT * FROM animais WHERE tag_id = ?");
    $stmt->execute([$tag_id]);
    $animal = $stmt->fetch(PDO::FETCH_ASSOC);
        logDebug("Animal buscado: " . ($animal ? "encontrado" : "não encontrado"));

        // Se o animal não existe, retorna erro
        if (!$animal) {
            throw new Exception('Tag RFID não cadastrada para nenhum animal');
        }

        // Registra a leitura
        $stmt = $conn->prepare("INSERT INTO historico_leitura (tag_id, data_leitura) VALUES (?, NOW())");
        $stmt->execute([$tag_id]);
        logDebug("Leitura registrada com sucesso");

        // Busca o histórico de leituras
        $stmt = $conn->prepare("
            SELECT h.*, a.identificador 
            FROM historico_leitura h
            LEFT JOIN animais a ON h.tag_id = a.tag_id
            WHERE h.tag_id = ?
            ORDER BY h.data_leitura DESC
            LIMIT 10
        ");
        $stmt->execute([$tag_id]);
        $historico = $stmt->fetchAll(PDO::FETCH_ASSOC);
        logDebug("Histórico de leituras obtido: " . count($historico) . " registros");

        // Confirma a transação
        $conn->commit();
        logDebug("Transação confirmada");

        // Retorna sucesso
        $response = [
        'success' => true,
            'message' => 'Leitura registrada com sucesso',
        'tag_id' => $tag_id,
            'animal' => $animal,
            'historico' => $historico
        ];

        logDebug("Resposta: " . json_encode($response));
        echo json_encode($response);

    } catch (Exception $e) {
        // Em caso de erro, reverte a transação
        $conn->rollBack();
        logDebug("ERRO na transação: " . $e->getMessage());
        throw $e;
    }

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