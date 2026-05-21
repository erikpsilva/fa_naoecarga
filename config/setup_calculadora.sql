-- Cria a tabela calculadora_config se não existir (schema completo)
CREATE TABLE IF NOT EXISTS calculadora_config (
    id               INT UNSIGNED   NOT NULL DEFAULT 1,
    animal_1_nome    VARCHAR(100)   NOT NULL DEFAULT 'Roedores',
    animal_1_pct     DECIMAL(5,2)   NOT NULL DEFAULT 65.00,
    animal_1_imagem  VARCHAR(255)   NOT NULL DEFAULT 'uploads/animais/imgRato.png',
    animal_2_nome    VARCHAR(100)   NOT NULL DEFAULT 'Peixes',
    animal_2_pct     DECIMAL(5,2)   NOT NULL DEFAULT 20.00,
    animal_2_imagem  VARCHAR(255)   NOT NULL DEFAULT 'uploads/animais/imgPeixe.png',
    animal_3_nome    VARCHAR(100)   NOT NULL DEFAULT 'Galinhas',
    animal_3_pct     DECIMAL(5,2)   NOT NULL DEFAULT 7.00,
    animal_3_imagem  VARCHAR(255)   NOT NULL DEFAULT 'uploads/animais/imgGalinha.png',
    animal_4_nome    VARCHAR(100)   NOT NULL DEFAULT 'Outros',
    animal_4_pct     DECIMAL(5,2)   NOT NULL DEFAULT 8.00,
    animal_4_imagem  VARCHAR(255)   NOT NULL DEFAULT 'uploads/animais/imgOutros.png',
    valor_btn_1      INT UNSIGNED   NOT NULL DEFAULT 30,
    valor_btn_2      INT UNSIGNED   NOT NULL DEFAULT 60,
    valor_btn_3      INT UNSIGNED   NOT NULL DEFAULT 120,
    custo_por_animal DECIMAL(10,2)  NOT NULL DEFAULT 15.00,
    calc_pretitulo   VARCHAR(200)   NOT NULL DEFAULT 'Calculadora de impacto',
    calc_titulo      TEXT           NOT NULL,
    calc_texto       TEXT           NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insere a linha única se ainda não existir
INSERT IGNORE INTO calculadora_config (id) VALUES (1);

-- Migração: adiciona colunas caso a tabela já exista sem elas
ALTER TABLE calculadora_config
    ADD COLUMN IF NOT EXISTS animal_1_imagem VARCHAR(255) NOT NULL DEFAULT 'uploads/animais/imgRato.png'    AFTER animal_1_pct,
    ADD COLUMN IF NOT EXISTS animal_2_imagem VARCHAR(255) NOT NULL DEFAULT 'uploads/animais/imgPeixe.png'   AFTER animal_2_pct,
    ADD COLUMN IF NOT EXISTS animal_3_imagem VARCHAR(255) NOT NULL DEFAULT 'uploads/animais/imgGalinha.png' AFTER animal_3_pct,
    ADD COLUMN IF NOT EXISTS animal_4_imagem VARCHAR(255) NOT NULL DEFAULT 'uploads/animais/imgOutros.png'  AFTER animal_4_pct,
    ADD COLUMN IF NOT EXISTS calc_pretitulo  VARCHAR(200) NOT NULL DEFAULT 'Calculadora de impacto',
    ADD COLUMN IF NOT EXISTS calc_titulo     TEXT         NOT NULL,
    ADD COLUMN IF NOT EXISTS calc_texto      TEXT         NOT NULL;
