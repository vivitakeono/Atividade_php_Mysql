CREATE DATABASE IF NOT EXISTS monitoramento;

USE monitoramento;

CREATE TABLE visitantes (
    id_visitante INT AUTO_INCREMENT PRIMARY KEY,
    identificador CHAR(32) NOT NULL UNIQUE,
    data_primeiro_acesso DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE acessos (
    id_acesso INT AUTO_INCREMENT PRIMARY KEY,
    id_visitante INT NOT NULL,
    pagina_acessada VARCHAR(100) NOT NULL,
    termo_pesquisado VARCHAR(150),
    data_acesso DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_visitante)
        REFERENCES visitantes(id_visitante)
);