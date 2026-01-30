<?php
session_start();
// Verifica se o usuário não está logado
if (!isset($_SESSION['logged_in'])) {
    // Redireciona para a página de login
    header("Location: ../login.php");
    exit();
}

// Carregar config e depois header
include '../includes/config.php';
include '../includes/header.php';

////////////////////////////////////////////////////////
// LÓGICA PARA ATUALIZAR O REGISTRO (AO ENVIAR FORM)
////////////////////////////////////////////////////////
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modo']) && $_POST['modo'] === 'atualizar') {
    try {
        $id = $_POST['id'];
        $nascimento = $_POST['nascimento'];
        $sexo = $_POST['sexo'];
        $raca = $_POST['raca'];
        $pai = !empty($_POST['pai']) ? $_POST['pai'] : null;
        $mae = !empty($_POST['mae']) ? $_POST['mae'] : null;
        $tag_id = !empty($_POST['tag_id']) ? $_POST['tag_id'] : null;

        $stmt = $conn->prepare("UPDATE animais SET data_nascimento = ?, genero = ?, raca = ?, pai_id = ?, mae_id = ?, tag_id = ? WHERE id = ?");
        $stmt->execute([$nascimento, $sexo, $raca, $pai, $mae, $tag_id, $id]);

        echo "<div class='alert alert-success text-center mt-3'>Informações atualizadas com sucesso!</div>";
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger text-center mt-3'>Erro ao atualizar as informações: " . $e->getMessage() . "</div>";
    }
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
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger text-center mt-3'>Erro ao buscar animal: " . $e->getMessage() . "</div>";
    }
}
?>

<style>
/* Estilos específicos para a página de atualização */
.card-header-custom {
  background-color: #34699A;
  color: #fff;
  border-radius: 12px 12px 0 0;
}

.card-custom {
  border-radius: 12px;
  border: none;
  box-shadow: 0 4px 10px rgba(0,0,0,0.06);
}

.card-custom:hover {
  transform: translateY(-3px);
}

.btn-primary {
  background-color: #34699A;
  border-color: #34699A;
}

.btn-primary:hover {
  background-color: #285071;
  border-color: #285071;
}
</style>

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
                            <input type="number" class="form-control" name="id_busca" placeholder="Digite o ID do animal" required>
                            <button type="submit" class="btn btn-primary">Buscar</button>
                        </div>
                    </form>

                    <?php if ($animal): ?>
                    <!-- Formulário de atualização -->
                    <form method="POST">
                        <input type="hidden" name="modo" value="atualizar">
                        <input type="hidden" name="id" value="<?php echo $animal['id']; ?>">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nascimento" class="form-label">Data de Nascimento</label>
                                <input type="date" class="form-control" id="nascimento" name="nascimento" value="<?php echo $animal['data_nascimento']; ?>" required>
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
                                <input type="text" class="form-control" id="raca" name="raca" value="<?php echo $animal['raca']; ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="pai" class="form-label">Pai (opcional)</label>
                                <input type="text" class="form-control" id="pai" name="pai" value="<?php echo $animal['pai_id']; ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="mae" class="form-label">Mãe (opcional)</label>
                                <input type="text" class="form-control" id="mae" name="mae" value="<?php echo $animal['mae_id']; ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="tag_id" class="form-label">Tag RFID (opcional)</label>
                                <input type="text" class="form-control" id="tag_id" name="tag_id" value="<?php echo $animal['tag_id']; ?>" placeholder="Ex: 770EA45F">
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
