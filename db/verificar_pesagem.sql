-- Script para verificar e corrigir a tabela de pesagem
-- Execute este script no phpMyAdmin ou MySQL

USE ifmabov;

-- 1. Remove a tabela pesagens se existir (nome incorreto)
DROP TABLE IF EXISTS pesagens;

-- 2. Cria a tabela pesagem com a estrutura correta
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

-- 3. Verifica se a tabela foi criada corretamente
DESCRIBE pesagem;

-- 4. Mostra as tabelas existentes
SHOW TABLES;

-- 5. Verifica se há dados na tabela
SELECT COUNT(*) as total_pesagens FROM pesagem;

-- 6. Mostra a estrutura completa do banco
SELECT 
    TABLE_NAME,
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'ifmabov' 
AND TABLE_NAME IN ('animais', 'pesagem', 'vacinas', 'historico_leitura')
ORDER BY TABLE_NAME, ORDINAL_POSITION; 