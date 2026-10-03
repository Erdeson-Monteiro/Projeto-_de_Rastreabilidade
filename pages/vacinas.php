<?php
// Só usuários logados acessam esta página
require_once '../includes/auth.php';

require_once '../includes/header.php';
require_once '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $identificador = trim($_POST['identificador']);
        $vacina = $_POST['vacina'];
        $data = $_POST['data'];
        
        // Primeiro, verifica se o animal existe
        $stmt = $conn->prepare("SELECT id FROM animais WHERE identificador = ?");
        $stmt->execute([$identificador]);
        $animal = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$animal) {
            echo "<div class='alert alert-danger text-center mt-3'>Animal não encontrado!</div>";
        } else {
            // Insere a vacinação
            $stmt = $conn->prepare("INSERT INTO vacinas (animal_id, vacina, data) VALUES (?, ?, ?)");
            $stmt->execute([$animal['id'], $vacina, $data]);
            
            if ($stmt->rowCount() > 0) {
                echo "<div class='alert alert-success text-center mt-3'>Vacinação registrada com sucesso!</div>";
            } else {
                echo "<div class='alert alert-danger text-center mt-3'>Erro ao registrar a vacinação.</div>";
            }
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo "<div class='alert alert-danger text-center mt-3'>Não foi possível salvar. Tente novamente.</div>";
    }
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-custom">
                <div class="card-header card-header-custom text-center">
                    <h2 class="mb-0">Registro de Vacinação</h2>
                </div>
                <div class="card-body">
                    <form method="post">
                        <div class="mb-3">
                            <label for="identificador" class="form-label">Número do Identificador</label>
                            <input type="text" id="identificador" name="identificador" class="form-control" value="<?= htmlspecialchars($_GET['identificador'] ?? '') ?>" placeholder="Ex: BOI-001" required>
                            <small class="text-muted">Veja os identificadores na <a href="informacoes.php">lista de animais</a>.</small>
                        </div>
                        <div class="mb-3">
                            <label for="vacina" class="form-label">Nome da Vacina</label>
                            <input type="text" id="vacina" name="vacina" class="form-control" placeholder="Ex: Febre aftosa" required>
                        </div>
                        <div class="mb-3">
                            <label for="data" class="form-label">Data da Aplicação</label>
                            <input type="date" id="data" name="data" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Registrar Vacinação</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
