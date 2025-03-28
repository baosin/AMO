USE amo_system;

CREATE TABLE menu_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    name VARCHAR(50) NOT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 修改 menus 表添加分類 ID
ALTER TABLE menus ADD COLUMN category_id INT NULL;
ALTER TABLE menus ADD FOREIGN KEY (category_id) REFERENCES menu_categories(id);
