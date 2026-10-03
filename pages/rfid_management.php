<?php
// Só usuários logados acessam esta página
require_once '../includes/auth.php';
require_once '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card text-white border-0" style="background-color: #34699A;">
                <div class="card-body text-center">
                    <h2 class="mb-0"><i class="fas fa-id-card"></i> Sistema de Rastreabilidade RFID</h2>
                    <p class="mb-0">Monitoramento em tempo real de animais</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Painel de Leitura Atual -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-rss"></i> Leitura Atual</h5>
                </div>
                <div class="card-body">
                    <div id="currentReading" class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
                        <p class="text-muted mt-2">Aguardando leitura...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Painel de Informações do Animal -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-cow"></i> Informações do Animal</h5>
                </div>
                <div class="card-body">
                    <div id="animalInfo" class="text-center">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Nenhum animal identificado
                        </div>
                </div>
                </div>
            </div>
        </div>
                                    </div>

    <!-- Painel de Histórico de Leituras -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-history"></i> Histórico de Leituras <small class="fw-normal">(últimas 10)</small></h5>
                </div>
                <div class="card-body">
                    <div id="readingHistory" class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th><i class="fas fa-calendar"></i> Data/Hora</th>
                                    <th><i class="fas fa-tag"></i> Tag ID</th>
                                    <th><i class="fas fa-id-badge"></i> Identificador</th>
                                    <th><i class="fas fa-info-circle"></i> Status</th>
                                </tr>
                            </thead>
                            <tbody id="historyTableBody">
                                <!-- Dados serão inseridos via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Painel de Estatísticas -->
    <div class="row mt-4">
        <div class="col-md-4 mb-3">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-chart-line"></i> Leituras Hoje</h5>
                </div>
                <div class="card-body text-center">
                    <h3 id="leiturasHoje">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-check-circle"></i> Válidas Hoje</h5>
                </div>
                <div class="card-body text-center">
                    <h3 id="leiturasValidas">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card h-100">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0"><i class="fas fa-exclamation-triangle"></i> Inválidas Hoje</h5>
                </div>
                <div class="card-body text-center">
                    <h3 id="leiturasInvalidas">0</h3>
                </div>
            </div>
        </div>
    </div>
            </div>

        <script>
// Escapa texto vindo do servidor antes de colocar no HTML
function esc(valor) {
    const div = document.createElement('div');
    div.textContent = valor ?? '';
    return div.innerHTML;
}

// "2025-03-10 14:30:00" -> "10/03/2025, 14:30:00" (hora local do servidor)
function formatDate(dateString) {
    if (!dateString) return '---';
    return new Date(dateString.replace(' ', 'T')).toLocaleString('pt-BR');
}

// "2023-03-10" -> "10/03/2023" (sem converter fuso, que mudava o dia)
function formatDateOnly(dateString) {
    if (!dateString) return '---';
    const [ano, mes, dia] = dateString.substring(0, 10).split('-');
    return `${dia}/${mes}/${ano}`;
}

// Função para atualizar informações do animal
function updateAnimalInfo(animal) {
    const animalInfo = document.getElementById('animalInfo');

    if (animal) {
        animalInfo.innerHTML = `
            <div class="alert alert-success mb-0">
                <h4 class="alert-heading">${esc(animal.identificador)}</h4>
                <hr>
                <div class="row">
                    <div class="col-sm-6">
                        <p><strong><i class="fas fa-paw"></i> Raça:</strong> ${esc(animal.raca)}</p>
                        <p class="mb-sm-0"><strong><i class="fas fa-birthday-cake"></i> Nascimento:</strong> ${formatDateOnly(animal.data_nascimento)}</p>
                    </div>
                    <div class="col-sm-6">
                        <p><strong><i class="fas fa-venus-mars"></i> Sexo:</strong> ${animal.genero === 'M' ? 'Macho' : 'Fêmea'}</p>
                        <p class="mb-0"><strong><i class="fas fa-weight-hanging"></i> Peso:</strong> ${esc(animal.peso)} kg</p>
                    </div>
                </div>
                <a href="informacoes.php?id=${encodeURIComponent(animal.id)}" class="btn btn-sm btn-success mt-3">
                    <i class="fas fa-eye"></i> Ver ficha completa
                </a>
            </div>
        `;
    } else {
        animalInfo.innerHTML = `
            <div class="alert alert-danger mb-0">
                <i class="fas fa-exclamation-triangle"></i> Tag não cadastrada
                <div class="small mt-2">Vincule esta tag a um animal em
                    <a href="cadastro.php">Cadastrar</a> ou <a href="atualizar.php">Atualizar</a>.
                </div>
            </div>
        `;
    }
}

