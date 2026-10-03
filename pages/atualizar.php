<?php
// Só usuários logados acessam esta página
require_once '../includes/auth.php';

// Carregar config e depois header
require_once '../includes/config.php';
require_once '../includes/header.php';

////////////////////////////////////////////////////////
// LÓGICA PARA ATUALIZAR O REGISTRO (AO ENVIAR FORM)
////////////////////////////////////////////////////////
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modo']) && $_POST['modo'] === 'atualizar') {
    try {
        $id = $_POST['id'];
        $identificador = trim($_POST['identificador']);
        $nascimento = $_POST['nascimento'];
        $sexo = $_POST['sexo'];
        $raca = $_POST['raca'];
        $pai = !empty($_POST['pai']) ? $_POST['pai'] : null;
        $mae = !empty($_POST['mae']) ? $_POST['mae'] : null;
        $tag_id = !empty($_POST['tag_id']) ? strtoupper(trim($_POST['tag_id'])) : null;

        $stmt = $conn->prepare("UPDATE animais SET identificador = ?, data_nascimento = ?, genero = ?, raca = ?, pai_id = ?, mae_id = ?, tag_id = ? WHERE id = ?");
        $stmt->execute([$identificador, $nascimento, $sexo, $raca, $pai, $mae, $tag_id, $id]);

        echo "<div class='alert alert-success text-center mt-3'>Informações atualizadas com sucesso!</div>";
    } catch (PDOException $e) {
        $mensagem = $e->getCode() == 23000
            ? "Esta tag RFID já está vinculada a outro animal."
            : "Não foi possível salvar. Tente novamente.";
        echo "<div class='alert alert-danger text-center mt-3'>Erro ao atualizar as informações: " . htmlspecialchars($mensagem) . "</div>";
    }
}

// Depois de atualizar, continua exibindo o mesmo animal
if (isset($_POST['modo']) && $_POST['modo'] === 'atualizar') {
    $_GET['id'] = $_POST['id'];
}

// Verifica se foi submetido o ID via GET ou via POST da busca
$id_animal = isset($_GET['id']) ? (int) $_GET['id'] : null;
if (isset($_POST['modo']) && $_POST['modo'] === 'buscar') {
    $id_animal = (int) $_POST['id_busca'];
}

// Lógica para buscar dados do animal, se ID existir
$animal = null;
if ($id_animal) {
    try {
        $stmt = $conn->prepare("SELECT * FROM animais WHERE id = ?");
        $stmt->execute([$id_animal]);
        $animal = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$animal) {
            echo "<div class='alert alert-warning text-center mt-3'>Nenhum animal encontrado com o ID " . $id_animal . ".</div>";
        }
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger text-center mt-3'>Erro ao buscar o animal. Tente novamente.</div>";
    }
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-custom">
                <div class="card-header card-header-custom text-center">
                    <h2 class="mb-0">Atualizar Animal</h2>
                </div>
                <div class="card-body">
                    <!-- Formulário de busca -->
                    <form method="POST" class="mb-4">
                        <input type="hidden" name="modo" value="buscar">
                        <div class="input-group">
                            <input type="number" class="form-control" name="id_busca" placeholder="Digite o ID do animal" value="<?= $id_animal ?: '' ?>" min="1" required>
                            <button type="submit" class="btn btn-primary">Buscar</button>
                        </div>
                    </form>
                    <small class="text-muted d-block mb-4" style="margin-top: -16px;">
                        O ID aparece na <a href="informacoes.php">lista de animais</a>.
                    </small>

                    <?php if ($animal): ?>
                    <!-- Formulário de atualização -->
                    <form method="POST">
                        <input type="hidden" name="modo" value="atualizar">
                        <input type="hidden" name="id" value="<?php echo $animal['id']; ?>">
                        
                        <div class="mb-3">
                            <label for="identificador" class="form-label">Identificador</label>
                            <input type="text" class="form-control" id="identificador" name="identificador" value="<?= htmlspecialchars($animal['identificador']) ?>" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nascimento" class="form-label">Data de Nascimento</label>
                                <input type="date" class="form-control" id="nascimento" name="nascimento" value="<?= $animal['data_nascimento'] ?>" max="<?= date('Y-m-d') ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="sexo" class="form-label">Sexo</label>
                                <select class="form-select" id="sexo" name="sexo" required>
                                    <option value="">Selecione...</option>
                                    <option value="M" <?php echo $animal['genero'] === 'M' ? 'selected' : ''; ?>>Macho</option>
                                    <option value="F" <?php echo $animal['genero'] === 'F' ? 'selected' : ''; ?>>Fêmea</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="raca" class="form-label">Raça</label>
                                <input type="text" class="form-control" id="raca" name="raca" value="<?= htmlspecialchars($animal['raca'] ?? '') ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="pai" class="form-label">Pai (opcional)</label>
                                <input type="text" class="form-control" id="pai" name="pai" value="<?= htmlspecialchars($animal['pai_id'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="mae" class="form-label">Mãe (opcional)</label>
                                <input type="text" class="form-control" id="mae" name="mae" value="<?= htmlspecialchars($animal['mae_id'] ?? '') ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="tag_id" class="form-label">Tag RFID (opcional)</label>
                                <input type="text" class="form-control" id="tag_id" name="tag_id" value="<?= htmlspecialchars($animal['tag_id'] ?? '') ?>" placeholder="Ex: 770EA45F">
                                <small class="text-muted">Pode ser registrado posteriormente</small>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary">Atualizar Animal</button>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
