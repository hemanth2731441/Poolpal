-- Add payment_status column to bookings table
ALTER TABLE bookings
ADD COLUMN payment_status ENUM('pending', 'completed', 'failed') NOT NULL DEFAULT 'pending',
ADD COLUMN payment_id VARCHAR(255) DEFAULT NULL,
ADD COLUMN payment_method VARCHAR(50) DEFAULT NULL,
ADD COLUMN payment_date TIMESTAMP NULL DEFAULT NULL; 