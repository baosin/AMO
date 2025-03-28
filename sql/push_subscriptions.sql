CREATE TABLE IF NOT EXISTS push_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    endpoint VARCHAR(255) NOT NULL UNIQUE,
    p256dh VARCHAR(255) NOT NULL,
    auth VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_endpoint (endpoint)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; 