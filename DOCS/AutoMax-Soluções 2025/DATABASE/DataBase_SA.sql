DROP DATABASE IF EXISTS automax;
CREATE DATABASE automax CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE automax;

-- TABELAS
DROP TABLE IF EXISTS item_os;
DROP TABLE IF EXISTS ordem_servico;
DROP TABLE IF EXISTS veiculo;
DROP TABLE IF EXISTS cliente;
DROP TABLE IF EXISTS peca;
DROP TABLE IF EXISTS usuario;

CREATE TABLE cliente (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cpf CHAR(11) UNIQUE NOT NULL,
    celular VARCHAR(20),
    email VARCHAR(100),
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE veiculo (
    id_veiculo INT AUTO_INCREMENT PRIMARY KEY,
    placa CHAR(8) UNIQUE NOT NULL,
    modelo VARCHAR(50) NOT NULL,
    marca VARCHAR(50) NOT NULL,
    ano YEAR,
    cor VARCHAR(30),
    km_atual DECIMAL(10,2),
    id_cliente INT,
    FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente) ON DELETE CASCADE
);

CREATE TABLE peca (
    id_peca INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(30) UNIQUE NOT NULL,
    descricao VARCHAR(200) NOT NULL,
    preco_custo DECIMAL(10,2) NOT NULL,
    preco_venda DECIMAL(10,2) NOT NULL,
    estoque_atual INT DEFAULT 0,
    estoque_minimo INT DEFAULT 5,
    fornecedor VARCHAR(100)
);

CREATE TABLE usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    login VARCHAR(50) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    perfil ENUM('Recepcionista', 'Mecânico', 'Gerente') DEFAULT 'Recepcionista'
);

CREATE TABLE ordem_servico (
    id_os INT AUTO_INCREMENT PRIMARY KEY,
    data_abertura DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_fechamento DATETIME NULL,
    diagnostico TEXT,
    status ENUM('Aberta', 'Em execução', 'Aguardando peças', 'Finalizada') DEFAULT 'Aberta',
    prazo_previsto DATE,
    valor_total DECIMAL(10,2) DEFAULT 0.00,
    id_cliente INT,
    id_veiculo INT,
    id_usuario_abertura INT,
    FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente) ON DELETE CASCADE,
    FOREIGN KEY (id_veiculo) REFERENCES veiculo(id_veiculo) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario_abertura) REFERENCES usuario(id_usuario) ON DELETE SET NULL
);

CREATE TABLE item_os (
    id_item_os INT AUTO_INCREMENT PRIMARY KEY,
    id_os INT,
    id_peca INT,
    quantidade INT NOT NULL DEFAULT 1,
    preco_unitario DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_os) REFERENCES ordem_servico(id_os) ON DELETE CASCADE,
    FOREIGN KEY (id_peca) REFERENCES peca(id_peca) ON DELETE SET NULL
);

-- DADOS DE TESTE (senhas = "123")
INSERT INTO usuario (nome, login, senha, perfil) VALUES
('Antônio Lima',    'antonio',  '123', 'Gerente'),
('Luciana Ribeiro', 'luciana',  '123', 'Recepcionista'),
('Jonas Pereira',   'jonas',    '123', 'Mecânico');

INSERT INTO cliente (nome, cpf, celular, email) VALUES
('Flávio Cunha', '12345678900', '48999991234', 'flavio@email.com'),
('Maria Silva',  '98765432100', '11987654321', 'maria@email.com');

INSERT INTO veiculo (placa, modelo, marca, ano, cor, km_atual, id_cliente) VALUES
('ABC3D45', 'Civic',    'Honda',   2018, 'Prata',  85000.00, 1),
('XYZ9E87', 'Corolla',  'Toyota',  2020, 'Preto',  45000.00, 2);

INSERT INTO peca (codigo, descricao, preco_custo, preco_venda, estoque_atual, estoque_minimo, fornecedor) VALUES
('OLEO01',   'Óleo 5W30 Sintético', 35.00, 65.00, 15, 5, 'Petrobras'),
('VELA01',   'Jogo de velas NGK',   80.00, 140.00, 8, 5, 'NGK'),
('FILTRO01', 'Filtro de óleo',      25.00, 45.00, 12, 5, 'Mann'),
('PASTILHA', 'Pastilha de freio',   120.00, 220.00, 6, 3, 'Bosch');
