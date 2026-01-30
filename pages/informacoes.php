<?php
// informacoes.php
include '../includes/header.php';
include '../includes/config.php';

// Verifica se um ID foi passado
$id_animal = isset($_GET['id']) ? (int) $_GET['id'] : null;
?>

<!-- Estilos omitidos para foco na lógica -->

<?php
// Se não há ID, exibe a listagem paginada
if (!$id_animal) {
    // Definir paginação
    $limit = 5;
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    if ($page < 1) $page = 1;
    $start = ($page - 1) * $limit;

    // Buscar total de animais
    $totalQuery = $conn->query("SELECT COUNT(*) as total FROM animais");
    $totalRow = $totalQuery->fetch(PDO::FETCH_ASSOC);
    $total = $totalRow['total'];

    // Quantidade de páginas
    $pages = ($total > 0) ? ceil($total / $limit) : 1;

    // Consulta paginada (usando bindValue em vez de bindParam para LIMIT)
    $stmt = $conn->prepare("
        SELECT id, data_nascimento, genero, raca, pai_id, mae_id, peso
        FROM animais
        ORDER BY id DESC
        LIMIT :start, :limit
    ");
    $stmt->bindValue(':start', $start, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <div class="container my-5">
        <h2 class="mb-4 text-center">Lista de Animais Cadastrados</h2>
        <?php if ($total > 0): ?>
            <div class="table-responsive">
                <table class="table table-custom table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Data Nascimento</th>
                            <th>Sexo</th>
                            <th>Raça</th>
                            <th>Pai (ID)</th>
                            <th>Mãe (ID)</th>
                            <th>Peso (Kg)</th>
                            <th class="text-center">Detalhes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($result as $row): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= date('d/m/Y', strtotime($row['data_nascimento'])) ?></td>
                                <td><?= ($row['genero'] === 'M') ? 'Macho' : 'Fêmea' ?></td>
                                <td><?= $row['raca'] ?></td>
                                <td><?= $row['pai_id'] ?: '---' ?></td>
                                <td><?= $row['mae_id'] ?: '---' ?></td>
                                <td><?= $row['peso'] ?></td>
                                <td class="text-center">
                                    <a href="?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> Ver
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginação omitida por clareza -->
        <?php else: ?>
            <div class="alert alert-info text-center">Nenhum animal cadastrado ainda.</div>
        <?php endif; ?>
    </div>

    <?php
    include '../includes/footer.php';
    exit;
}

// Se há ID, exibe detalhes de um animal específico
$stmt = $conn->prepare("SELECT * FROM animais WHERE id = :id");
$stmt->bindParam(':id', $id_animal, PDO::PARAM_INT);
$stmt->execute();
$animal = $stmt->fetch(PDO::FETCH_ASSOC);

// Se não encontrar o animal, exibe alerta
if (!$animal) {
    echo "<div class='container mt-5 alert alert-warning text-center'>Animal não encontrado.</div>";
    include '../includes/footer.php';
    exit;
}

// Consulta histórico de pesagem
$peso_stmt = $conn->prepare("SELECT data, peso FROM pesagem WHERE animal_id = :animal_id ORDER BY data DESC");
$peso_stmt->bindParam(':animal_id', $id_animal, PDO::PARAM_INT);
$peso_stmt->execute();
$peso_result = $peso_stmt->fetchAll(PDO::FETCH_ASSOC);

// Consulta histórico de vacinação
$vacina_stmt = $conn->prepare("SELECT data, vacina FROM vacinas WHERE animal_id = :animal_id ORDER BY data DESC");
$vacina_stmt->bindParam(':animal_id', $id_animal, PDO::PARAM_INT);
$vacina_stmt->execute();
$vacina_result = $vacina_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Detalhes do animal -->
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-custom">
                <div class="card-header card-header-custom text-center">
                    <h2 class="mb-0">Informações do Animal</h2>
                </div>
                <div class="card-body">
                    <h4 class="text-primary">Dados Cadastrais</h4>
                    <p><strong>ID:</strong> <?= $animal['id'] ?></p>
                    <p><strong>Nascimento:</strong> <?= date('d/m/Y', strtotime($animal['data_nascimento'])) ?></p>
                    <p><strong>Sexo:</strong> <?= ($animal['genero'] == 'M') ? 'Macho' : 'Fêmea' ?></p>
                    <p><strong>Raça:</strong> <?= $animal['raca'] ?></p>
                    <p><strong>Pai:</strong> <?= $animal['pai_id'] ?: 'Desconhecido' ?></p>
                    <p><strong>Mãe:</strong> <?= $animal['mae_id'] ?: 'Desconhecida' ?></p>
                    <p><strong>Peso (Kg):</strong> <?= $animal['peso'] ?></p>

                    <hr>
                    <h4 class="text-primary">Histórico de Pesagem</h4>
                    <?php if (count($peso_result) > 0): ?>
                        <ul class="list-group">
                            <?php foreach ($peso_result as $peso): ?>
                                <li class="list-group-item">
                                    <?= date('d/m/Y', strtotime($peso['data'])) ?> - <?= $peso['peso'] ?> kg
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="text-muted">Nenhum registro de pesagem encontrado.</div>
                    <?php endif; ?>

                    <hr>
                    <h4 class="text-primary">Registro de Vacinação</h4>
                    <?php if (count($vacina_result) > 0): ?>
                        <ul class="list-group">
                            <?php foreach ($vacina_result as $vacina): ?>
                                <li class="list-group-item">
                                    <?= date('d/m/Y', strtotime($vacina['data'])) ?> - <?= $vacina['vacina'] ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="text-muted">Nenhum registro de vacinação encontrado.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
