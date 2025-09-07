CREATE DATABASE sistema_seguranca;
USE sistema_seguranca;
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL
);
CREATE TABLE dispositivos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(100) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    ip VARCHAR(50) NOT NULL,
    nome_dispositivo VARCHAR(100) NOT NULL,
    modelo VARCHAR(100) NOT NULL,
    mac VARCHAR(50) NOT NULL,
    tipo_dispositivo VARCHAR(100) NOT NULL,
    condominio VARCHAR(100) NOT NULL,
    bloco VARCHAR(100) DEFAULT NULL,
    local VARCHAR(100) DEFAULT NULL,        -- Portaria, Garagem, Apartamento etc
    observacao VARCHAR(255) DEFAULT NULL,   -- Detalhes extras
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);USE sistema_seguranca;
SELECT id, usuario FROM usuarios;


