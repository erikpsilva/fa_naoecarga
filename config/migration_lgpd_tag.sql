-- Migração: adiciona coluna lgpd_tag na tabela doadores
-- Execute este script UMA VEZ no banco de dados de produção

ALTER TABLE doadores
    ADD COLUMN lgpd_tag VARCHAR(10) DEFAULT NULL
        COMMENT 'LGPD_OK = aceita compartilhar dados, LGPD_NOK = recusa'
    AFTER mp_id;
