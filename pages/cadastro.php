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

// Processa o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Obtém os dados do formulário
        $identificador = $_POST['identificador'] ?? '';
        $nascimento = $_POST['nascimento'] ?? '';
        $sexo = $_POST['sexo'] ?? '';
        $raca = $_POST['raca'] ?? '';
        $pai = $_POST['pai'] ?? null;
        $mae = $_POST['mae'] ?? null;
        $peso = !empty($_POST['peso']) ? (float) str_replace(',', '.', $_POST['peso']) : 0;
        $tag_id = $_POST['tag_id'] ?? null;

        // Validações
        if (empty($identificador)) {
            throw new Exception("O identificador é obrigatório");
        }
        if (empty($nascimento)) {
            throw new Exception("A data de nascimento é obrigatória");
        }
        if (empty($sexo)) {
            throw new Exception("O sexo é obrigatório");
        }
        if (empty($raca)) {
            throw new Exception("A raça é obrigatória");
        }
        if ($peso <= 0) {
            throw new Exception("O peso deve ser maior que zero");
        }

        // Prepara a consulta
        $stmt = $conn->prepare("INSERT INTO animais (identificador, data_nascimento, genero, raca, pai_id, mae_id, peso, tag_id) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        // Executa a consulta com os parâmetros
        $stmt->execute([$identificador, $nascimento, $sexo, $raca, $pai, $mae, $peso, $tag_id]);

        echo "<div class='alert alert-success text-center mt-3'>Cadastro registrado com sucesso!</div>";
    } catch (Exception $e) {
        echo "<div class='alert alert-danger text-center mt-3'>Erro ao registrar o animal: " . $e->getMessage() . "</div>";
    }
}
?>

<style>
/* Estilos específicos para a página de cadastro */
.card-header-custom {
  background-color: #34699A;
  color: #fff;
  border-radius: 12px 12px 0 0;
}

.card-custom {
  border-radius: 12px;
  border: none;
  box-shadow: 0 4px 10px rgba(0,0,0,0.06);
  margin-bottom: 20px;
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

/* Estilo específico para o campo de peso */
.peso-container {
  margin-bottom: 15px;
}

.peso-container input {
  width: 100%;
  padding: 8px;
  border: 1px solid #ced4da;
  border-radius: 4px;
}

.peso-container small {
  display: block;
  margin-top: 5px;
  color: #6c757d;
}
</style>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-custom">
                <div class="card-header card-header-custom text-center">
                    <h2 class="mb-0">Cadastro de Animal</h2>
                </div>
                <div class="card-body">
                    <form method="POST" class="needs-validation" novalidate>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="identificador" class="form-label">Identificador</label>
                                <input type="text" class="form-control" id="identificador" name="identificador" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="nascimento" class="form-label">Data de Nascimento</label>
                                <input type="date" class="form-control" id="nascimento" name="nascimento" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="sexo" class="form-label">Sexo</label>
                                <select class="form-select" id="sexo" name="sexo" required>
                                    <option value="">Selecione...</option>
                                    <option value="M">Macho</option>
                                    <option value="F">Fêmea</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="raca" class="form-label">Raça</label>
                                <input type="text" class="form-control" id="raca" name="raca" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="pai" class="form-label">Pai (opcional)</label>
                                <input type="text" class="form-control" id="pai" name="pai">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="mae" class="form-label">Mãe (opcional)</label>
                                <input type="text" class="form-control" id="mae" name="mae">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="peso-container">
                                    <label for="peso" class="form-label">Peso (kg)</label>
                                    <input type="number" step="0.01" class="form-control" id="peso" name="peso" required min="0.01">
                                    <small>Use ponto (.) como separador decimal</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="tag_id" class="form-label">Tag RFID (opcional)</label>
                                <input type="text" class="form-control" id="tag_id" name="tag_id" placeholder="Ex: 770EA45F">
                                <small class="text-muted">Pode ser registrado posteriormente</small>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary">Cadastrar Animal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Ajuda RFID -->
<div class="modal fade" id="rfidHelpModal" tabindex="-1" aria-labelledby="rfidHelpModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rfidHelpModalLabel">
                    <i class="bi bi-rss"></i> Sobre as Tags RFID
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6>O que é uma Tag RFID?</h6>
                <p>Uma tag RFID é um dispositivo eletrônico que armazena um identificador único para cada animal.</p>
                
                <h6>Como obter uma Tag RFID?</h6>
                <ul>
                    <li>Use o leitor RFID para ler uma tag física</li>
                    <li>Gere uma tag aleatória usando o botão "Gerar"</li>
                    <li>Digite manualmente o código da tag</li>
                </ul>
                
                <h6>Importante:</h6>
                <p>O cadastro da tag RFID é opcional e pode ser feito posteriormente na página de Monitoramento RFID.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script>
// Função para gerar uma tag RFID aleatória
function gerarTagAleatoria() {
    const caracteres = '0123456789ABCDEF';
    let tag = '';
    for (let i = 0; i < 8; i++) {
        tag += caracteres.charAt(Math.floor(Math.random() * caracteres.length));
    }
    document.getElementById('tag_id').value = tag;
}
</script>

<?php include '../includes/footer.php'; ?>
