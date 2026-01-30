<?php
require_once '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h2 class="mb-0"><i class="fas fa-id-card"></i> Sistema de Rastreabilidade RFID</h2>
                    <p class="mb-0">Monitoramento em tempo real de animais</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Painel de Leitura Atual -->
        <div class="col-md-6">
            <div class="card">
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
        <div class="col-md-6">
            <div class="card">
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
                    <h5 class="card-title mb-0"><i class="fas fa-history"></i> Histórico de Leituras</h5>
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
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-chart-line"></i> Leituras Hoje</h5>
                </div>
                <div class="card-body text-center">
                    <h3 id="leiturasHoje">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-check-circle"></i> Leituras Válidas</h5>
                </div>
                <div class="card-body text-center">
                    <h3 id="leiturasValidas">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0"><i class="fas fa-exclamation-triangle"></i> Leituras Inválidas</h5>
                </div>
                <div class="card-body text-center">
                    <h3 id="leiturasInvalidas">0</h3>
                </div>
            </div>
        </div>
    </div>
            </div>

        <script>
// Função para formatar data
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('pt-BR');
}

// Função para atualizar informações do animal
function updateAnimalInfo(data) {
    const animalInfo = document.getElementById('animalInfo');
    
    if (data.animal) {
        animalInfo.innerHTML = `
            <div class="alert alert-success">
                <h4 class="alert-heading">${data.animal.identificador}</h4>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong><i class="fas fa-paw"></i> Raça:</strong> ${data.animal.raca}</p>
                        <p><strong><i class="fas fa-birthday-cake"></i> Data de Nascimento:</strong> ${formatDate(data.animal.data_nascimento)}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong><i class="fas fa-venus-mars"></i> Gênero:</strong> ${data.animal.genero === 'M' ? 'Macho' : 'Fêmea'}</p>
                        <p><strong><i class="fas fa-weight"></i> Peso Atual:</strong> ${data.animal.peso} kg</p>
                    </div>
                </div>
            </div>
        `;
                } else {
        animalInfo.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> Animal não encontrado
            </div>
        `;
    }
}

// Função para atualizar leitura atual
function updateCurrentReading(tagId) {
    const currentReading = document.getElementById('currentReading');
    currentReading.innerHTML = `
        <div class="alert alert-info">
            <h4><i class="fas fa-id-card"></i> Tag Detectada</h4>
            <hr>
        <p><strong>ID:</strong> ${tagId}</p>
            <p><small class="text-muted"><i class="fas fa-clock"></i> ${new Date().toLocaleTimeString()}</small></p>
        </div>
    `;
}

// Função para atualizar histórico de leituras
function updateReadingHistory(historico) {
    const historyTableBody = document.getElementById('historyTableBody');
    historyTableBody.innerHTML = '';

    if (historico && historico.length > 0) {
        historico.forEach(leitura => {
            const row = document.createElement('tr');
            const status = leitura.identificador ? 
                '<span class="badge bg-success">Válida</span>' : 
                '<span class="badge bg-warning text-dark">Inválida</span>';
            
            row.innerHTML = `
                <td>${formatDate(leitura.data_leitura)}</td>
                <td>${leitura.tag_id}</td>
                <td>${leitura.identificador || 'Não identificado'}</td>
                <td>${status}</td>
            `;
            historyTableBody.appendChild(row);
        });
    } else {
        historyTableBody.innerHTML = `
            <tr>
                <td colspan="4" class="text-center">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Nenhuma leitura registrada
                    </div>
                </td>
            </tr>
        `;
    }
}

// Função para atualizar estatísticas
function updateStatistics(historico) {
    const hoje = new Date().toLocaleDateString('pt-BR');
    const leiturasHoje = historico.filter(leitura => 
        new Date(leitura.data_leitura).toLocaleDateString('pt-BR') === hoje
    ).length;
    
    const leiturasValidas = historico.filter(leitura => leitura.identificador).length;
    const leiturasInvalidas = historico.filter(leitura => !leitura.identificador).length;
    
    document.getElementById('leiturasHoje').textContent = leiturasHoje;
    document.getElementById('leiturasValidas').textContent = leiturasValidas;
    document.getElementById('leiturasInvalidas').textContent = leiturasInvalidas;
}

// Variável global para armazenar o último tag_id
let ultimoTagId = null;

function checkReadings() {
    console.log('Iniciando verificação de leituras...');
    
    // Configuração da requisição
    const options = {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Accept': 'application/json'
        },
        body: 'tag_id='
    };

    console.log('Enviando requisição para:', 'http://192.168.1.100/projeto_rastreabilidade/api/rfid/read.php');
    console.log('Opções da requisição:', options);

    fetch('http://192.168.1.100/projeto_rastreabilidade/api/rfid/read.php', options)
        .then(response => {
            console.log('Resposta recebida:', response);
            if (!response.ok) {
                throw new Error('Erro na resposta do servidor: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Dados recebidos:', data);
            
            if (data.success) {
                console.log('Resposta com sucesso:', data);
                
                // Verifica se o tag_id mudou
                if (data.tag_id && data.tag_id !== ultimoTagId) {
                    console.log('Novo tag_id detectado:', data.tag_id);
                    ultimoTagId = data.tag_id;
                    
                    // Se tiver tag_id, atualiza a leitura atual
                    updateCurrentReading(data.tag_id);
                    
                    // Atualiza as informações do animal
                    if (data.animal) {
                        console.log('Animal encontrado:', data.animal);
                        updateAnimalInfo(data);
                    } else {
                        console.log('Animal não encontrado');
                        document.getElementById('animalInfo').innerHTML = `
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i> Animal não encontrado
                            </div>
                        `;
                    }
                    
                    // Atualiza o histórico e estatísticas
                    if (data.historico && data.historico.length > 0) {
                        console.log('Histórico encontrado:', data.historico);
                        updateReadingHistory(data.historico);
                        updateStatistics(data.historico);
                    } else {
                        console.log('Nenhum histórico encontrado');
                    }
                } else if (!data.tag_id) {
                    console.log('Nenhum tag_id na resposta');
                    // Mostra mensagem de aguardando leitura
                    document.getElementById('currentReading').innerHTML = `
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> ${data.message || 'Aguardando leitura...'}
                        </div>
                    `;
                    
                    document.getElementById('animalInfo').innerHTML = `
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Nenhum animal identificado
                        </div>
                    `;
                    
                    document.getElementById('historyTableBody').innerHTML = `
                        <tr>
                            <td colspan="4" class="text-center">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> Nenhuma leitura registrada
                                </div>
                            </td>
                        </tr>
                    `;
                } else {
                    console.log('Mesmo tag_id, não atualizando interface');
                }
            } else {
                console.error('Erro na resposta:', data.message);
                document.getElementById('currentReading').innerHTML = `
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> ${data.message}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Erro na requisição:', error);
            document.getElementById('currentReading').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> Erro ao conectar com o servidor: ${error.message}
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