-- =============================================================
-- BANCO DE DADOS: AutoMax Oficina Mecânica
-- Versão 3.0 — PHP + Gráficos + Foto de Veículo
-- =============================================================

CREATE DATABASE IF NOT EXISTS automax
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE automax;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS ITEM_OS;
DROP TABLE IF EXISTS ORDEM_SERVICO;
DROP TABLE IF EXISTS PECA;
DROP TABLE IF EXISTS VEICULO;
DROP TABLE IF EXISTS CLIENTE;
DROP TABLE IF EXISTS USUARIO;
SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================
-- USUARIO
-- =============================================================
CREATE TABLE USUARIO (
    id_usuario  INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome        VARCHAR(100) NOT NULL,
    login       VARCHAR(50)  NOT NULL,
    senha       VARCHAR(255) NOT NULL,   -- guardar hash em produção
    perfil      ENUM('Gerente','Recepcionista','Mecanico') NOT NULL DEFAULT 'Recepcionista',
    ativo       TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id_usuario),
    UNIQUE KEY UQ_USUARIO_LOGIN (login)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
-- CLIENTE
-- =============================================================
CREATE TABLE CLIENTE (
    id_cliente    INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome          VARCHAR(100) NOT NULL,
    cpf           CHAR(11)     NOT NULL,
    telefone      VARCHAR(20),
    celular       VARCHAR(20),
    email         VARCHAR(150),
    endereco      VARCHAR(255),
    data_cadastro DATE NOT NULL DEFAULT (CURRENT_DATE),
    ativo         TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id_cliente),
    UNIQUE KEY UQ_CLIENTE_CPF (cpf),
    CONSTRAINT CHK_CLIENTE_CPF CHECK (cpf REGEXP '^[0-9]{11}$')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
