-- Cria o banco se não existir
DROP DATABASE IF EXISTS ifmabov;
CREATE DATABASE IF NOT EXISTS ifmabov;
USE ifmabov;

-- Tabela de Usuários (caso ainda seja necessária)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL
);

-- Tabela de Animais
-- Observação:
--  1) "id" é a chave primária auto-increment, usada internamente.
--  2) "identificador" é um campo extra que o usuário informa no cadastro (pode ser único ou não, dependendo da sua regra).
--  3) "data_nascimento" substitui a "idade" e facilita o cálculo de idade.
--  4) "genero" se mantém em ENUM('M','F').
--  5) "raca", "pai_id" e "mae_id" permitem valores alfanuméricos, pois muitas vezes o usuário só digita um ID/registro e não necessariamente é um INT existente no BD.
--  6) "peso" armazenará o peso inicial do animal.
--  7) "tag_id" armazenará o identificador único da tag RFID.

CREATE TABLE IF NOT EXISTS animais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    identificador VARCHAR(50) NOT NULL,
    data_nascimento DATE NOT NULL,
    genero ENUM('M','F') NOT NULL,
    raca VARCHAR(50) NOT NULL,
    pai_id VARCHAR(50) DEFAULT NULL,
    mae_id VARCHAR(50) DEFAULT NULL,
    peso DECIMAL(5,2) NOT NULL,
    tag_id VARCHAR(50) UNIQUE DEFAULT NULL,
    data_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Histórico de Leituras RFID
CREATE TABLE IF NOT EXISTS historico_leitura (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tag_id VARCHAR(50) NOT NULL,
    data_leitura TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_historico_tag
        FOREIGN KEY (tag_id) REFERENCES animais(tag_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
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
    peso DECIMAL(5,2) NOT NULL,
    data DATE NOT NULL,
    CONSTRAINT fk_pesagem_animal
        FOREIGN KEY (animal_id) REFERENCES animais(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);
