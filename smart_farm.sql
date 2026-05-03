CREATE DATABASE IF NOT EXISTS smart_farm;
USE smart_farm;


DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS cart_items;
DROP TABLE IF EXISTS carts;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;


CREATE TABLE users (
user_id INT AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(100) NOT NULL,
email VARCHAR(150) UNIQUE NOT NULL,
phone VARCHAR(20),
role ENUM('farmer','buyer','admin') DEFAULT 'buyer',
password_hash VARCHAR(255) NOT NULL,
address TEXT,
location VARCHAR(255),
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


INSERT INTO users (name,email,phone,role,password_hash) VALUES
('Admin','admin@example.com','9999999999','admin', '" + md5("admin@123") + "');


CREATE TABLE categories (
category_id INT AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(100) NOT NULL
);


INSERT INTO categories (name) VALUES ('Vegetables'), ('Fruits'), ('Grains'), ('Dairy');


CREATE TABLE products (
product_id INT AUTO_INCREMENT PRIMARY KEY,
farmer_id INT NOT NULL,
category_id INT,
name VARCHAR(150) NOT NULL,
description TEXT,
quantity DECIMAL(10,2) DEFAULT 0,
unit VARCHAR(20) DEFAULT 'kg',
price DECIMAL(10,2) NOT NULL,
image_path VARCHAR(255),
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (farmer_id) REFERENCES users(user_id) ON DELETE CASCADE,
FOREIGN KEY (category_id) REFERENCES categories(category_id)
);


CREATE TABLE carts (
cart_id INT AUTO_INCREMENT PRIMARY KEY,
user_id INT NOT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);


CREATE TABLE cart_items (
cart_item_id INT AUTO_INCREMENT PRIMARY KEY,
cart_id INT NOT NULL,
product_id INT NOT NULL,
qty DECIMAL(10,2) DEFAULT 1,
price DECIMAL(10,2) NOT NULL,
FOREIGN KEY (cart_id) REFERENCES carts(cart_id) ON DELETE CASCADE,
FOREIGN KEY (product_id) REFERENCES products(product_id)
);


CREATE TABLE `orders` (
order_id INT AUTO_INCREMENT PRIMARY KEY,
buyer_id INT NOT NULL,
total_amount DECIMAL(12,2) NOT NULL,