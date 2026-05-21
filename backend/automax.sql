-- =============================================================
-- BANCO DE DADOS: Oficina Mecânica
-- Baseado no DER: Cliente > Veículo > Ordem de Serviço
-- =============================================================

CREATE DATABASE IF NOT EXISTS oficina_mecanica
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE oficina_mecanica;

-- =============================================================
-- DROP das tabelas existentes (ordem inversa das FKs)
-- =============================================================
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS ITEM_OS;
DROP TABLE IF EXISTS OS_MECANICO;
DROP TABLE IF EXISTS ORDEM_SERVICO;
DROP TABLE IF EXISTS MECANICO;
DROP TABLE IF EXISTS SERVICO;
DROP TABLE IF EXISTS PECA;
DROP TABLE IF EXISTS VEICULO;
DROP TABLE IF EXISTS CLIENTE;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================
-- TABELA: CLIENTE
-- =============================================================
CREATE TABLE CLIENTE (
    idCliente       INT             NOT NULL AUTO_INCREMENT,
    nome            VARCHAR(100)    NOT NULL,
    cpf             CHAR(11)        NOT NULL,
    telefone        VARCHAR(15),
    celular         VARCHAR(15),
    email           VARCHAR(100),
    endereco        VARCHAR(255),
    data_cadastro   DATE            NOT NULL DEFAULT (CURRENT_DATE),
    observacoes     TEXT,

    PRIMARY KEY (idCliente),
    CONSTRAINT UQ_CLIENTE_CPF   UNIQUE (cpf),
    CONSTRAINT UQ_CLIENTE_EMAIL UNIQUE (email)
);

-- =============================================================
-- TABELA: VEICULO
-- =============================================================
CREATE TABLE VEICULO (
    idVeiculo           INT             NOT NULL AUTO_INCREMENT,
    placa               VARCHAR(8)      NOT NULL,
    marca               VARCHAR(50)     NOT NULL,
    modelo              VARCHAR(80)     NOT NULL,
    ano                 INT             NOT NULL,
    cor                 VARCHAR(30),
    quilometragem       DECIMAL(10, 2),
    renavam             VARCHAR(11),
    chassi              VARCHAR(17),
    data_ultima_revisao DATE,
    idCliente           INT             NOT NULL,

    PRIMARY KEY (idVeiculo),
    CONSTRAINT UQ_VEICULO_PLACA     UNIQUE (placa),
    CONSTRAINT UQ_VEICULO_RENAVAM   UNIQUE (renavam),
    CONSTRAINT UQ_VEICULO_CHASSI    UNIQUE (chassi),
    CONSTRAINT FK_VEICULO_CLIENTE   FOREIGN KEY (idCliente)
        REFERENCES CLIENTE (idCliente)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT CHK_VEICULO_ANO      CHECK (ano >= 1900 AND ano <= 2100)
);

-- =============================================================
-- TABELA: MECANICO
-- =============================================================
CREATE TABLE MECANICO (
    idMecanico      INT             NOT NULL AUTO_INCREMENT,
    nome            VARCHAR(100)    NOT NULL,
    cpf             CHAR(11)        NOT NULL,
    telefone        VARCHAR(15),
    especialidade   VARCHAR(80),
    data_admissao   DATE            NOT NULL DEFAULT (CURRENT_DATE),
    status          VARCHAR(20)     NOT NULL DEFAULT 'ativo',

    PRIMARY KEY (idMecanico),
    CONSTRAINT UQ_MECANICO_CPF      UNIQUE (cpf),
    CONSTRAINT CHK_MECANICO_STATUS  CHECK (status IN ('ativo', 'inativo', 'férias', 'afastado'))
);

-- =============================================================
-- TABELA: SERVICO
-- =============================================================
CREATE TABLE SERVICO (
    idServico               INT             NOT NULL AUTO_INCREMENT,
    nome                    VARCHAR(100)    NOT NULL,
    descricao               TEXT,
    preco_mao_obra          DECIMAL(10, 2)  NOT NULL DEFAULT 0.00,
    tempo_estimado_minutos  INT             NOT NULL DEFAULT 0,
    categoria               VARCHAR(60),

    PRIMARY KEY (idServico),
    CONSTRAINT CHK_SERVICO_PRECO        CHECK (preco_mao_obra >= 0),
    CONSTRAINT CHK_SERVICO_TEMPO        CHECK (tempo_estimado_minutos >= 0)
);

