<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: /projeto_rastreabilidade/login.php");
    exit();
}

// Carregar config e depois header
include '../includes/config.php';
include '../includes/header.php';

// Consulta ao banco de dados para obter estatísticas
$stmt = $conn->prepare("SELECT 
    (SELECT COUNT(*) FROM animais) AS total_animais,
    (SELECT COUNT(*) FROM animais WHERE genero = 'M') AS total_machos,
    (SELECT COUNT(*) FROM animais WHERE genero = 'F') AS total_femeas");
$stmt->execute();
$stats = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<style>
/* Estilo específico da página Index */

/* Banner Superior (mais compacto e sem duplicar a navbar) */
.top-banner {
  background-color: #e9ecef;  /* cinza claro para contraste */
  padding: 30px 0;
  text-align: center;
  border-bottom: 2px solid #dee2e6;
}
.top-banner h1 {
  margin: 0;
  font-weight: 700;
  font-size: 2rem;
  color: #343a40;   /* Contraste maior no texto */
}
.top-banner p {
  color: #495057;
  margin-bottom: 0;
  font-weight: 500;
}

/* Barra de busca */
.search-bar {
  margin-top: 20px;
}

/* Seção de estatísticas */
.stats-row .card {
  border: none;
  border-radius: 12px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.06);
  transition: 0.3s;
}
.stats-row .card:hover {
  transform: translateY(-4px);
}
.stats-icon {
  font-size: 2.5rem;
  margin-bottom: 10px;
}

/* Seção do gráfico */
.chart-section {
  border: none;
  border-radius: 12px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.06);
  padding: 20px;
  background-color: #ffffff;
}
.chart-section h4 {
  margin-bottom: 15px;
  color: #343a40;
  font-weight: 600;
}
</style>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<!-- Banner Superior -->
<div class="top-banner">
  <h1>Bem-vindo ao Rastreamento Bovino - IFMA</h1>
  <p>Gerencie seu rebanho de forma rápida e eficiente</p>
</div>

<!-- Conteúdo principal -->
<div class="container my-5">
  <!-- Linha de estatísticas -->
  <div class="row stats-row text-center mb-4">
    <div class="col-md-3 mb-3">
      <div class="card p-4">
        <i class="bi bi-collection stats-icon text-primary"></i>
        <h5 class="text-muted">Total de Animais</h5>
        <h2 class="text-primary mb-0"><?php echo $stats['total_animais']; ?></h2>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card p-4">
        <i class="bi bi-gender-male stats-icon text-info"></i>
        <h5 class="text-muted">Machos</h5>
        <h2 class="text-info mb-0"><?php echo $stats['total_machos']; ?></h2>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card p-4">
        <i class="bi bi-gender-female stats-icon text-danger"></i>
        <h5 class="text-muted">Fêmeas</h5>
        <h2 class="text-danger mb-0"><?php echo $stats['total_femeas']; ?></h2>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card p-4">
        <a href="rfid_management.php" class="text-decoration-none">
          <i class="bi bi-rss stats-icon text-success"></i>
          <h5 class="text-muted">Monitoramento RFID</h5>
          <h2 class="text-success mb-0"><i class="bi bi-arrow-right"></i></h2>
        </a>
      </div>
    </div>
  </div>

  <!-- Gráfico de evolução de peso -->
  <div class="chart-section mb-5">
    <h4>Evolução do Peso dos Animais</h4>
    <canvas id="pesoChart" height="80"></canvas>
  </div>
</div>

<!-- Script do gráfico -->
<script>
document.addEventListener('DOMContentLoaded', () => {
  const ctx = document.getElementById('pesoChart').getContext('2d');
  console.log('Inicializando gráfico...');

  // Função para buscar os dados do banco de dados
  async function fetchData() {
    try {
      console.log('Buscando dados...');
      const response = await fetch('get_media_peso.php');
      console.log('Resposta recebida:', response.status);
      
      if (!response.ok) {
        throw new Error('Erro ao buscar dados: ' + response.status);
      }
      
      const data = await response.json();
      console.log('Dados recebidos:', data);
      return data;
    } catch (error) {
      console.error('Erro ao buscar dados:', error);
      return [];
    }
  }

  // Função para inicializar o gráfico
  async function initChart() {
    console.log('Inicializando gráfico...');
    const data = await fetchData();
    console.log('Dados para o gráfico:', data);

    if (data.length === 0) {
      console.log('Nenhum dado encontrado');
      document.getElementById('pesoChart').style.display = 'none';
      document.querySelector('.chart-section').innerHTML += 
        '<div class="alert alert-info">Nenhum dado de pesagem disponível.</div>';
      return;
    }

    // Extrai as labels (meses) e os valores (média de peso)
    const labels = data.map(item => item.mes);
    const medias = data.map(item => item.media_peso);
    console.log('Labels:', labels);
    console.log('Médias:', medias);

    // Cria o gráfico
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: 'Média de Peso (kg)',
          data: medias,
          borderColor: 'rgba(52, 105, 154, 1)',
          backgroundColor: 'rgba(52, 105, 154, 0.1)',
          borderWidth: 2,
          fill: true,
          tension: 0.4
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { 
            position: 'top',
            labels: {
              font: {
                size: 14
              }
            }
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                return `Peso médio: ${context.parsed.y.toFixed(2)} kg`;
              }
            }
          }
        },
        scales: {
          y: { 
            beginAtZero: true,
            title: {
              display: true,
              text: 'Peso (kg)'
            }
          },
          x: {
            title: {
              display: true,
              text: 'Mês'
            }
          }
        }
      }
    });
    console.log('Gráfico criado com sucesso');
  }

  // Inicializa o gráfico
  initChart();
});
</script>

<?php include '../includes/footer.php'; ?>
