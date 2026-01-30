<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: /projeto_rastreabilidade/login.php");
    exit();
}

include '../includes/config.php';
header('Content-Type: application/json');

try {
    $stmt = $conn->prepare("
        SELECT 
            DATE_FORMAT(data, '%m/%Y') as mes,
            AVG(peso) as media_peso,
            COUNT(*) as total_pesagens
        FROM pesagem
        GROUP BY DATE_FORMAT(data, '%Y-%m')
        ORDER BY mes DESC
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
