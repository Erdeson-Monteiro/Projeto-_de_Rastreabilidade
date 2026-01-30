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
        $data_pesagem = $_POST['data_pesagem'];
        $peso = $_POST['peso'];
        
        // Primeiro, verifica se o animal existe
        $stmt = $conn->prepare("SELECT id FROM animais WHERE identificador = ?");
        $stmt->execute([$identificador]);
        $animal = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$animal) {
            echo "<div class='alert alert-danger text-center mt-3'>Animal não encontrado!</div>";
        } else {
            // Insere a pesagem
            $stmt = $conn->prepare("INSERT INTO pesagem (animal_id, data, peso) VALUES (?, ?, ?)");
            $stmt->execute([$animal['id'], $data_pesagem, $peso]);
            
            if ($stmt->rowCount() > 0) {
                echo "<div class='alert alert-success text-center mt-3'>Pesagem registrada com sucesso!</div>";
            } else {
                echo "<div class='alert alert-danger text-center mt-3'>Erro ao registrar a pesagem.</div>";
            }
        }
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger text-center mt-3'>Erro: " . $e->getMessage() . "</div>";
    }
}
?>

<style>
/* Estilos para a página de pesagem */
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
                    <h2 class="mb-0">Registro de Pesagem</h2>
                </div>
                <div class="card-body">
                    <form method="post">
                        <div class="mb-3">
                            <label for="identificador" class="form-label">Número do Identificador</label>
                            <input type="text" name="identificador" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="data_pesagem" class="form-label">Data da Pesagem</label>
                            <input type="date" name="data_pesagem" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="peso" class="form-label">Peso (Kg)</label>
                            <input type="number" name="peso" class="form-control" step="0.1" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Registrar Pesagem</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
