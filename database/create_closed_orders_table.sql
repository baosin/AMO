CREATE TABLE IF NOT EXISTS closed_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    close_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id),
    UNIQUE KEY unique_restaurant_date (restaurant_id, close_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
