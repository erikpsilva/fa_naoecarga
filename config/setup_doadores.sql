CREATE TABLE IF NOT EXISTS doadores (
    id         INT           AUTO_INCREMENT PRIMARY KEY,
    nome       VARCHAR(255)  NOT NULL,
    email      VARCHAR(255)  NOT NULL,
    telefone   VARCHAR(20)   DEFAULT NULL,
    tipo       ENUM('unica','mensal') NOT NULL,
    valor      DECIMAL(10,2) NOT NULL,
    status     ENUM('pendente','aprovado','recusado','cancelado') NOT NULL DEFAULT 'pendente',
    mp_id      VARCHAR(100)  DEFAULT NULL COMMENT 'ID da preferência ou preapproval no MP',
    lgpd_tag   VARCHAR(10)   DEFAULT NULL COMMENT 'LGPD_OK = aceita compartilhar dados, LGPD_NOK = recusa',
    created_at TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
