CREATE TABLE IF NOT EXISTS configuracoes (
    chave      VARCHAR(100) NOT NULL PRIMARY KEY,
    valor      TEXT         NOT NULL,
    updated_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO configuracoes (chave, valor) VALUES ('mp_modo_teste', '1');
INSERT IGNORE INTO configuracoes (chave, valor) VALUES ('link_instagram', '');
INSERT IGNORE INTO configuracoes (chave, valor) VALUES ('link_facebook', '');
INSERT IGNORE INTO configuracoes (chave, valor) VALUES ('link_youtube', '');
INSERT IGNORE INTO configuracoes (chave, valor) VALUES ('link_doe_agora', '#calculadora');
INSERT IGNORE INTO configuracoes (chave, valor) VALUES ('link_doe_agora_target', '_self');
INSERT IGNORE INTO configuracoes (chave, valor) VALUES ('link_seja_voluntario', '#apadrinhe');
INSERT IGNORE INTO configuracoes (chave, valor) VALUES ('link_seja_voluntario_target', '_self');
