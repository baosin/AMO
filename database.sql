-- 建立資料庫
CREATE DATABASE IF NOT EXISTS amo_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE amo_system;

-- 店家資料表
CREATE TABLE IF NOT EXISTS restaurants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 便當菜單表
CREATE TABLE IF NOT EXISTS menus (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    is_available BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id)
);

-- 系統設定表
CREATE TABLE IF NOT EXISTS settings (
    id INT PRIMARY KEY DEFAULT 1,
    order_deadline DATETIME DEFAULT '2025-02-17 11:00:00',
    is_ordering_active BOOLEAN DEFAULT true,
    selected_restaurant_id INT,
    FOREIGN KEY (selected_restaurant_id) REFERENCES restaurants(id)
);

-- 訂單表
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_name VARCHAR(255) NOT NULL,
    menu_id INT,
    quantity INT DEFAULT 1,
    note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('open', 'closed') DEFAULT 'open',
    FOREIGN KEY (menu_id) REFERENCES menus(id)
);

-- 訂單詳細項目表
CREATE TABLE IF NOT EXISTS order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    menu_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (menu_id) REFERENCES menus(id)
);

-- 訂單修改歷史表
CREATE TABLE IF NOT EXISTS order_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    modified_by VARCHAR(255) NOT NULL,
    modified_at DATETIME NOT NULL,
    old_menu_id INT,
    new_menu_id INT,
    old_quantity INT,
    new_quantity INT,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (old_menu_id) REFERENCES menus(id),
    FOREIGN KEY (new_menu_id) REFERENCES menus(id)
);

-- 每日店家選擇表
CREATE TABLE IF NOT EXISTS daily_restaurants (
    id INT PRIMARY KEY AUTO_INCREMENT,
    restaurant_id INT NOT NULL,
    date DATE NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id)
);