// Função para atualizar leitura atual
function updateCurrentReading(tagId, dataLeitura) {
    document.getElementById('currentReading').innerHTML = `
        <div class="alert alert-info mb-0">
            <h4><i class="fas fa-id-card"></i> Tag Detectada</h4>
            <hr>
            <p><strong>ID:</strong> ${esc(tagId)}</p>
            <p class="mb-0"><small class="text-muted"><i class="fas fa-clock"></i> ${formatDate(dataLeitura)}</small></p>
        </div>
    `;
}

// Mensagem quando ainda não há nenhuma leitura
function showWaiting(message) {
    document.getElementById('currentReading').innerHTML = `
        <div class="alert alert-info mb-0">
            <i class="fas fa-info-circle"></i> ${esc(message || 'Aguardando leitura...')}
            <div class="small mt-2">Aproxime uma tag do leitor RFID.</div>
        </div>
    `;
    document.getElementById('animalInfo').innerHTML = `
        <div class="alert alert-info mb-0">
            <i class="fas fa-info-circle"></i> Nenhum animal identificado
        </div>
    `;
}

// Função para atualizar histórico de leituras (todas as tags)
function updateReadingHistory(historico) {
    const historyTableBody = document.getElementById('historyTableBody');

    if (!historico || historico.length === 0) {
        historyTableBody.innerHTML = `
            <tr>
                <td colspan="4" class="text-center text-muted py-4">
                    <i class="fas fa-info-circle"></i> Nenhuma leitura registrada
                </td>
            </tr>
        `;
        return;
    }

    historyTableBody.innerHTML = historico.map(leitura => {
        const status = leitura.identificador
            ? '<span class="badge bg-success">Válida</span>'
            : '<span class="badge bg-warning text-dark">Inválida</span>';
        return `
            <tr>
                <td class="text-nowrap">${formatDate(leitura.data_leitura)}</td>
                <td>${esc(leitura.tag_id)}</td>
                <td>${leitura.identificador ? esc(leitura.identificador) : '<span class="text-muted">Não identificado</span>'}</td>
                <td>${status}</td>
            </tr>
        `;
    }).join('');
}

// Função para atualizar estatísticas do dia (calculadas no servidor)
function updateStatistics(estatisticas) {
    estatisticas = estatisticas || { hoje: 0, validas: 0, invalidas: 0 };
    document.getElementById('leiturasHoje').textContent = estatisticas.hoje;
    document.getElementById('leiturasValidas').textContent = estatisticas.validas;
    document.getElementById('leiturasInvalidas').textContent = estatisticas.invalidas;
}

// Id da última leitura exibida: muda a cada nova leitura, mesmo que seja a mesma tag
let ultimaLeituraId = undefined;

function checkReadings() {
    fetch('../api/rfid/read.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Accept': 'application/json'
        },
        body: 'tag_id='
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro na resposta do servidor: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (!data.success) {
                throw new Error(data.message);
            }

            // Só redesenha quando chega uma leitura nova
            if (data.leitura_id === ultimaLeituraId) {
                return;
            }
            ultimaLeituraId = data.leitura_id;

            if (data.tag_id) {
                updateCurrentReading(data.tag_id, data.data_leitura);
                updateAnimalInfo(data.animal);
            } else {
                showWaiting(data.message);
            }
            updateReadingHistory(data.historico);
            updateStatistics(data.estatisticas);
        })
        .catch(error => {
            console.error('Erro ao consultar leituras:', error);
            ultimaLeituraId = undefined; // tenta redesenhar quando a conexão voltar
            document.getElementById('currentReading').innerHTML = `
                <div class="alert alert-danger mb-0">
                    <i class="fas fa-exclamation-triangle"></i> Erro ao conectar com o servidor: ${esc(error.message)}
                </div>
            `;
        });
}

// Verifica leituras a cada 2 segundos
setInterval(checkReadings, 2000);

// Executa a primeira verificação imediatamente
checkReadings();
        </script>

<?php
require_once '../includes/footer.php';
?>