<?php
// Só usuários logados acessam esta página
require_once '../includes/auth.php';

// Carregar config e depois header
require_once '../includes/config.php';
require_once '../includes/header.php';

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

/* Celular: cards em duas colunas, mais compactos */
@media (max-width: 576px) {
  .stats-row .card { padding: 1rem !important; }
  .stats-row h5 { font-size: 0.95rem; }
  .stats-icon { font-size: 2rem; }
  .top-banner { padding: 20px 12px; }
  .top-banner h1 { font-size: 1.4rem; }
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
    <div class="col-6 col-md-3 mb-3">
      <div class="card p-4 h-100">
        <i class="bi bi-collection stats-icon text-primary"></i>
        <h5 class="text-muted">Total de Animais</h5>
        <h2 class="text-primary mb-0"><?php echo $stats['total_animais']; ?></h2>
      </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
      <div class="card p-4 h-100">
        <i class="bi bi-gender-male stats-icon text-info"></i>
        <h5 class="text-muted">Machos</h5>
        <h2 class="text-info mb-0"><?php echo $stats['total_machos']; ?></h2>
      </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
      <div class="card p-4 h-100">
        <i class="bi bi-gender-female stats-icon text-danger"></i>
        <h5 class="text-muted">Fêmeas</h5>
        <h2 class="text-danger mb-0"><?php echo $stats['total_femeas']; ?></h2>
      </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
      <a href="rfid_management.php" class="card p-4 h-100 text-decoration-none">
          <i class="bi bi-rss stats-icon text-success"></i>
          <h5 class="text-muted">Monitoramento RFID</h5>
          <h2 class="text-success mb-0"><i class="bi bi-arrow-right"></i></h2>
      </a>
    </div>
  </div>

  <!-- Gráfico de evolução de peso -->
  <div class="chart-section mb-5">
    <h4>Evolução do Peso dos Animais</h4>
    <div style="position: relative; height: 320px;">
      <canvas id="pesoChart"></canvas>
    </div>
  </div>
</div>

<!-- Script do gráfico -->
<script>
document.addEventListener('DOMContentLoaded', () => {
  const ctx = document.getElementById('pesoChart').getContext('2d');

  // Função para buscar os dados do banco de dados
  async function fetchData() {
    try {
      const response = await fetch('get_media_peso.php');
      
      if (!response.ok) {
        throw new Error('Erro ao buscar dados: ' + response.status);
      }
      
      const data = await response.json();
      return data;
    } catch (error) {
      console.error('Erro ao buscar dados:', error);
      return [];
    }
  }

  // Função para inicializar o gráfico
  async function initChart() {
    const data = await fetchData();

    if (data.length === 0) {
      document.getElementById('pesoChart').style.display = 'none';
      document.querySelector('.chart-section').innerHTML += 
        '<div class="alert alert-info">Nenhum dado de pesagem disponível.</div>';
      return;
    }

    // Extrai as labels (meses) e os valores (média de peso)
    const labels = data.map(item => item.mes);
    const medias = data.map(item => item.media_peso);

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
            beginAtZero: false, // foca na variação de peso, em vez de achatar a linha
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
  }

  // Inicializa o gráfico
  initChart();
});
</script>

<?php include '../includes/footer.php'; ?>
