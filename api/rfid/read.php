<?php
// Força o tipo de retorno para JSON
header('Content-Type: application/json');

// Configurações de CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Mude para true para registrar cada passo em read_log.txt (depuração).
// Desligado, só os erros são registrados: a tela de monitoramento consulta
// esta API a cada 2 segundos e o log crescia vários MB.
define('READ_DEBUG', false);

// Função para log
function logDebug($message) {
    if (!READ_DEBUG && stripos($message, 'erro') === false) {
        return;
    }
    $logFile = __DIR__ . '/read_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message\n";
    
    // Grava no arquivo de log; se não houver permissão, ignora em silêncio
    // (um aviso do PHP aqui quebraria o JSON da resposta)
    @file_put_contents($logFile, $logMessage, FILE_APPEND);
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
        throw new Exception("Arquivo de configuração não encontrado.");
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
        throw new Exception("Erro ao conectar com o banco de dados.");
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

    // Se não tem tag_id, a tela de monitoramento está pedindo o estado atual:
    // última leitura, histórico recente (de todas as tags) e estatísticas do dia
    if (empty($tag_id)) {
        try {
            // Última leitura registrada, com os dados do animal (se a tag estiver cadastrada)
            $stmt = $conn->query("
                SELECT a.*, h.id AS leitura_id, h.tag_id, h.data_leitura
                FROM historico_leitura h
                LEFT JOIN animais a ON h.tag_id = a.tag_id
                ORDER BY h.data_leitura DESC, h.id DESC
                LIMIT 1
            ");
            $ultimaLeitura = $stmt->fetch(PDO::FETCH_ASSOC);

            // Últimas 10 leituras de qualquer tag
            $stmt = $conn->query("
                SELECT h.id, h.tag_id, h.data_leitura, a.identificador
                FROM historico_leitura h
                LEFT JOIN animais a ON h.tag_id = a.tag_id
                ORDER BY h.data_leitura DESC, h.id DESC
                LIMIT 10
            ");
            $historico = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Estatísticas de hoje: total, válidas (tag cadastrada) e inválidas
            $stmt = $conn->query("
                SELECT COUNT(*) AS hoje,
                       COALESCE(SUM(a.id IS NOT NULL), 0) AS validas,
                       COALESCE(SUM(a.id IS NULL), 0) AS invalidas
                FROM historico_leitura h
                LEFT JOIN animais a ON h.tag_id = a.tag_id
                WHERE DATE(h.data_leitura) = CURDATE()
            ");
            $estatisticas = array_map('intval', $stmt->fetch(PDO::FETCH_ASSOC));

            $response = [
                'success' => true,
                'message' => $ultimaLeitura ? 'Última leitura encontrada' : 'Aguardando leitura...',
                'tag_id' => $ultimaLeitura['tag_id'] ?? null,
                'leitura_id' => $ultimaLeitura['leitura_id'] ?? null,
                'data_leitura' => $ultimaLeitura['data_leitura'] ?? null,
                // Tag sem animal cadastrado: devolve só o tag_id, sem animal
                'animal' => !empty($ultimaLeitura['identificador']) ? $ultimaLeitura : null,
                'historico' => $historico,
                'estatisticas' => $estatisticas
            ];
        } catch (Exception $e) {
            logDebug("Erro ao buscar última leitura: " . $e->getMessage());
            $response = [
                'success' => false,
                'message' => 'Erro ao consultar as leituras.',
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
        'message' => $e->getMessage()
    ]);
}