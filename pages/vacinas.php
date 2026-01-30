<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: /projeto_rastreabilidade/login.php");
    exit();
}

include '../includes/header.php';
include '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $identificador = $_POST['identificador'];
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
        echo "<div class='alert alert-danger text-center mt-3'>Erro: " . $e->getMessage() . "</div>";
    }
}
?>

<style>
/* Estilos para a página de vacinas */
.card-custom {
  border-radius: 12px;
  border: none;
  box-shadow: 0 4px 10px rgba(0,0,0,0.06);
  transition: 0.3s;
}

.card-custom:hover {
  transform: translateY(-3px);
}

.card-header-custom {
  background-color: #34699A;
  color: #fff;
  border-radius: 12px 12px 0 0;
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
                    <h2 class="mb-0">Registro de Vacinação</h2>
                </div>
                <div class="card-body">
                    <form method="post">
                        <div class="mb-3">
                            <label for="identificador" class="form-label">Número do Identificador</label>
                            <input type="text" name="identificador" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="vacina" class="form-label">Nome da Vacina</label>
                            <input type="text" name="vacina" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="data" class="form-label">Data da Aplicação</label>
                            <input type="date" name="data" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Registrar Vacinação</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
