CREATE DATABASE IF NOT EXISTS quiz;

USE quiz;

CREATE TABLE categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    descricao VARCHAR(60) NOT NULL
);

CREATE TABLE perguntas (
    id_pergunta INT AUTO_INCREMENT PRIMARY KEY,
    pergunta VARCHAR(255) NOT NULL,
    alternativa_a VARCHAR(150) NOT NULL,
    alternativa_b VARCHAR(150) NOT NULL,
    alternativa_c VARCHAR(150) NOT NULL,
    resposta_correta CHAR(1) NOT NULL,
    id_categoria INT NOT NULL,
    FOREIGN KEY (id_categoria)
        REFERENCES categorias(id_categoria)
);

INSERT INTO categorias (descricao) VALUES
('Matemática'),
('Português'),
('Informática');

INSERT INTO perguntas
(pergunta, alternativa_a, alternativa_b,
 alternativa_c, resposta_correta, id_categoria)
VALUES
('Quanto é 5 x 6?', '25', '30', '35', 'B', 1),
('Quanto é 100 dividido por 4?', '25', '20', '40', 'A', 1),
('Qual é a raiz quadrada de 81?', '7', '8', '9', 'C', 1),
('Qual é o plural de pão?', 'Pães', 'Pãos', 'Pãoses', 'A', 2),
('Qual é o antônimo de feliz?', 'Alegre', 'Triste', 'Contente', 'B', 2),
('Qual palavra é um verbo?', 'Casa', 'Mesa', 'Correr', 'C', 2),
('Qual linguagem estrutura páginas web?',
 'HTML', 'MySQL', 'Java', 'A', 3),
('Qual é a função do MySQL?',
 'Editar imagens', 'Gerenciar bancos de dados',
 'Reproduzir vídeos', 'B', 3),
('Qual atalho é utilizado para copiar?',
 'Ctrl + V', 'Ctrl + Z', 'Ctrl + C', 'C', 3);