CREATE TABLE IF NOT EXISTS banners (
    id         INT           AUTO_INCREMENT PRIMARY KEY,
    pagina     VARCHAR(50)   NOT NULL UNIQUE,
    arquivo    VARCHAR(255)  NOT NULL DEFAULT 'images/bannerHome.jpg',
    created_at TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO banners (pagina, arquivo) VALUES ('home',   'images/bannerHome.jpg');
INSERT IGNORE INTO banners (pagina, arquivo) VALUES ('doacao', 'images/bannerHome.jpg');
