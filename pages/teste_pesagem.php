<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: /projeto_rastreabilidade/login.php");
    exit();
}

include '../includes/header.php';
include '../includes/config.php';

// Testa a inserção de dados
try {
    // Primeiro, verifica se existe algum animal
    $stmt = $conn->prepare("SELECT id FROM animais LIMIT 1");
    $stmt->execute();
    $animal = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$animal) {
        echo "<div class='alert alert-warning'>Nenhum animal encontrado no banco de dados.</div>";
    } else {
        // Tenta inserir uma pesagem de teste
        $stmt = $conn->prepare("INSERT INTO pesagem (animal_id, peso, data) VALUES (?, ?, NOW())");
        $stmt->execute([$animal['id'], 500.00]);
        
        if ($stmt->rowCount() > 0) {
            echo "<div class='alert alert-success'>Dados de teste inseridos com sucesso!</div>";
        } else {
            echo "<div class='alert alert-danger'>Erro ao inserir dados de teste.</div>";
        }
    }
    
    // Mostra os dados atuais da tabela
    $stmt = $conn->prepare("SELECT p.*, a.identificador FROM pesagem p JOIN animais a ON p.animal_id = a.id ORDER BY p.data DESC");
    $stmt->execute();
    $pesagens = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Dados atuais da tabela pesagem:</h3>";
    echo "<table class='table table-striped'>";
    echo "<thead><tr><th>ID</th><th>Animal</th><th>Peso</th><th>Data</th></tr></thead>";
    echo "<tbody>";
    foreach ($pesagens as $pesagem) {
        echo "<tr>";
        echo "<td>" . $pesagem['id'] . "</td>";
        echo "<td>" . $pesagem['identificador'] . "</td>";
        echo "<td>" . $pesagem['peso'] . " kg</td>";
        echo "<td>" . $pesagem['data'] . "</td>";
        echo "</tr>";
    }
    echo "</tbody></table>";
    
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Erro: " . $e->getMessage() . "</div>";
}
?>

<?php include '../includes/footer.php'; ?> 