-- =============================================================
-- TABELA: PECA
-- =============================================================
CREATE TABLE PECA (
    idPeca              INT             NOT NULL AUTO_INCREMENT,
    codigo              VARCHAR(50)     NOT NULL,
    nome                VARCHAR(100)    NOT NULL,
    descricao           TEXT,
    preco_compra        DECIMAL(10, 2)  NOT NULL DEFAULT 0.00,
    preco_venda         DECIMAL(10, 2)  NOT NULL DEFAULT 0.00,
    quantidade_estoque  INT             NOT NULL DEFAULT 0,
    estoque_minimo      INT             NOT NULL DEFAULT 0,
    fornecedor          VARCHAR(100),

    PRIMARY KEY (idPeca),
    CONSTRAINT UQ_PECA_CODIGO       UNIQUE (codigo),
    CONSTRAINT CHK_PECA_PRECO_C     CHECK (preco_compra >= 0),
    CONSTRAINT CHK_PECA_PRECO_V     CHECK (preco_venda >= 0),
    CONSTRAINT CHK_PECA_ESTOQUE     CHECK (quantidade_estoque >= 0),
    CONSTRAINT CHK_PECA_EST_MIN     CHECK (estoque_minimo >= 0)
);

-- =============================================================
-- TABELA: ORDEM_SERVICO
-- =============================================================
CREATE TABLE ORDEM_SERVICO (
    idOS            INT             NOT NULL AUTO_INCREMENT,
    numero_os       VARCHAR(20)     NOT NULL,
    data_entrada    DATE            NOT NULL DEFAULT (CURRENT_DATE),
    data_previsao   DATE,
    data_saida      DATE,
    status          VARCHAR(20)     NOT NULL DEFAULT 'aberta',
    valor_total     DECIMAL(10, 2)  NOT NULL DEFAULT 0.00,
    valor_pago      DECIMAL(10, 2)  NOT NULL DEFAULT 0.00,
    forma_pagamento VARCHAR(30),
    observacoes     TEXT,
    idVeiculo       INT             NOT NULL,
    idCliente       INT             NOT NULL,

    PRIMARY KEY (idOS),
    CONSTRAINT UQ_OS_NUMERO             UNIQUE (numero_os),
    CONSTRAINT FK_OS_VEICULO            FOREIGN KEY (idVeiculo)
        REFERENCES VEICULO (idVeiculo)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT FK_OS_CLIENTE            FOREIGN KEY (idCliente)
        REFERENCES CLIENTE (idCliente)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT CHK_OS_STATUS            CHECK (status IN ('aberta', 'em andamento', 'aguardando peça', 'concluída', 'cancelada')),
    CONSTRAINT CHK_OS_VALOR_TOTAL       CHECK (valor_total >= 0),
    CONSTRAINT CHK_OS_VALOR_PAGO        CHECK (valor_pago >= 0),
    CONSTRAINT CHK_OS_DATAS             CHECK (data_saida IS NULL OR data_saida >= data_entrada)
);

