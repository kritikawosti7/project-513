CREATE DATABASE IF NOT EXISTS lucas_loaves
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE lucas_loaves;

CREATE TABLE IF NOT EXISTS products (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(8, 2) UNSIGNED NOT NULL,
    image VARCHAR(255) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_products_slug (slug),
    CONSTRAINT chk_products_price CHECK (price > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS orders (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_number VARCHAR(32) NOT NULL,
    customer_name VARCHAR(150) NOT NULL,
    email VARCHAR(254) NOT NULL,
    phone VARCHAR(40) NULL,
    pickup_date DATE NOT NULL,
    pickup_time TIME NOT NULL,
    subtotal DECIMAL(10, 2) UNSIGNED NOT NULL,
    total DECIMAL(10, 2) UNSIGNED NOT NULL,
    status ENUM('pending', 'confirmed', 'ready', 'collected', 'cancelled')
        NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_orders_order_number (order_number),
    KEY idx_orders_created_at (created_at),
    KEY idx_orders_pickup_date (pickup_date),
    CONSTRAINT chk_orders_amounts CHECK (subtotal >= 0 AND total >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_items (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_id BIGINT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    product_name VARCHAR(100) NOT NULL,
    unit_price DECIMAL(8, 2) UNSIGNED NOT NULL,
    quantity SMALLINT UNSIGNED NOT NULL,
    line_total DECIMAL(10, 2) UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    KEY idx_order_items_order_id (order_id),
    KEY idx_order_items_product_id (product_id),
    CONSTRAINT fk_order_items_order
        FOREIGN KEY (order_id) REFERENCES orders (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_order_items_product
        FOREIGN KEY (product_id) REFERENCES products (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_order_items_quantity CHECK (quantity > 0),
    CONSTRAINT chk_order_items_amounts CHECK (unit_price >= 0 AND line_total >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS applications (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(254) NOT NULL,
    cover_letter TEXT NOT NULL,
    resume_path VARCHAR(255) NOT NULL,
    resume_original_name VARCHAR(255) NOT NULL,
    resume_mime_type VARCHAR(100) NOT NULL,
    resume_size INT UNSIGNED NOT NULL,
    status ENUM('new', 'reviewing', 'interview', 'declined', 'hired')
        NOT NULL DEFAULT 'new',
    submitted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_applications_submitted_at (submitted_at),
    KEY idx_applications_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    username VARCHAR(80) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_login_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_admin_users_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO products (name, slug, description, price, image)
VALUES
    (
        'White loaf',
        'white-loaf',
        'A naturally leavened country loaf with a tender crumb and crisp, golden crust.',
        7.00,
        'images/white-loaf.jpg'
    ),
    (
        'Rye loaf',
        'rye-loaf',
        'A hearty, gently tangy loaf made with organic rye flour and a long fermentation.',
        8.00,
        'images/rye-loaf.jpg'
    ),
    (
        'Spelt loaf',
        'spelt-loaf',
        'An aromatic sourdough with a delicate nutty flavour, made with organic spelt.',
        9.00,
        'images/spelt-loaf.jpg'
    ),
    (
        'Seeded loaf',
        'seeded-loaf',
        'Our crusty country sourdough generously topped with a toasted seed blend.',
        9.50,
        'images/seeded-loaf.jpg'
    )
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description),
    price = VALUES(price),
    image = VALUES(image),
    is_active = 1;
