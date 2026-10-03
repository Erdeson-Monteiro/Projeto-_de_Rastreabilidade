-- Cria o banco se não existir
-- (para atualizar um banco já existente, use db/atualizacao_banco.sql)
CREATE DATABASE IF NOT EXISTS ifmabov CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ifmabov;

-- Tabela de Usuários do sistema
-- A senha é guardada com password_hash() do PHP (nunca em texto puro).
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL
);

-- Usuário de demonstração: usuario@exemplo.com / senha123
-- (troque a senha antes de usar em produção)
INSERT IGNORE INTO usuarios (nome, email, senha) VALUES
    ('Usuário Demo', 'usuario@exemplo.com', '$2y$10$1MehxGhf0s0mJwdVSQtMyehHrkmYHPWn..zQYew0evBzEXLb9bDSG');

-- Tabela de Animais
-- Observação:
--  1) "id" é a chave primária auto-increment, usada internamente.
--  2) "identificador" é um campo extra que o usuário informa no cadastro (pode ser único ou não, dependendo da sua regra).
--  3) "data_nascimento" substitui a "idade" e facilita o cálculo de idade.
--  4) "genero" se mantém em ENUM('M','F').
--  5) "raca", "pai_id" e "mae_id" permitem valores alfanuméricos, pois muitas vezes o usuário só digita um ID/registro e não necessariamente é um INT existente no BD.
--  6) "peso" armazenará o peso inicial do animal (até 9999,99 kg).
--  7) "tag_id" armazenará o identificador único da tag RFID.

CREATE TABLE IF NOT EXISTS animais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    identificador VARCHAR(50) NOT NULL,
    data_nascimento DATE NOT NULL,
    genero ENUM('M','F') NOT NULL,
    raca VARCHAR(50) NOT NULL,
    pai_id VARCHAR(50) DEFAULT NULL,
    mae_id VARCHAR(50) DEFAULT NULL,
    peso DECIMAL(6,2) NOT NULL,
    tag_id VARCHAR(50) UNIQUE DEFAULT NULL,
    data_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Histórico de Leituras RFID
-- Sem chave estrangeira para "animais": o leitor também registra tags ainda
-- não cadastradas, que aparecem como "Leituras Inválidas" no monitoramento.
CREATE TABLE IF NOT EXISTS historico_leitura (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tag_id VARCHAR(50) NOT NULL,
    data_leitura TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_historico_tag (tag_id)
);

-- Tabela de Vacinas
-- O código utiliza "INSERT INTO vacinas (animal_id, vacina, data)"
-- Por isso, substituímos "nome" por "vacina" e adicionamos "animal_id" para relacionar com 'animais'.

CREATE TABLE IF NOT EXISTS vacinas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    animal_id INT NOT NULL,
    vacina VARCHAR(100) NOT NULL,
    data DATE NOT NULL,
    CONSTRAINT fk_vacina_animal
        FOREIGN KEY (animal_id) REFERENCES animais(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

-- Tabela de Pesagem
-- O código insere "animal_id, data, peso"
-- Então criamos esses campos, relacionando "animal_id" com 'animais'.

CREATE TABLE IF NOT EXISTS pesagem (
    id INT AUTO_INCREMENT PRIMARY KEY,
    animal_id INT NOT NULL,
    peso DECIMAL(6,2) NOT NULL,
    data DATE NOT NULL,
    CONSTRAINT fk_pesagem_animal
        FOREIGN KEY (animal_id) REFERENCES animais(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

-- =====================================================================
-- Dados de exemplo
-- Já vêm junto para o sistema abrir com animais, pesagens, vacinas e
-- leituras RFID. Para começar com o banco vazio, apague daqui até o fim.
-- =====================================================================

INSERT INTO animais (identificador, data_nascimento, genero, raca, pai_id, mae_id, peso, tag_id) VALUES
    ('BOI-001',  '2023-03-10', 'M', 'Nelore',    NULL,      NULL,       280.00, 'A1B2C3D4'),
    ('VACA-002', '2022-08-21', 'F', 'Gir',       NULL,      NULL,       350.00, '0F3E9A12'),
    ('BEZ-003',  '2024-09-05', 'F', 'Girolando', 'BOI-001', 'VACA-002',  95.50, '7C21D0B8'),
    ('BOI-004',  '2021-11-30', 'M', 'Angus',     NULL,      NULL,       520.00, NULL);

-- Pesagens mensais (usadas no gráfico do Dashboard)
INSERT INTO pesagem (animal_id, peso, data) VALUES
    (1, 300.00, '2025-01-15'), (1, 318.50, '2025-02-15'), (1, 332.00, '2025-03-15'), (1, 347.20, '2025-04-15'),
    (2, 362.00, '2025-01-15'), (2, 368.40, '2025-02-15'), (2, 375.00, '2025-03-15'), (2, 381.30, '2025-04-15'),
    (3, 110.00, '2025-01-15'), (3, 128.70, '2025-02-15'), (3, 146.00, '2025-03-15'), (3, 163.90, '2025-04-15'),
    (4, 540.00, '2025-01-15'), (4, 548.00, '2025-02-15'), (4, 556.50, '2025-03-15'), (4, 561.00, '2025-04-15');

INSERT INTO vacinas (animal_id, vacina, data) VALUES
    (1, 'Febre aftosa', '2025-02-01'),
    (1, 'Raiva',        '2025-03-10'),
    (2, 'Febre aftosa', '2025-02-01'),
    (2, 'Brucelose',    '2024-10-20'),
    (3, 'Clostridiose', '2025-01-20');

-- Algumas leituras RFID de hoje (a última é de uma tag não cadastrada)
INSERT INTO historico_leitura (tag_id, data_leitura) VALUES
    ('A1B2C3D4', NOW() - INTERVAL 50 MINUTE),
    ('0F3E9A12', NOW() - INTERVAL 35 MINUTE),
    ('7C21D0B8', NOW() - INTERVAL 20 MINUTE),
    ('A1B2C3D4', NOW() - INTERVAL 5 MINUTE);
