-- -- Create event subscriptions table
-- CREATE TABLE IF NOT EXISTS event_subscriptions (
--     id INT AUTO_INCREMENT PRIMARY KEY,
--     event_id INT NOT NULL,
--     email VARCHAR(255) NOT NULL,
--     subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     reminded_at TIMESTAMP NULL,
--     is_active TINYINT(1) DEFAULT 1,
--     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
--     -- Indexes and constraints
--     UNIQUE KEY unique_subscription (event_id, email),
--     INDEX idx_event_email (event_id, email),
--     INDEX idx_reminder_check (is_active, reminded_at),
--     INDEX idx_active_subscriptions (is_active, subscribed_at),
    
--     -- Foreign key constraint (adjust table name if different)
--     FOREIGN KEY (event_id) REFERENCES hayvan_etkinlikleri(id) ON DELETE CASCADE
-- );

-- -- Insert sample data (optional)
-- -- INSERT INTO event_subscriptions (event_id, email) VALUES 
-- -- (1, 'test@example.com'),
-- -- (2, 'user@example.com');