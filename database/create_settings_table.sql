USE amo_system;

DROP TABLE IF EXISTS settings;

CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_deadline DATETIME,
    selected_restaurant_id INT,
    is_ordering_active BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (selected_restaurant_id) REFERENCES restaurants(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
