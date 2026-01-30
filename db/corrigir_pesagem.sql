-- Script para corrigir a tabela de pesagem
-- Execute este script no phpMyAdmin ou MySQL

USE ifmabov;

-- Remove a tabela pesagens se existir (nome incorreto)
DROP TABLE IF EXISTS pesagens;

-- Cria a tabela pesagem com a estrutura correta
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

-- Verifica se a tabela foi criada corretamente
DESCRIBE pesagem;

-- Mostra as tabelas existentes
SHOW TABLES; 