-- VEICULO (com foto)
-- =============================================================
CREATE TABLE VEICULO (
    id_veiculo   INT UNSIGNED NOT NULL AUTO_INCREMENT,
    placa        VARCHAR(8)   NOT NULL,
    modelo       VARCHAR(80)  NOT NULL,
    marca        VARCHAR(50)  NOT NULL,
    ano          SMALLINT UNSIGNED NOT NULL,
    cor          VARCHAR(30),
    km_atual     DECIMAL(10,2) NOT NULL DEFAULT 0,
    renavam      VARCHAR(11),
    chassi       VARCHAR(17),
    combustivel  ENUM('gasolina','etanol','flex','diesel','gnv','eletrico') DEFAULT 'flex',
    foto         VARCHAR(255) DEFAULT NULL,   -- caminho relativo da imagem
    id_cliente   INT UNSIGNED NOT NULL,
    ativo        TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id_veiculo),
    UNIQUE KEY UQ_VEICULO_PLACA (placa),
    CONSTRAINT FK_VEICULO_CLIENTE FOREIGN KEY (id_cliente)
        REFERENCES CLIENTE (id_cliente)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT CHK_VEICULO_ANO CHECK (ano BETWEEN 1900 AND 2100),
    CONSTRAINT CHK_VEICULO_KM  CHECK (km_atual >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
-- PECA
-- =============================================================
CREATE TABLE PECA (
    id_peca         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    codigo          VARCHAR(50)  NOT NULL,
    descricao       VARCHAR(150) NOT NULL,
    preco_custo     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    preco_venda     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    estoque_atual   INT NOT NULL DEFAULT 0,
    estoque_minimo  INT UNSIGNED NOT NULL DEFAULT 5,
    fornecedor      VARCHAR(100),
    ativo           TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id_peca),
    UNIQUE KEY UQ_PECA_CODIGO (codigo),
    CONSTRAINT CHK_PECA_CUSTO  CHECK (preco_custo >= 0),
    CONSTRAINT CHK_PECA_VENDA  CHECK (preco_venda >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
-- ORDEM_SERVICO
-- =============================================================
CREATE TABLE ORDEM_SERVICO (
    id_os              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    diagnostico        TEXT NOT NULL,
    prazo_previsto     DATE,
    data_abertura      DATE NOT NULL DEFAULT (CURRENT_DATE),
    data_fechamento    DATE,
    status             ENUM('Aberta','Em andamento','Aguardando peca','Finalizada','Cancelada')
                       NOT NULL DEFAULT 'Aberta',
    valor_total        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    observacoes        TEXT,
    id_cliente         INT UNSIGNED NOT NULL,
    id_veiculo         INT UNSIGNED NOT NULL,
    id_usuario_abertura INT UNSIGNED,
    PRIMARY KEY (id_os),
    CONSTRAINT FK_OS_CLIENTE  FOREIGN KEY (id_cliente)
        REFERENCES CLIENTE (id_cliente) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT FK_OS_VEICULO  FOREIGN KEY (id_veiculo)
        REFERENCES VEICULO (id_veiculo) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT FK_OS_USUARIO  FOREIGN KEY (id_usuario_abertura)
        REFERENCES USUARIO (id_usuario) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT CHK_OS_VALOR   CHECK (valor_total >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
-- ITEM_OS
-- =============================================================
CREATE TABLE ITEM_OS (
    id_item    INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_os      INT UNSIGNED NOT NULL,
    id_peca    INT UNSIGNED NOT NULL,
    quantidade SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    preco_unit DECIMAL(10,2) NOT NULL DEFAULT 0.00,  -- travado no momento da venda
    PRIMARY KEY (id_item),
    CONSTRAINT FK_ITEM_OS   FOREIGN KEY (id_os)   REFERENCES ORDEM_SERVICO (id_os)  ON DELETE CASCADE,
    CONSTRAINT FK_ITEM_PECA FOREIGN KEY (id_peca) REFERENCES PECA (id_peca)         ON DELETE RESTRICT,
    CONSTRAINT CHK_ITEM_QTD CHECK (quantidade > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
-- ÍNDICES DE PERFORMANCE
-- =============================================================
CREATE INDEX IDX_OS_STATUS        ON ORDEM_SERVICO (status);
CREATE INDEX IDX_OS_FECHAMENTO    ON ORDEM_SERVICO (data_fechamento);
CREATE INDEX IDX_OS_CLIENTE       ON ORDEM_SERVICO (id_cliente);
CREATE INDEX IDX_VEICULO_CLIENTE  ON VEICULO (id_cliente);
CREATE INDEX IDX_PECA_ESTOQUE     ON PECA (estoque_atual);

-- =============================================================
-- DADOS DE EXEMPLO
-- =============================================================

INSERT INTO USUARIO (nome, login, senha, perfil) VALUES
('Antonio Gerente',   'antonio', '123', 'Gerente'),
('Luciana Recepção',  'luciana', '123', 'Recepcionista'),
('Jonas Mecânico',    'jonas',   '123', 'Mecanico');

INSERT INTO CLIENTE (nome, cpf, celular, email, endereco) VALUES
('João Silva',    '12345678901', '11999990001', 'joao@email.com',   'Rua das Flores, 10'),
('Maria Souza',   '98765432100', '11988880002', 'maria@email.com',  'Av. Central, 200'),
('Carlos Mendes', '11122233344', '11977770003', 'carlos@email.com', 'Rua B, 45');

INSERT INTO VEICULO (placa, modelo, marca, ano, cor, km_atual, combustivel, id_cliente) VALUES
('ABC1D23', 'Gol',   'Volkswagen', 2019, 'Prata',  52000, 'flex',     1),
('XYZ9E87', 'Uno',   'Fiat',       2015, 'Branco', 98000, 'flex',     2),
('DEF4F56', 'Civic', 'Honda',      2022, 'Preto',  15000, 'gasolina', 3);

INSERT INTO PECA (codigo, descricao, preco_custo, preco_venda, estoque_atual, estoque_minimo, fornecedor) VALUES
('OL-5W30',   'Óleo Motor 5W30 1L',         25.00,  45.00, 50, 10, 'Distribuidora A'),
('FIL-001',   'Filtro de Óleo Universal',    18.00,  35.00, 30,  5, 'Distribuidora A'),
('PAST-01',   'Pastilha Freio Dianteira',    65.00, 120.00,  4,  5, 'Distribuidora B'),
('VELA-NGK',  'Vela Ignição NGK BKR5E',      12.00,  25.00, 40,  8, 'Auto Peças SP'),
('CORR-001',  'Correia Dentada Kit',         95.00, 180.00,  2,  3, 'Distribuidora C');

INSERT INTO ORDEM_SERVICO (diagnostico, prazo_previsto, data_abertura, data_fechamento, status, valor_total, id_cliente, id_veiculo, id_usuario_abertura) VALUES
('Troca de óleo e filtro', '2026-01-15', '2026-01-10', '2026-01-14', 'Finalizada', 260.00, 1, 1, 1),
('Revisão completa + pastilhas', '2026-02-20', '2026-02-15', '2026-02-19', 'Finalizada', 580.00, 2, 2, 1),
('Troca de correia dentada', '2026-02-28', '2026-02-25', '2026-02-27', 'Finalizada', 420.00, 3, 3, 2),
('Diagnóstico elétrico', '2026-03-10', '2026-03-05', '2026-03-09', 'Finalizada', 320.00, 1, 1, 1),
('Alinhamento e balanceamento', '2026-03-20', '2026-03-18', '2026-03-19', 'Finalizada', 180.00, 2, 2, 2),
('Troca de velas e filtros', '2026-04-05', '2026-04-01', '2026-04-04', 'Finalizada', 340.00, 3, 3, 1),
('Revisão dos freios', '2026-04-15', '2026-04-10', '2026-04-14', 'Finalizada', 460.00, 1, 1, 2),
('Troca óleo câmbio', '2026-05-08', '2026-05-03', NULL, 'Em andamento', 0.00, 2, 2, 1),
('Barulho no motor ao acelerar', '2026-05-30', '2026-05-20', NULL, 'Aberta', 0.00, 3, 3, 2);

INSERT INTO ITEM_OS (id_os, id_peca, quantidade, preco_unit) VALUES
(1, 1, 4, 45.00), (1, 2, 1, 35.00),
(2, 3, 1, 120.00),(2, 4, 4, 25.00),
(3, 5, 1, 180.00),
(4, 2, 1, 35.00),
(5, 4, 4, 25.00),
(6, 4, 4, 25.00),(6, 2, 1, 35.00),
(7, 3, 1, 120.00);
