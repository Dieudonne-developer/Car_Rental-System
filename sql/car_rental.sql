CREATE DATABASE IF NOT EXISTS car_rental;

USE car_rental;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    role ENUM('admin', 'seller', 'client') DEFAULT 'client'
);

CREATE TABLE cars (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    title VARCHAR(255),
    description TEXT,
    image VARCHAR(255),
    price_per_day DECIMAL(10, 2),
    status ENUM(
        'pending',
        'approved',
        'rented'
    ) DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES users (id)
);