-- =============================================================
-- TABELA: OS_MECANICO  (relacionamento N:N entre OS e Mecânico)
-- =============================================================
CREATE TABLE OS_MECANICO (
    idOS        INT NOT NULL,
    idMecanico  INT NOT NULL,

    PRIMARY KEY (idOS, idMecanico),
    CONSTRAINT FK_OSM_OS            FOREIGN KEY (idOS)
        REFERENCES ORDEM_SERVICO (idOS)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT FK_OSM_MECANICO      FOREIGN KEY (idMecanico)
        REFERENCES MECANICO (idMecanico)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

-- =============================================================
-- TABELA: ITEM_OS
-- =============================================================
CREATE TABLE ITEM_OS (
    idItemOS        INT             NOT NULL AUTO_INCREMENT,
    idOS            INT             NOT NULL,
    idServico       INT,
    idPeca          INT,
    quantidade      INT             NOT NULL DEFAULT 1,
    valor_unitario  DECIMAL(10, 2)  NOT NULL DEFAULT 0.00,
    desconto        DECIMAL(10, 2)  NOT NULL DEFAULT 0.00,
    subtotal        DECIMAL(10, 2)  GENERATED ALWAYS AS
                        (quantidade * valor_unitario - desconto) STORED,

    PRIMARY KEY (idItemOS),
    CONSTRAINT FK_ITEM_OS_OS            FOREIGN KEY (idOS)
        REFERENCES ORDEM_SERVICO (idOS)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT FK_ITEM_OS_SERVICO       FOREIGN KEY (idServico)
        REFERENCES SERVICO (idServico)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT FK_ITEM_OS_PECA          FOREIGN KEY (idPeca)
        REFERENCES PECA (idPeca)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT CHK_ITEM_OS_QTD          CHECK (quantidade > 0),
    CONSTRAINT CHK_ITEM_OS_VLR          CHECK (valor_unitario >= 0),
    CONSTRAINT CHK_ITEM_OS_DESC         CHECK (desconto >= 0),
    -- Cada item deve ter pelo menos um: serviço OU peça
    CONSTRAINT CHK_ITEM_OS_TIPO         CHECK (idServico IS NOT NULL OR idPeca IS NOT NULL)
);

-- =============================================================
-- ÍNDICES ADICIONAIS (performance)
-- =============================================================
CREATE INDEX IDX_VEICULO_CLIENTE        ON VEICULO       (idCliente);
CREATE INDEX IDX_OS_VEICULO             ON ORDEM_SERVICO (idVeiculo);
CREATE INDEX IDX_OS_CLIENTE             ON ORDEM_SERVICO (idCliente);
CREATE INDEX IDX_OS_STATUS              ON ORDEM_SERVICO (status);
CREATE INDEX IDX_OS_DATA_ENTRADA        ON ORDEM_SERVICO (data_entrada);
CREATE INDEX IDX_ITEM_OS_OS             ON ITEM_OS       (idOS);
CREATE INDEX IDX_ITEM_OS_SERVICO        ON ITEM_OS       (idServico);
CREATE INDEX IDX_ITEM_OS_PECA          ON ITEM_OS       (idPeca);
CREATE INDEX IDX_PECA_CODIGO            ON PECA          (codigo);

-- =============================================================
-- TRIGGERS
-- =============================================================
DROP TRIGGER IF EXISTS TRG_ATUALIZA_TOTAL_OS_INSERT;
DROP TRIGGER IF EXISTS TRG_ATUALIZA_TOTAL_OS_UPDATE;
DROP TRIGGER IF EXISTS TRG_ATUALIZA_TOTAL_OS_DELETE;
DROP TRIGGER IF EXISTS TRG_BAIXA_ESTOQUE_INSERT;
DROP TRIGGER IF EXISTS TRG_RESTAURA_ESTOQUE_DELETE;
DROP TRIGGER IF EXISTS TRG_AJUSTA_ESTOQUE_UPDATE;

DELIMITER $$

CREATE TRIGGER TRG_ATUALIZA_TOTAL_OS_INSERT
AFTER INSERT ON ITEM_OS
FOR EACH ROW
BEGIN
    UPDATE ORDEM_SERVICO
    SET valor_total = (
        SELECT COALESCE(SUM(subtotal), 0)
        FROM ITEM_OS
        WHERE idOS = NEW.idOS
    )
    WHERE idOS = NEW.idOS;
END$$

CREATE TRIGGER TRG_ATUALIZA_TOTAL_OS_UPDATE
AFTER UPDATE ON ITEM_OS
FOR EACH ROW
BEGIN
    UPDATE ORDEM_SERVICO
    SET valor_total = (
        SELECT COALESCE(SUM(subtotal), 0)
        FROM ITEM_OS
        WHERE idOS = NEW.idOS
    )
    WHERE idOS = NEW.idOS;
END$$

CREATE TRIGGER TRG_ATUALIZA_TOTAL_OS_DELETE
AFTER DELETE ON ITEM_OS
FOR EACH ROW
BEGIN
    UPDATE ORDEM_SERVICO
    SET valor_total = (
        SELECT COALESCE(SUM(subtotal), 0)
        FROM ITEM_OS
        WHERE idOS = OLD.idOS
    )
    WHERE idOS = OLD.idOS;
END$$

-- =============================================================
-- TRIGGER: baixa estoque ao inserir ITEM_OS com peça
-- =============================================================
CREATE TRIGGER TRG_BAIXA_ESTOQUE_INSERT
AFTER INSERT ON ITEM_OS
FOR EACH ROW
BEGIN
    IF NEW.idPeca IS NOT NULL THEN
        UPDATE PECA
        SET quantidade_estoque = quantidade_estoque - NEW.quantidade
        WHERE idPeca = NEW.idPeca;
    END IF;
END$$

-- Restaura estoque ao deletar item com peça
CREATE TRIGGER TRG_RESTAURA_ESTOQUE_DELETE
AFTER DELETE ON ITEM_OS
FOR EACH ROW
BEGIN
    IF OLD.idPeca IS NOT NULL THEN
        UPDATE PECA
        SET quantidade_estoque = quantidade_estoque + OLD.quantidade
        WHERE idPeca = OLD.idPeca;
    END IF;
END$$

-- Ajusta estoque ao alterar quantidade de peça no item
CREATE TRIGGER TRG_AJUSTA_ESTOQUE_UPDATE
AFTER UPDATE ON ITEM_OS
FOR EACH ROW
BEGIN
    -- Devolve quantidade antiga
    IF OLD.idPeca IS NOT NULL THEN
        UPDATE PECA
        SET quantidade_estoque = quantidade_estoque + OLD.quantidade
        WHERE idPeca = OLD.idPeca;
    END IF;
    -- Desconta quantidade nova
    IF NEW.idPeca IS NOT NULL THEN
        UPDATE PECA
        SET quantidade_estoque = quantidade_estoque - NEW.quantidade
        WHERE idPeca = NEW.idPeca;
    END IF;
END$$

DELIMITER ;

-- =============================================================
-- VIEWS
-- =============================================================
DROP VIEW IF EXISTS VW_OS_ABERTAS;
DROP VIEW IF EXISTS VW_PECAS_ESTOQUE_BAIXO;
DROP VIEW IF EXISTS VW_FINANCEIRO_OS;

CREATE VIEW VW_OS_ABERTAS AS
SELECT
    os.idOS,
    os.numero_os,
    os.data_entrada,
    os.data_previsao,
    os.status,
    os.valor_total,
    c.nome          AS nome_cliente,
    c.celular       AS celular_cliente,
    v.placa,
    v.marca,
    v.modelo,
    v.ano
FROM ORDEM_SERVICO os
JOIN CLIENTE c   ON c.idCliente = os.idCliente
JOIN VEICULO v   ON v.idVeiculo = os.idVeiculo
WHERE os.status NOT IN ('concluída', 'cancelada');

-- View: Peças com estoque abaixo do mínimo
CREATE VIEW VW_PECAS_ESTOQUE_BAIXO AS
SELECT
    idPeca,
    codigo,
    nome,
    quantidade_estoque,
    estoque_minimo,
    fornecedor
FROM PECA
WHERE quantidade_estoque <= estoque_minimo;

-- View: Resumo financeiro por OS
CREATE VIEW VW_FINANCEIRO_OS AS
SELECT
    os.idOS,
    os.numero_os,
    os.data_entrada,
    os.data_saida,
    os.valor_total,
    os.valor_pago,
    (os.valor_total - os.valor_pago) AS saldo_devedor,
    os.forma_pagamento,
    os.status,
    c.nome AS nome_cliente
FROM ORDEM_SERVICO os
JOIN CLIENTE c ON c.idCliente = os.idCliente;

-- =============================================================
-- DADOS DE EXEMPLO 
-- =============================================================

-- Clientes
INSERT INTO CLIENTE (nome, cpf, telefone, celular, email, endereco) VALUES
('João Silva',    '12345678901', '1133334444', '11999990001', 'joao@email.com',   'Rua das Flores, 10 - SP'),
('Maria Souza',   '98765432100', '1122223333', '11988880002', 'maria@email.com',  'Av. Central, 200 - SP'),
('Carlos Mendes', '11122233344', NULL,          '11977770003', 'carlos@email.com', 'Rua B, 45 - SP');

-- Veículos
INSERT INTO VEICULO (placa, marca, modelo, ano, cor, quilometragem, idCliente) VALUES
('ABC1D23', 'Volkswagen', 'Gol',    2019, 'Prata', 52000.00, 1),
('XYZ9E87', 'Fiat',       'Uno',    2015, 'Branco', 98000.50, 2),
('DEF4F56', 'Honda',      'Civic',  2022, 'Preto',  15000.00, 3);

-- Mecânicos
INSERT INTO MECANICO (nome, cpf, telefone, especialidade, data_admissao, status) VALUES
('Ricardo Alves',  '55566677788', '11944440001', 'Motor e transmissão', '2020-03-01', 'ativo'),
('Fernanda Lima',  '44455566699', '11933330002', 'Elétrica automotiva', '2021-07-15', 'ativo'),
('Paulo Rocha',    '33344455500', '11922220003', 'Suspensão e freios',  '2019-01-10', 'ativo');

-- Serviços
INSERT INTO SERVICO (nome, descricao, preco_mao_obra, tempo_estimado_minutos, categoria) VALUES
('Troca de óleo',         'Troca de óleo do motor + filtro',    80.00,  30,  'Manutenção'),
('Alinhamento',           'Alinhamento das quatro rodas',        120.00, 60,  'Suspensão'),
('Diagnóstico eletrônico','Leitura e diagnóstico via scanner',   150.00, 45,  'Elétrica'),
('Troca de pastilhas',    'Substituição das pastilhas de freio', 200.00, 90,  'Freios');

-- Peças
INSERT INTO PECA (codigo, nome, descricao, preco_compra, preco_venda, quantidade_estoque, estoque_minimo, fornecedor) VALUES
('OL-5W30-1L',  'Óleo 5W30 1L',          'Óleo sintético 5W30',         25.00,  45.00,  50, 10, 'Distribuidora A'),
('FIL-001',     'Filtro de óleo',         'Filtro de óleo universal',    18.00,  35.00,  30,  5, 'Distribuidora A'),
('PAST-DIANT',  'Pastilha de freio diant','Jogo de pastilhas dianteiras', 65.00, 120.00,  20,  5, 'Distribuidora B'),
('VELA-NGK-BKR','Vela NGK BKR5E',         'Vela de ignição NGK',         12.00,  25.00,  40,  8, 'Auto Peças SP');

-- Ordens de Serviço
INSERT INTO ORDEM_SERVICO (numero_os, data_entrada, data_previsao, status, forma_pagamento, idVeiculo, idCliente) VALUES
('OS-2026-0001', '2026-04-20', '2026-04-21', 'concluída',    'pix',      1, 1),
('OS-2026-0002', '2026-04-28', '2026-04-30', 'em andamento', 'cartão',   2, 2),
('OS-2026-0003', '2026-04-29', '2026-05-02', 'aberta',       NULL,       3, 3);

-- Mecânicos nas OS
INSERT INTO OS_MECANICO (idOS, idMecanico) VALUES
(1, 1), (1, 3),
(2, 2),
(3, 1);

-- Itens das OS
INSERT INTO ITEM_OS (idOS, idServico, idPeca, quantidade, valor_unitario, desconto) VALUES
-- OS-0001: troca de óleo (serviço + peças)
(1, 1, NULL, 1,  80.00, 0.00),   -- serviço: troca de óleo
(1, NULL, 1, 4,  45.00, 0.00),   -- 4L óleo 5W30
(1, NULL, 2, 1,  35.00, 0.00),   -- filtro de óleo
-- OS-0002: diagnóstico + pastilhas
(2, 3, NULL, 1, 150.00, 0.00),   -- diagnóstico
(2, 4, NULL, 1, 200.00, 10.00),  -- pastilhas (com R$10 desconto)
(2, NULL, 3, 1, 120.00, 0.00),   -- peça: pastilhas
-- OS-0003: alinhamento
(3, 2, NULL, 1, 120.00, 0.00);   -- alinhamento