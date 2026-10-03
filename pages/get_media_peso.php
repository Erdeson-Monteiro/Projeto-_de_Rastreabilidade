<?php
// Média de peso por mês, usada no gráfico do Dashboard (retorna JSON)
session_start();
header('Content-Type: application/json');

// Sem login: responde 401 em JSON (é uma chamada via fetch, não uma página)
if (empty($_SESSION['logged_in'])) {
    http_response_code(401);
    echo json_encode([]);
    exit();
}

require_once '../includes/config.php';

try {
    $stmt = $conn->prepare("
        SELECT 
            DATE_FORMAT(data, '%m/%Y') as mes,
            AVG(peso) as media_peso,
            COUNT(*) as total_pesagens
        FROM pesagem
        GROUP BY DATE_FORMAT(data, '%Y-%m')
        ORDER BY DATE_FORMAT(data, '%Y-%m') DESC
        LIMIT 12
    ");
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $dados = [];
    foreach ($resultados as $row) {
        $dados[] = [
            'mes' => $row['mes'],
            'media_peso' => round(floatval($row['media_peso']), 2),
            'total_pesagens' => intval($row['total_pesagens'])
        ];
    }

    echo json_encode(array_reverse($dados)); // Para mostrar do mais antigo ao mais recente

} catch (PDOException $e) {
    echo json_encode([]);
}
?>
